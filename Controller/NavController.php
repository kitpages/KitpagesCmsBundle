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
 
    public function publishAction() {
        

        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->publish();
        
        $this->getRequest()->getSession()->setFlash('notice', 'Navigation published');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }        
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }  
    
    public function widgetAction($slug, $currentPageSlug) {
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        $navigation = array();
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($slug);
            $navigation = $this->navPageChildren($page, $context->getViewMode());
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($slug);
            $navigation = $this->navPageChildren($page, $context->getViewMode());      
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $navPublish = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($slug);
            $navigation = $this->navPublishChildren($navPublish);
        }
        return $this->render('KitpagesCmsBundle:Nav:navigation.html.twig', array('slugCurrent' => $currentPageSlug, 'navigation' => $navigation, 'navigationLabel' => $slug, 'root' => true));
    }

    public function navPublishChildren($navPublish){
        $em = $this->getDoctrine()->getEntityManager();
        $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->children($navPublish, true);
        $listNavigationElem = array();
        foreach($navPublishList as $navPublishChild) {
            $pagePublish = $navPublishChild->getPage()->getPagePublish();
            $navigationElem = array(
                'slug' => $navPublishChild->getSlug(),                
                'title' => $navPublishChild->getTitle(),
                'level' => $navPublishChild->getLevel(),   
                'url' => ''                 
            );
            if ($pagePublish->getPageType() != 'technical' ) {
                $navigationElem['url'] = $this->generateUrl(
                    'kitpages_cms_page_view_lng',
                    array(
                        'id' => $navPublishChild->getId(),
                        'lng' => $pagePublish->getLanguage(),
                        'urlTitle' => $pagePublish->getUrlTitle()
                    )
                );
            }            
            $navigationElem['children'] = $this->navPublishChildren($navPublishChild);
            $listNavigationElem[] = $navigationElem;
        }
        return $listNavigationElem;
    }

    public function navPageChildren($page, $viewMode){
        $em = $this->getDoctrine()->getEntityManager();
        $pageList = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
        $listNavigationElem = array();
        foreach($pageList as $pageChild) {
            $navigationElem = array(
                'slug' => $pageChild->getSlug(),                
                'title' => $pageChild->getMenuTitle(),
                'level' => $pageChild->getLevel(),
                'url' => ''
            );
            if ($pageChild->getPageType() != 'technical' ) {
                $navigationElem['url'] = $this->generateUrl(
                    'kitpages_cms_page_view_lng',
                    array(
                        'id' => $pageChild->getId(),
                        'lng' => $pageChild->getLanguage(),
                        'urlTitle' => $pageChild->getUrlTitle()
                    )
                );
            }
            $navigationElem['children'] = $this->navPageChildren($pageChild, $viewMode);
            $listNavigationElem[] = $navigationElem;
        }
        return $listNavigationElem;
    }
    
    
    public function arboAction(){
      
        $arbo = $this->arboChildren();
        return $this->render('KitpagesCmsBundle:Nav:arbo.html.twig', array('arbo' => $arbo));
    }    
 
    
   
    public function arboChildren($pageParent = null){ 
        
        $em = $this->getDoctrine()->getEntityManager();
        
        if (is_null($pageParent)) {
            $pageList = $em->getRepository('KitpagesCmsBundle:Page')->getRootNodes();
        } else {
            $pageList = $em->getRepository('KitpagesCmsBundle:Page')->children($pageParent, true);        
        }
        
        $pageListRenderer = array();
        foreach($pageList as $page) {
            $pageArbo = array();
            $pageArbo['slug'] = $page->getSlug();
            $pageArbo['menuTitle'] = $page->getMenuTitle(); 
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
                $pageArbo['actionList'] = array(
                    "publish All" => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
                ); 
                if ($pageParent->getIsPendingDelete() == 0) {
                    $pageArbo['actionList']["undelete"] = $this->generateUrl('kitpages_cms_page_undelete', $paramUrl);
                }
            } else {
                
                $pageArbo['actionList'] = array(
                    "publish" => $this->generateUrl('kitpages_cms_page_publish', $paramUrl),
                    "publish All" => $this->generateUrl('kitpages_cms_page_publish', $paramUrlWithChild),
                    "up" => $this->generateUrl('kitpages_cms_nav_moveup', $paramUrl),
                    "down" => $this->generateUrl('kitpages_cms_nav_movedown', $paramUrl),
                    "add page" => $this->generateUrl('kitpages_cms_page_create', $paramUrlCreate),
                    "add page technical" => $this->generateUrl('kitpages_cms_page_create_technical', $paramUrlCreate),
                    "add page link" => $this->generateUrl('kitpages_cms_page_create_link', $paramUrlCreate),
                    "delete" => $this->generateUrl('kitpages_cms_page_delete', $paramUrl) 
                );                
            }
            
            
            if ($page->getPageType() == 'edito') {
                $pageArbo['url'] = $this->generateUrl(
                            'kitpages_cms_page_view_lng',
                            array(
                                'id' => $page->getId(),
                                'lng' => $page->getLanguage(),
                                'urlTitle' => $page->getUrlTitle()
                            )
                        );
            } elseif($page->getPageType() == 'technical') {
                $pageArbo['url'] = $this->generateUrl('kitpages_cms_page_edit_technical', $paramUrl);
            } elseif($page->getPageType() == 'link') {
                $pageArbo['url'] = $this->generateUrl('kitpages_cms_page_edit_link', $paramUrl);
                $pageArbo['actionList']['link'] = $page->getLinkUrl();
            }
            $pageArbo['children'] = $this->arboChildren($page);
            $pageListRenderer[] = $pageArbo;
        }
        return $pageListRenderer;
    }

    public function moveUpAction(Page $page){
        
        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->moveUp($page, 1);        
        
        $this->getRequest()->getSession()->setFlash('notice', 'Page moved');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
    }

    public function moveDownAction(Page $page){
        $navManager = $this->get('kitpages.cms.manager.nav');
        $navManager->moveDown($page, 1);    
        $this->getRequest()->getSession()->setFlash('notice', 'Page moved');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));

    }
    
}