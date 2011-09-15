<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\PageZone;

class PageController extends Controller
{

    public function widgetToolbarAction(Page $page) {
        $context = $this->get('kitpages.cms.controller.context');
        $dataRender = array(
            'kitCmsViewMode' => $context->getViewMode(),
            'kitCmsPage' => $page,
            'target' => $_SERVER["REQUEST_URI"]
        );
        return $this->render('KitpagesCmsBundle:Page:toolbar.html.twig', $dataRender);
    }

    public function viewAction(Page $page, $lang, $urlTitle)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $pageId = $page->getId();
        $pageType = $page->getPageType();
        $pageLanguage = $page->getLanguage();
        $pageUrlTitle = $page->getUrlTitle();
        $pageLayout = $page->getLayout();
        $forcedUrl = $page->getForcedUrl();
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {

        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {

        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);
            if ($pagePublish == null ) {
                throw new NotFoundHttpException('The page does not exist.');
            }
            $pageType = $pagePublish->getPageType();
            $pageLanguage = $pagePublish->getLanguage();
            $pageUrlTitle = $pagePublish->getUrlTitle();
            $pageLayout = $pagePublish->getLayout();
            $forcedUrl = $pagePublish->getForcedUrl();
        }

        if ($pageType == "technical") {
            throw new NotFoundHttpException('The page does not exist.');
        }

        if ($pageType == "link") {
            return $this->redirect ($page->getLinkUrl(), 301);
        }

        if ($forcedUrl && ($forcedUrl != $this->getRequest()->getPathInfo() ) ) {
            return $this->redirect(
                $this->getRequest()->getBaseUrl().$forcedUrl
            );
        }

        if ( ($pageLanguage != $lang) || ($pageUrlTitle != $urlTitle) ) {
            return $this->redirect (
                $this->generateUrl(
                    'kitpages_cms_page_view_lang',
                    array(
                        'id' => $pageId,
                        'lang' => $pageLanguage,
                        'urlTitle' => $pageUrlTitle
                    )
                ),
                301
            );
        }

        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$pageLayout);
        $cmsManager->setLayout($layout['renderer_twig']);

        return $this->render(
            'KitpagesCmsBundle:Page:layout.html.twig',
            array(
                'kitCmsViewMode' => $context->getViewMode(),
                'kitCmsPage' => $page
            )
        );
    }

    public function widgetZoneAction($location_in_page, Page $page) {
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findByPageAndLocation($page, $location_in_page);
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$page->getLayout());
        if ($zone == null) {
            $createZoneInPageUrl = $this->generateUrl(
                "kitpages_cms_createZoneInPage",
                array(
                    'id' => $page->getId(),
                    'locationInPage' => $location_in_page,
                    'kitpages_target' => $_SERVER["REQUEST_URI"]
                )
            );
            return new Response('Please <a href="'.$createZoneInPageUrl.'">create a zone</a> with the location "'.htmlspecialchars($location_in_page).'"');
        }

        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = $this->get('templating.helper.actions')->render(
            "KitpagesCmsBundle:Zone:widget",
            array(
                "slug" => $zone->getSlug(),
                "renderer" =>$layout['zone_list'][$location_in_page]['renderer'],
                'displayToolbar' => false,
                'authorizedBlockTemplateList' => $layout['zone_list'][$location_in_page]['authorized_block_template_list']
            ),
            array()
        );
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $resultingHtml = $this->toolbarZone($zone, $resultingHtml, $layout['zone_list'][$location_in_page]['authorized_block_template_list']);
        }
        return new Response($resultingHtml);
    }

    public function createAction()
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        $page = new Page();

        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $selectLayoutList = array();
        foreach ($layoutList as $key => $layout) {
            $selectLayoutList[$key] = $key;
        }

        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add(
            'slug',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'required' => false,
                'attr' => array('size'=>'40')
            )
        );
        $builder->add('parent_id','hidden',array(
            'property_path' => false,
            'data' => $this->get('request')->query->get('parent_id')
        ));
        $builder->add('layout', 'choice',array(
            'choices' => $selectLayoutList,
            'required' => true
        ));
        // get form
        $form = $builder->getForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $page->setPageType('edito');
                $page->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setLanguage($pageParent->getLanguage());
                    $page->setParent($pageParent);
                }

                $em->persist($page);
                $em->flush();
                $layoutKey = $dataForm['layout'];
                $zoneList = $layoutList[$layoutKey]['zone_list'];
                foreach($zoneList as $locationInPage => $render) {
                    $pageManager->createZoneInPage($page, $locationInPage);
                }

                $this->getRequest()->getSession()->setFlash('notice', 'Page created');
                return $this->redirect(
                    $this->generateUrl(
                        'kitpages_cms_page_edit',
                        array(
                            'id' => $page->getId(),
                            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
                        )
                    )
                );
            }
        }
        return $this->render('KitpagesCmsBundle:Page:create.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }

    public function createZoneInPageAction(Page $page, $locationInPage)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        $pageManager->createZoneInPage($page, $locationInPage);
        $this->getRequest()->getSession()->setFlash('notice', "Zone $locationInPage created");
        $target = $this->getRequest()->query->get('kitpages_target', null);
        return $this->redirect($target);
    }

    public function createTechnicalAction()
    {
        $page = new Page();

        $parent_id = $this->get('request')->query->get('parent_id', null);
        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text', array('required' => false, 'attr' => array('class'=>'kit-cms-advanced')));
        $builder->add('isInNavigation', 'checkbox', array('required' => false));
        $builder->add('menuTitle', 'text', array('required' => false));
        if (empty($parent_id)) {
            $builder->add('language', 'text');
        }
        $builder->add('parent_id','hidden',array(
            'property_path' => false,
            'data' => $parent_id
        ));

        // get form
        $form = $builder->getForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $page->setPageType('technical');
                $page->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setLanguage($pageParent->getLanguage());
                    $page->setParent($pageParent);
                }

                $em->persist($page);
                $em->flush();

                $this->getRequest()->getSession()->setFlash('notice', 'Page technical created');
                $target = $this->getRequest()->query->get('kitpages_target', null);
                if ($target) {
                    return $this->redirect($target);
                }
            }
        }
        return $this->render('KitpagesCmsBundle:Page:createTechnical.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }



    public function createLinkAction()
    {
        $page = new Page();


        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text', array('required' => false, 'attr' => array('class'=>'kit-cms-advanced')));
        $builder->add('title', 'text');
        $builder->add('isInNavigation', 'checkbox', array('required' => false));
        $builder->add('menuTitle', 'text', array('required' => false));
        $builder->add('linkUrl', 'text');
        $builder->add('parent_id','hidden',array(
            'property_path' => false,
            'data' => $this->get('request')->query->get('parent_id')
        ));


        // get form
        $form = $builder->getForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $page->setPageType('link');
                $page->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setLanguage($pageParent->getLanguage());
                    $page->setParent($pageParent);
                }

                $em->persist($page);
                $em->flush();

                $this->getRequest()->getSession()->setFlash('notice', 'Page technical created');
                $target = $this->getRequest()->query->get('kitpages_target', null);
                if ($target) {
                    return $this->redirect($target);
                }
            }
        }
        return $this->render('KitpagesCmsBundle:Page:createLink.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }

    public function editAction(Page $page, $inToolbar = false, $target = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        if (is_null($target)) {
            $target = $request->query->get('kitpages_target', null);
        }

        if (!$page->getData()) {
            $page->setData(array('root'=>null));
        }
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$page->getLayout());

        // build basic form
        $pageParent = $page->getParent();
        $parentId = '';
        if ($pageParent instanceof Page) {
            $parentId = $pageParent->getId();
        }
        $builder = $this->createFormBuilder($page);
        $builder->add(
            'slug',
            'text',
            array(
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );
        $builder->add(
            'forcedUrl',
            'text',
            array(
                'label' => 'Forced Url',
                'required' => false,
                'attr' => array(
                    'class'=>'kit-cms-advanced',
                    'size' => '100'
                )
            )
        );
        $builder->add(
            'title',
            'text',
            array(
                'label' => "Title of the page",
                'attr' => array("size" => '100')
            )
        );
        $builder->add(
            'isInNavigation',
            'checkbox',
            array(
                'label' => "Display in navigation ?",
                'required' => false
            )
        );
        $builder->add(
            'menuTitle',
            'text',
            array(
                'label' => 'Page name in the navigation',
                'required' => false
            )
        );
        $builder->add(
            'parent_id',
            'text',
            array(
                'label' => 'Id of the parent page',
                'attr' => array('class'=>'kit-cms-advanced'),
                'property_path' => false,
                'data' => $parentId
            )
        );

        $builder->add(
            'language',
            'text',
            array(
                'label' => "Page language",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );

        // build custom form
        $className = $layout['data_form_class'];
        $builder->add('data', 'collection', array(
           'type' => new $className(),
        ));

        // get form
        $form = $builder->getForm();

        // persist form if needed
        if ($request->getMethod() == 'POST') {
            $oldPageData = $page->getData();
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setParent($pageParent);
                }
                $em->flush();
                $pageManager = $this->get('kitpages.cms.manager.page');
                $pageManager->afterModify($page, $oldPageData);
                $this->getRequest()->getSession()->setFlash('notice', 'Page modified');
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            }
        }
        $view = $form->createView();
        return $this->render($layout['data_form_twig'], array(
            'form' => $form->createView(),
            'id' => $page->getId(),
            'inToolbar' => $inToolbar,
            'kitpages_target' => $target
        ));
    }

    public function editTechnicalAction(Page $page)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $target = $request->query->get('kitpages_target', null);

        // build basic form
        $pageParent = $page->getParent();
        $parentId = '';
        if ($pageParent instanceof Page) {
            $parentId = $pageParent->getId();
        }
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text', array('attr' => array('class'=>'kit-cms-advanced')));
        $builder->add(
            'language',
            'text',
            array(
                'label' => "Page language",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );

        $builder->add('isInNavigation', 'checkbox', array('required' => false));
        $builder->add('menuTitle', 'text', array('required' => false));
         $builder->add('parent_id','text',array(
            'property_path' => false,
            'data' => $parentId
        ));
        // get form
        $form = $builder->getForm();

        // persist form if needed
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setParent($pageParent);
                }
                $em->flush();
                $this->getRequest()->getSession()->setFlash('notice', 'Page Technical modified');
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            }
        }
        $view = $form->createView();
        return $this->render('KitpagesCmsBundle:Page:editTechnical.html.twig', array(
            'form' => $form->createView(),
            'id' => $page->getId(),
            'kitpages_target' => $target
        ));
    }

    public function editLinkAction(Page $page)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $target = $request->query->get('kitpages_target', null);

        // build basic form
        $pageParent = $page->getParent();
        $parentId = '';
        if ($pageParent instanceof Page) {
            $parentId = $pageParent->getId();
        }
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text', array('attr' => array('class'=>'kit-cms-advanced')));
        $builder->add('title', 'text');
        $builder->add('isInNavigation', 'checkbox', array('required' => false));
        $builder->add('menuTitle', 'text', array('required' => false));
        $builder->add(
            'language',
            'text',
            array(
                'label' => "Page language",
                'attr' => array('class'=>'kit-cms-advanced')
            )
        );

        $builder->add('linkUrl', 'text');
        $builder->add('parent_id','text',array(
            'property_path' => false,
            'data' => $parentId
        ));
        // get form
        $form = $builder->getForm();

        // persist form if needed
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->get('doctrine')->getEntityManager();
                $dataForm = $request->request->get('form');
                $parent_id = $dataForm['parent_id'];
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setParent($pageParent);
                }
                $em->flush();
                $this->getRequest()->getSession()->setFlash('notice', 'Page Link modified');
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            }
        }
        $view = $form->createView();
        return $this->render('KitpagesCmsBundle:Page:editLink.html.twig', array(
            'form' => $form->createView(),
            'id' => $page->getId(),
            'kitpages_target' => $target
        ));
    }

    public function toolbarZone(Zone $zone, $htmlZone, $authorizedBlockTemplateList = null) {
        $actionList[] = array(
            'label' => 'addBlock',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_create',
                array(
                    'zone_id' => $zone->getId(),
                    'position' => 0,
                    'kitpages_target' => $_SERVER['REQUEST_URI'],
                    'authorized_block_template_list' => $authorizedBlockTemplateList
                )
            ),
            'icon' => 'icon/add.png'
        );

        $dataRenderer = array(
            'title' => $zone->getSlug(),
            'actionList' => $actionList,
            'htmlBlock' => $htmlZone
        );
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbar.html.twig', $dataRenderer
        );
        return $resultingHtml;
    }

    public function publish(Page $page, $childrenPublish)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $listRenderer = $this->container->getParameter('kitpages_cms.block.renderer');
        if ($childrenPublish) {

            $em = $this->getDoctrine()->getEntityManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page);
            foreach($pageChildren as $pageChild) {
                $this->publish($pageChild, $childrenPublish);
            }
        }
        $pageManager->publish($page, $layoutList, $listRenderer);
    }

    public function publishAction(Page $page)
    {
        $childrenPublish = $this->get('request')->query->get('children', false);
        $this->publish($page, $childrenPublish);

        $this->getRequest()->getSession()->setFlash('notice', 'Page published');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
    }

    public function delete(Page $page, $childrenDelete)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        if ($childrenDelete) {

            $em = $this->getDoctrine()->getEntityManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page);
            foreach($pageChildren as $pageChild) {
                $this->delete($pageChild, $childrenDelete);
            }
        }
        $pageManager->delete($page);
    }

    public function pendingDelete(Page $page, $childrenDelete)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        if ($childrenDelete) {

            $em = $this->getDoctrine()->getEntityManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page);
            foreach($pageChildren as $pageChild) {
                $this->pendingDelete($pageChild, $childrenDelete);
            }
        }
        $pageManager->pendingDelete($page);
    }

    public function unpendingDelete(Page $page, $childrenUndelete)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        if ($childrenUndelete) {

            $em = $this->getDoctrine()->getEntityManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page);
            foreach($pageChildren as $pageChild) {
                $this->unpendingDelete($pageChild, $childrenUndelete);
            }
        }
        $pageManager->unpendingDelete($page);
    }

    public function deleteAction(Page $page)
    {
        $childrenDelete = $this->get('request')->query->get('children', true);
        $this->pendingDelete($page, $childrenDelete);
        $this->getRequest()->getSession()->setFlash('notice', 'Page pending delete');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }

    public function undeleteAction(Page $page)
    {
        $childrenUndelete = $this->get('request')->query->get('children', true);
        $this->unpendingDelete($page, $childrenUndelete);
        $this->getRequest()->getSession()->setFlash('notice', 'Page unpending delete');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }

//    public function deleteAction(Page $page)
//    {
//        $childrenDelete = $this->get('request')->query->get('children', false);
//        $this->delete($page, $childrenDelete);
//        $this->getRequest()->getSession()->setFlash('notice', 'Page deleted');
//        $target = $this->getRequest()->query->get('kitpages_target', null);
//        if ($target) {
//            return $this->redirect($target);
//        }
//        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
//    }

}
