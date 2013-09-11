<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Entity\NavPublish;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PagePublish;

class NavController extends Controller
{

    public function publishAllAction()
    {
        ini_set ('max_execution_time', 3000);
        ini_set ('memory_limit', "750M");
        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $listRenderer = $this->container->getParameter('kitpages_cms.block.renderer');
        $dataInheritanceList = $this->container->getParameter('kitpages_cms.page.data_inheritance_list');
        $em = $this->getDoctrine()->getEntityManager();
        $pageManager = $this->get('kitpages.cms.manager.page');
        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $blockManager = $this->get('kitpages.cms.manager.block');
        $navManager = $this->get('kitpages.cms.manager.nav');

        $query = $em->getConnection()->executeUpdate("UPDATE cms_page SET is_published = 0");
        $query = $em->getConnection()->executeUpdate("UPDATE cms_zone SET is_published = 0");
        $query = $em->getConnection()->executeUpdate("UPDATE cms_block SET is_published = 0");

        $pageSiteList = $em->getRepository('KitpagesCmsBundle:Page')->getRootNodes();

        foreach($pageSiteList as $pageSite) {
            $pageManager->publish($pageSite, $layoutList, $listRenderer, $dataInheritanceList, true);
        }

        $zoneList = $em->getRepository('KitpagesCmsBundle:Zone')->findByIsPublished(0);

        foreach($zoneList as $zone) {
            $zoneManager->publish($zone, $listRenderer);
        }

        $blockList = $em->getRepository('KitpagesCmsBundle:Block')->findByIsPublished(0);

        foreach($blockList as $block) {
            $blockManager->publish($block, $listRenderer[$block->getTemplate()]);
        }


        $navManager->publish();

        $this->get('session')->getFlashBag()->add('notice', 'Site published');

        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_nav_tree'));

    }

    public function publishAction() {


        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->publish();

        $this->get('session')->getFlashBag()->add('notice', 'Navigation published');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_nav_tree'));
    }


    public function widgetBreadcrumbAction($slug, $page, $homePageSlug = null, $startDepth = 0) {
        if ($page instanceof Page) {
            $em = $this->getDoctrine()->getEntityManager();
            $context = $this->get('kitpages.cms.controller.context');
            $dataBreadcrumb = array();
            if ($context->getViewMode() == Context::VIEW_MODE_EDIT || $context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
                $pageStartBreadcrumb = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($slug);
                $levelStartBreadcrumb = $pageStartBreadcrumb->getLevel()+1+$startDepth;
                $levelEndBreadcrumb = $page->getLevel()-1;
                $pageListBreadcrumb = $em->getRepository('KitpagesCmsBundle:Page')->parentBetweenTwoDepth($page, $levelStartBreadcrumb, $levelEndBreadcrumb);

                if ($homePageSlug != null && (isset($pageListBreadcrumb[0]) && $pageListBreadcrumb[0]->getSlug() != $homePageSlug)) {
                    $pageHP = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($homePageSlug);
                    if ($pageHP instanceof Page) {
                        array_unshift($pageListBreadcrumb, $pageHP);
                    }
                }

                foreach($pageListBreadcrumb as $pageBreadcrumb) {
                    $dataBreadcrumb[] = array(
                        'url' => $this->getPageLink($pageBreadcrumb),
                        'label' => $pageBreadcrumb->getMenuTitle()
                    );
                }
                $title = $page->getMenuTitle();
            } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
                $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);
                $pageStartBreadcrumb = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($slug);
                $levelStartBreadcrumb = $pageStartBreadcrumb->getLevel()+1+$startDepth;
                $pageData = $pagePublish->getData();
                $levelEndBreadcrumb = $pageData['page']['level']-1;
                $navPublish = $page->getNavPublish();
                $navListBreadcrumb = $em->getRepository('KitpagesCmsBundle:NavPublish')->parentBetweenTwoDepth($navPublish, $levelStartBreadcrumb, $levelEndBreadcrumb);

                if ($homePageSlug != null && (isset($navListBreadcrumb[0]) && $navListBreadcrumb[0]->getSlug() != $homePageSlug)) {
                    $pageHP = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($homePageSlug);
                    if ($pageHP instanceof NavPublish) {
                        array_unshift($navListBreadcrumb, $pageHP);
                    }
                }

                foreach($navListBreadcrumb as $navBreadcrumb) {
                    $dataBreadcrumb[] = array(
                        'url' => $this->getPagePublishLink($navBreadcrumb),
                        'label' => $navBreadcrumb->getTitle()
                    );
                }
                $title = $navPublish->getTitle();
            }
            return $this->render(
                'KitpagesCmsBundle:Nav:breadcrumb.html.twig',
                array(
                    'kitCmsPageList' => $dataBreadcrumb,
                    'kitCmsPageLabel' => $title
                )
            );
        }
        return  new Response('');
    }

    public function widgetAction($slug, $cssClass, $currentPageSlug, $startDepth = 1, $endDepth = 10, $filterByCurrentPage = true) {
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        $navigation = array();
        $selectPageSlugList = array();
        if ($startDepth == 1) {
           $filterByCurrentPage = false;
        }
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT || $context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($slug);
            $this->get('logger')->info('slug = '.$slug);
            $currentPage = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($currentPageSlug);
            if ( (!$filterByCurrentPage) || ($currentPage != null) ) {
                if ($filterByCurrentPage && $currentPage != null) {
                    $page = $em->getRepository('KitpagesCmsBundle:Page')->childOfPageWithForParentOtherPage($page, $currentPage, $startDepth-1);
                    $startDepth = 1;
                }
                if ($page != null) {
                    $startLevel = $page->getLevel() + $startDepth;
                    $endLevel = $page->getLevel() + $endDepth;
                    $navigation = $this->navPageChildren($page, $context->getViewMode(), $startDepth, $endLevel);

                    if ($currentPage != null) {
                        $selectParentPageList = $em->getRepository('KitpagesCmsBundle:Page')->parentBetweenTwoDepth($currentPage, $startLevel, $endLevel);
                        foreach($selectParentPageList as $selectParentPage) {
                            $selectPageSlugList[] = $selectParentPage->getSlug();
                        }
                    }
                }
            }

        }

        elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            // calculate page
            $cacheManager = $this->get('kitpages.simple_cache');
            $filterString = 'notfiltered';
            if ($filterByCurrentPage) {
                $filterString = 'filtered';
            }
            $myThis = $this;
            $response = $cacheManager->get(
                'kit-cms-navigation-'.$context->getViewMode()."-$slug-$currentPageSlug-$filterString-$startDepth-$endDepth",
                function() use ($myThis, $em, $slug, $cssClass, $currentPageSlug, $startDepth, $endDepth, $filterByCurrentPage ) {
                    $em = $myThis->getDoctrine()->getEntityManager();
                    $context = $myThis->get('kitpages.cms.controller.context');
                    $resultingHtml = '';
                    $navigation = array();
                    $selectPageSlugList = array();
                    if ($startDepth == 1) {
                       $filterByCurrentPage = false;
                    }
                    $navPublish = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($slug);
                    $currentNavPublish = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($currentPageSlug);
                    if (
                        ($navPublish != null) &&
                        ( (!$filterByCurrentPage) || ($currentNavPublish != null) )
                    ) {
                        if ($filterByCurrentPage && $currentNavPublish != null) {
                            $navPublish = $em->getRepository('KitpagesCmsBundle:NavPublish')->childOfPageWithForParentOtherPage($navPublish, $currentNavPublish, $startDepth-1);
                            $startDepth = 1;
                        }
                        if ($navPublish != null) {
                            $startLevel = $navPublish->getLevel() + $startDepth;
                            $endLevel = $navPublish->getLevel() + $endDepth;
                            $navigation =  $myThis->navPublishChildren($navPublish, $context->getViewMode(), $startDepth, $endLevel);

                            if ($currentNavPublish != null) {
                                $selectParentNavPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->parentBetweenTwoDepth($currentNavPublish, $startLevel, $endLevel);
                                foreach($selectParentNavPublishList as $selectParentNavPublish) {
                                    $selectPageSlugList[] = $selectParentNavPublish->getSlug();
                                }
                            }
                        }
                    }
                    return $myThis->render(
                        'KitpagesCmsBundle:Nav:navigation.html.twig',
                        array(
                            'currentPageSlug' => $currentPageSlug,
                            'selectPageSlugList' => $selectPageSlugList,
                            'navigation' => $navigation,
                            'navigationSlug' => $slug,
                            'navigationCssClass' => $cssClass,
                            'root' => true,
                            'kitCmsViewMode' => $context->getViewMode()
                        )
                    );
                }
            );
            return $response;
        }
        return $this->render(
            'KitpagesCmsBundle:Nav:navigation.html.twig',
            array(
                'currentPageSlug' => $currentPageSlug,
                'selectPageSlugList' => $selectPageSlugList,
                'navigation' => $navigation,
                'navigationSlug' => $slug,
                'navigationCssClass' => $cssClass,
                'root' => true,
                'kitCmsViewMode' => $context->getViewMode(),
                'kitpages_target' => $_SERVER["REQUEST_URI"]
            )
        );
    }

    public function navPublishChildren($navPublish, $viewMode, $currentDepth, $endLevel){
        $em = $this->getDoctrine()->getEntityManager();
        $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->childrenOfDepth($navPublish, $currentDepth);
        $listNavigationElem = array();
        foreach($navPublishList as $navPublishChild) {
            $navigationElem = array(
                'slug' => $navPublishChild->getSlug(),
                'title' => $navPublishChild->getTitle(),
                'level' => $navPublishChild->getLevel(),
                'url' => ''
            );
            $navigationElem['url'] = $this->getPagePublishLink($navPublishChild);

            $navigationElem['children'] = array();
            if ($navPublishChild->getLevel() < $endLevel) {
                $navigationElem['children'] = $this->navPublishChildren($navPublishChild, $viewMode, 1, $endLevel);
            }
            $listNavigationElem[] = $navigationElem;
        }
        return $listNavigationElem;
    }

    public function navPageChildren($page, $viewMode, $currentDepth, $endLevel){
        $em = $this->getDoctrine()->getEntityManager();
        $pageList = $em->getRepository('KitpagesCmsBundle:Page')->childrenOfDepth($page, $currentDepth);
        $listNavigationElem = array();
        foreach($pageList as $pageChild) {
            $navigationElem = array(
                'slug' => $pageChild->getSlug(),
                'title' => $pageChild->getMenuTitle(),
                'level' => $pageChild->getLevel(),
                'url' => ''
            );

            $navigationElem['url'] = $this->getPageLink($pageChild);

            $navigationElem['children'] = array();
            if ($pageChild->getLevel() < $endLevel) {
                $navigationElem['children'] = $this->navPageChildren($pageChild, $viewMode, 1, $endLevel);
            }
            $listNavigationElem[] = $navigationElem;
        }
        return $listNavigationElem;
    }


    public function treeAction(){
        $userPreferenceManager = $this->get('kitpages.cms.manager.userPreference');
        $userPreference = $userPreferenceManager->getPreference($this->get('security.context')->getToken()->getUserName());
        $tree = $this->treeChildren();
        return $this->render(
            'KitpagesCmsBundle:Nav:tree.html.twig',
            array(
                'tree' => $tree,
                'kitCmsUserPreferenceTree' => $userPreference->getDataTree()
            )
        );
    }



    public function treeChildren($pageParent = null){

        $em = $this->getDoctrine()->getEntityManager();

        if (is_null($pageParent)) {
            $pageList = $em->getRepository('KitpagesCmsBundle:Page')->getRootNodes();
        } else {
            $pageList = $em->getRepository('KitpagesCmsBundle:Page')->children($pageParent, true);
        }

        $pageListRenderer = array();
        foreach($pageList as $page) {
            $pageTree = array();
            $pageTree['id'] = $page->getId();
            $pageTree['slug'] = $page->getSlug();
            $pageTree['menuTitle'] = $page->getMenuTitle();
            $pageTree['isPublished'] = $page->getIsPublished();
            $paramUrl = array(
                'id' => $page->getId(),
                'kitpages_target' => $_SERVER["REQUEST_URI"]
            );
            $paramUrlCreate = array(
                'parent_id' => $page->getId(),
                'kitpages_target' => $_SERVER["REQUEST_URI"]
            );
            $paramUrlWithChild = array(
                'id' => $page->getId(),
                'children' => true,
                'kitpages_target' => $_SERVER["REQUEST_URI"]
            );


            if ($page->getIsPendingDelete() == 1) {
                if ($pageParent->getIsPendingDelete() == 0) {
                    $pageTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'undelete',
                        'url' => $this->generateUrl('kitpages_cms_page_undelete', $paramUrl),
                    );
                    $pageTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'confirm delete',
                        'url' => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
                        'class' => 'kit-cms-modal-open'
                    );
                } else {
                    $pageTree['actionList'][] = array(
                        'id' => '',
                        'label' => 'publish all',
                        'url' => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
                        'class' => 'kit-cms-advanced'
                    );
                }
            } else {

                $pageTree['actionList'][] = array(
                    'id' => 'publish',
                    'label' => 'publish',
                    'url'  => $this->generateUrl('kitpages_cms_page_publish', $paramUrl),
                    'class' => ($page->getIsPublished() == '1')?'kit-cms-advanced':'',
                    'icon' => 'icon/publish.png'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'publish all',
                    'url'  => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
                    'class' => 'kit-cms-advanced kit-cms-modal-open'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'up',
                    'url'  => $this->generateUrl('kitpages_cms_nav_moveup', $paramUrl),
                    'class' => ($page->getPageType() == 'technical')?'kit-cms-advanced':'',
                    'icon' => 'icon/arrow-up.png'

                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'down',
                    'url'  => $this->generateUrl('kitpages_cms_nav_movedown', $paramUrl),
                    'class' => ($page->getPageType() == 'technical')?'kit-cms-advanced':'',
                    'icon' => 'icon/arrow-down.png'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add page',
                    'url'  => $this->generateUrl('kitpages_cms_page_create', $paramUrlCreate),
                    'icon' => 'icon/add.png'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add page technical',
                    'url'  => $this->generateUrl('kitpages_cms_page_create_technical', $paramUrlCreate),
                    'class' => 'kit-cms-advanced'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'add page link',
                    'url'  => $this->generateUrl('kitpages_cms_page_create_link', $paramUrlCreate),
                    'class' => 'kit-cms-advanced'
                );
                $pageTree['actionList'][] = array(
                    'id' => '',
                    'label' => 'delete',
                    'url'  => $this->generateUrl('kitpages_cms_page_delete', $paramUrl),
                    'class' => ($page->getPageType() == 'technical')?'kit-cms-advanced':'',
                    'icon' => 'icon/delete.png',
                    'class' => 'kit-cms-advanced'
                );

            }


            if ($page->getPageType() == 'edito') {
                $pageTree['url'] = $this->generateUrl(
                    'kitpages_cms_page_view_lang',
                    array(
                        'id' => $page->getId(),
                        'lang' => $page->getLanguage(),
                        'urlTitle' => $page->getUrlTitle()
                    )
                );
            } elseif($page->getPageType() == 'technical') {
                $pageTree['url'] = $this->generateUrl('kitpages_cms_page_edit_technical', $paramUrl);
            } elseif($page->getPageType() == 'link') {
                $pageTree['url'] = $this->generateUrl('kitpages_cms_page_edit_link', $paramUrl);
                //$pageTree['actionList']['link'] = $page->getLinkUrl();
                $pageTree['menuTitle'] .= ' <span class="kit-cms-tree-indicator-link">[ -&gt; '.$this->getPageLink($page).']</span>';
            }
            $pageTree['children'] = $this->treeChildren($page);
            $pageListRenderer[] = $pageTree;
        }
        return $pageListRenderer;
    }

    public function moveUpAction(Page $page){

        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->moveUp($page, 1);

        $this->get('session')->getFlashBag()->add('notice', 'Page moved');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
    }

    public function moveDownAction(Page $page){
        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->moveDown($page, 1);
        $this->get('session')->getFlashBag()->add('notice', 'Page moved');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));

    }

    public function getPageLink($page) {
        $url = '';
        if ($page->getPageType() == 'link' ) {
            $url = $page->getLinkUrl();
            if ($page->getIsLinkUrlFirstChild()) {
                $em = $this->getDoctrine()->getEntityManager();
                $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
                if (count($pageChildren) > 0 && $pageChildren['0'] instanceof Page) {
                    $url = $this->getPageLink($pageChildren['0']);
                }
            }
        }
        if ($page->getPageType() == 'edito' ) {
            if ($page->getForcedUrl()) {
                $url = $this->getRequest()->getBaseUrl().$page->getForcedUrl();
            } else {
                $url = $this->generateUrl(
                    'kitpages_cms_page_view_lang',
                    array(
                        'id' => $page->getId(),
                        'lang' => $page->getLanguage(),
                        'urlTitle' => $page->getUrlTitle()
                    )
                );
            }
        }
        return $url;

    }

    public function getPagePublishLink($navPublish) {
        $url = '';
        $page = $navPublish->getPage();
        $pagePublish = $page->getPagePublish();
        if ($pagePublish->getPageType() == 'link' ) {
            $url = $navPublish->getLinkUrl();
            if ($navPublish->getIsLinkUrlFirstChild()) {
                $em = $this->getDoctrine()->getEntityManager();
                $navPublishChildren = $em->getRepository('KitpagesCmsBundle:NavPublish')->children($navPublish, true);
                if (count($navPublishChildren) > 0 && $navPublishChildren['0'] instanceof NavPublish) {
                    $url = $this->getPagePublishLink($navPublishChildren['0']);
                }
            }
        }
        if ($pagePublish->getPageType() == 'edito' ) {
            if ($pagePublish->getForcedUrl()) {
                $url = $this->getRequest()->getBaseUrl().$pagePublish->getForcedUrl();
            } else {
                $url = $this->generateUrl(
                    'kitpages_cms_page_view_lang',
                    array(
                        'id' => $page->getId(),
                        'lang' => $pagePublish->getLanguage(),
                        'urlTitle' => $pagePublish->getUrlTitle()
                    )
                );
            }
        }

        return $url;

    }

    public function saveUserPreferenceTreeAction(){
        $userPreferenceManager = $this->get('kitpages.cms.manager.userPreference');
        $request = $this->getRequest();

        $pageId = $request->query->get("id", null);
        $actionTree = $request->query->get("action", null);
        $targetTree = $request->query->get("target", null);

        $userPreferenceManager->setPreferenceDataTreeState(
            $this->get('security.context')->getToken()->getUserName(),
            $pageId,
            $actionTree,
            $targetTree
        );

        return new Response(null);
    }

    public function saveUserPreferenceTreeScrollAction(){
        $userPreferenceManager = $this->get('kitpages.cms.manager.userPreference');
        $request = $this->getRequest();

        $scroll = $request->query->get("scroll", 0);
        $userPreferenceManager->setPreferenceDataTreeScroll(
            $this->get('security.context')->getToken()->getUserName(),
            $scroll
        );

        return new Response(null);
    }


}