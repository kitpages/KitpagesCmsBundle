<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\PageZone;

class PageController extends Controller
{

    public function widgetToolbarAction(Page $page) {
        $context = $this->get('kitpages.cms.controller.context');
        $dataRender = array(
            'viewMode' => $context->getViewMode(),
            'page' => $page,
            'target' => $_SERVER["REQUEST_URI"]
        );
        return $this->render('KitpagesCmsBundle:Page:toolbar.html.twig', $dataRender);
    }   
   
    public function viewAction(Page $page, $lng, $urlTitle)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $pageId = $page->getId();
        $pageType = $page->getPageType();
        $pageLanguage = $page->getLanguage();
        $pageUrlTitle = $page->getUrlTitle();
        $pageLayout = $page->getLayout();
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {

        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);
            $pageType = $pagePublish->getPageType();
            $pageLanguage = $pagePublish->getLanguage();
            $pageUrlTitle = $pagePublish->getUrlTitle();
            $pageLayout = $pagePublish->getLayout();
        }
        
        if ($pageType == "technical") {
            throw new NotFoundHttpException('The page does not exist.');
        }

        if ($pageType == "link") {
            return $this->redirect ($page->getLinkUrl(), 301);
        }
        
        if ($pageLanguage != $lng || $pageUrlTitle != $urlTitle) {
            return $this->redirect ($this->generateUrl(
                        'kitpages_cms_page_view_lng',
                        array(
                            'id' => $pageId,
                            'lng' => $pageLanguage,
                            'urlTitle' => $pageUrlTitle
                        )
                    ), 301); 
        }
     
        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$pageLayout);
        $cmsManager->setLayout($layout['twig']);
        
        return $this->render('KitpagesCmsBundle:page:layout_page.html.twig', array('viewMode' => $context->getViewMode(), 'page' => $page));        
    }

    public function widgetZoneAction($location_in_page, Page $page) {
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findByPageAndLocation($page, $location_in_page);
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$page->getLayout());
        if ($zone == null) {
            return new Response('Please create a zone with the location "'.htmlspecialchars($location_in_page).'"');
        }
        
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Zone:widget", 
                    array(
                        "label" => $zone->getSlug(),
                        "renderer" =>$layout['zone_list'][$location_in_page]['render'],
                        'displayToolbar' => false
                    ),
                    array()
        );
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $resultingHtml = $this->toolbarZone($zone, $resultingHtml);
        }
        return new Response($resultingHtml);
    }
    
    public function createAction()
    {
        $page = new Page();

        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $selectLayoutList = array();
        foreach ($layoutList as $key => $layout) {
            $selectLayoutList[$key] = $key;
        }
    
        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text');
        $builder->add('title', 'text');        
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
                $page->setIsActive(true);
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
                $zoneList = $layout['zone_list'];
                foreach($zoneList as $locationInPage => $render) {
                    $zone = new Zone();
                    $zone->setSlug('');
                    $zone->setIsPublished(false);
                    $em->persist($zone);
                    $em->flush();
                    $pageZone = new PageZone();
                    $pageZone->setPage($page);
                    $pageZone->setZone($zone);
                    $pageZone->setLocationInPage($locationInPage);
                    $em->persist($pageZone);
                    $em->flush();                    
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

    public function createTechnicalAction()
    {
        $page = new Page();

        $parent_id = $this->get('request')->query->get('parent_id', null);
        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text');
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
                $page->setIsActive(true);
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
        $builder->add('slug', 'text');
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
                $page->setIsActive(true);
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
        $builder->add('slug', 'text');
        $builder->add('title', 'text');
        $builder->add('isInNavigation', 'checkbox', array('required' => false));           
        $builder->add('menuTitle', 'text', array('required' => false)); 
        $builder->add('parent_id','text',array(
            'property_path' => false,
            'data' => $parentId
        ));         
  
        $builder->add('language', 'text');
        $builder->add('isActive', 'checkbox');

        // build custom form
        $className = $layout['class_data'];
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
        return $this->render($layout['twig_data'], array(
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
        $builder->add('slug', 'text');
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
        $builder->add('slug', 'text');
        $builder->add('title', 'text'); 
        $builder->add('isInNavigation', 'checkbox', array('required' => false));           
        $builder->add('menuTitle', 'text', array('required' => false)); 
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

    public function toolbarZone(Zone $zone, $htmlZone) {  
        $listAction['addBlock'] = $this->get('router')->generate(
            'kitpages_cms_block_create', 
            array(
                'zone_id' => $zone->getId(),
                'kitpages_target' => $_SERVER['REQUEST_URI']
            )

        );
        
        $dataRenderer = array(
            'title' => $zone->getSlug(),
            'listAction' => $listAction,
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
