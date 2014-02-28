<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\PageZone;
use Kitpages\CmsBundle\Model\CmsFileManager;

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

    public function uploadWidgetAction($pageId, $fieldId, $parameterList)
    {
        $cmsFileManager = $this->get('kitpages.cms.manager.file');
        $resultingHtml = $this->get('templating.helper.actions')->render(
            new ControllerReference(
                'KitpagesFileBundle:Upload:widget',
                array(
                    'fieldId' => $fieldId,
                    'itemClass' => $cmsFileManager->getItemClassPage(),
                    'itemId' => $pageId,
                    'parameterList' => $parameterList
                )
            )
        );
        return new Response($resultingHtml);
    }

    public function viewAction(Page $page, $lang, $urlTitle)
    {
        $em = $this->getDoctrine()->getManager();
        $context = $this->get('kitpages.cms.controller.context');
        $rendererTwig = $this->container->getParameter('kitpages_cms.page.renderer_twig_main');
        $pageId = $page->getId();
        $pageType = $page->getPageType();
        $pageLanguage = $page->getLanguage();
        $pageUrlTitle = $page->getUrlTitle();
        $pageLayout = $page->getLayout();
        $forcedUrl = $page->getForcedUrl();
        $data = array();

        if ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);
            if ($pagePublish == null ) {
                throw new NotFoundHttpException('The page does not exist.');
            }
            $pageType = $pagePublish->getPageType();
            $pageLanguage = $pagePublish->getLanguage();
            $pageUrlTitle = $pagePublish->getUrlTitle();
            $pageLayout = $pagePublish->getLayout();
            $forcedUrl = $pagePublish->getForcedUrl();
            $data = $pagePublish->getData();
        } else {
            $dataInheritanceList = $this->container->getParameter('kitpages_cms.page.data_inheritance_list');
            $dataRoot = $em->getRepository('KitpagesCmsBundle:Page')->getDataWithInheritance($page, $dataInheritanceList);
            $data['root'] = $dataRoot;
            $data['page'] = $page->getDataPage();
            $cmsFileManager = $cmsFileManager = $this->get('kitpages.cms.manager.file');
            $listMedia = $cmsFileManager->mediaList($data['root'], false);
            $data['media'] = $listMedia;
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
            $rendererTwig,
            array(
                'kitCmsViewMode' => $context->getViewMode(),
                'kitCmsPage' => $page,
                'kitCmsPageData' => $data
            )
        );
    }

    public function widgetZoneAction($location_in_page, Page $page, $title = 'zone') {
        $em = $this->getDoctrine()->getManager();
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
            new ControllerReference(
                "KitpagesCmsBundle:Zone:widget",
                array(
                    "slug" => $zone->getSlug(),
                    "renderer" =>$layout['zone_list'][$location_in_page]['renderer'],
                    'displayToolbar' => false,
                    'authorizedBlockTemplateList' => $layout['zone_list'][$location_in_page]['authorized_block_template_list']
                )
            )
        );
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $resultingHtml = $this->toolbarZone($zone, $resultingHtml, $layout['zone_list'][$location_in_page]['authorized_block_template_list'], $title);
        }
        return new Response($resultingHtml);
    }

    public function choiceCreateAction(){
        $em = $this->get('doctrine')->getManager();
        $next_sibling_slug = $this->get('request')->query->get('next_sibling_slug', null);
        if ($next_sibling_slug != null) {
            $slug = $next_sibling_slug;
        }
        $prev_sibling_slug = $this->get('request')->query->get('prev_sibling_slug', null);
        if ($prev_sibling_slug != null) {
            $slug = $prev_sibling_slug;
        }
        $target = $this->getRequest()->query->get('kitpages_target', null);
        $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($slug);
        return $this->render('KitpagesCmsBundle:Page:choiceCreate.html.twig', array(
            'next_sibling_slug' => $next_sibling_slug,
            'prev_sibling_slug' => $prev_sibling_slug,
            'parent_id' => $page->getParent()->getId(),
            'page_id' => $page->getId(),
            'kitpages_target' => $target
        ));
    }

    public function createAction(Request $request)
    {
        $page = new Page();

        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $selectLayoutList = array();
        foreach ($layoutList as $key => $layout) {
            $selectLayoutList[$key] = $key;
        }

        $form = $this->createForm('kitpagesCmsCreatePage', $page, array('layoutList' => $selectLayoutList));
        $form->get('parent_id')->setData($request->query->get('parent_id'));
        $form->get('next_sibling_slug')->setData($request->query->get('next_sibling_slug', null));
        $form->get('prev_sibling_slug')->setData($request->query->get('prev_sibling_slug', null));

        $formHandler = $this->container->get('kitpages_cms.formHandler.createPage');

        $process = $formHandler->process($form, $page);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
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


        return $this->render('KitpagesCmsBundle:Page:create.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }

    public function createZoneInPageAction(Page $page, $locationInPage)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        $pageManager->createZoneInPage($page, $locationInPage);
        $this->get('session')->getFlashBag()->add('notice', "Zone $locationInPage created");
        $target = $this->getRequest()->query->get('kitpages_target', null);
        return $this->redirect($target);
    }

    public function createTechnicalAction(Request $request)
    {
        $page = new Page();

        $parent_id = $this->get('request')->query->get('parent_id', null);

        $form = $this->createForm('kitpagesCmsCreatePageTechnical', $page, array('parent_id' => $parent_id));
        $form->get('parent_id')->setData($request->query->get('parent_id'));
        $form->get('next_sibling_slug')->setData($request->query->get('next_sibling_slug', null));
        $form->get('prev_sibling_slug')->setData($request->query->get('prev_sibling_slug', null));

        $formHandler = $this->container->get('kitpages_cms.formHandler.createPageTechnical');

        $process = $formHandler->process($form, $page);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            $target = $this->getRequest()->query->get('kitpages_target', null);
            if ($target) {
                return $this->redirect($target);
            }
        }

        return $this->render('KitpagesCmsBundle:Page:createTechnical.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }



    public function createLinkAction(Request $request)
    {
        $page = new Page();

        $form = $this->createForm('kitpagesCmsCreatePageLink', $page);
        $form->get('parent_id')->setData($request->query->get('parent_id'));
        $form->get('next_sibling_slug')->setData($request->query->get('next_sibling_slug', null));
        $form->get('prev_sibling_slug')->setData($request->query->get('prev_sibling_slug', null));

        $formHandler = $this->container->get('kitpages_cms.formHandler.createPageLink');

        $process = $formHandler->process($form, $page);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            $target = $this->getRequest()->query->get('kitpages_target', null);
            if ($target) {
                return $this->redirect($target);
            }
        }

        return $this->render('KitpagesCmsBundle:Page:createLink.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }

    public function editAction(Request $request, Page $page, $inToolbar = false, $target = null)
    {
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

        // build custom form
        if (isset($layout['data_form_class'])) {
            $className = $layout['data_form_class'];
            $formData = new $className();
        } else {
            $formData = $this->get($layout['data_form_service']);
        }

        $form = $this->createForm(
            'kitpagesCmsEditPage',
            $page,
            array(
                'formTypeCustom' => $formData
            )
        );
        $form->get('parent_id')->setData($parentId);

        $formHandler = $this->container->get('kitpages_cms.formHandler.editPage');

        $process = $formHandler->process($form, $page);
        if (isset($process['msg'])) {
            if ($process['result']) {
                $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            } else {
                $this->get('session')->getFlashBag()->add('error', $process['msg']);
            }

            if (is_null($target)) {
                if ($process['forcedUrl'] != null) {
                    return $this->redirect($process['forcedUrl']);
                } else {
                    return $this->redirect($this->generateUrl(
                        'kitpages_cms_page_view_lang',
                        array(
                            'id' => $page->getId(),
                            'lang' => $page->getLanguage(),
                            'urlTitle' => $page->getUrlTitle()
                        )
                    ));
                }
            } else {
                return $this->redirect($target);
            }
        }

        return $this->render($layout['data_form_twig'], array(
            'form' => $form->createView(),
            'id' => $page->getId(),
            'inToolbar' => $inToolbar,
            'kitpages_target' => $target
        ));
    }

    public function editTechnicalAction(Request $request, Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $target = $request->query->get('kitpages_target', null);

        $pageParent = $page->getParent();
        $parentId = '';
        if ($pageParent instanceof Page) {
            $parentId = $pageParent->getId();
        }

        if (!$page->getData()) {
            $page->setData(array('root'=>null));
        }

        $formData = null;
        if ($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_class')) {
            $classNameFormInheritance = $this->container->getParameter('kitpages_cms.page.data_inheritance_form_class');
            // build custom form
            $formData = new $classNameFormInheritance();
        } elseif($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_service')) {
            $formData = $this->get($this->container->getParameter('kitpages_cms.page.data_inheritance_form_class'));
        }
        $form = $this->createForm(
            'kitpagesCmsEditPageTechnical',
            $page,
            array('formTypeCustom' => $formData)
        );
        $form->get('parent_id')->setData($parentId);

        $formHandler = $this->container->get('kitpages_cms.formHandler.editPageTechnical');

        $process = $formHandler->process($form, $page);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            if ($target) {
                return $this->redirect($target);
            }
            return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
        }

        $renderer = 'KitpagesCmsBundle:Page:editTechnical.html.twig';
        if ($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_twig')) {
            $renderer = $this->container->getParameter('kitpages_cms.page.data_inheritance_form_twig');
        }

        return $this->render($renderer, array(
            'renderMain' => 'KitpagesCmsBundle:Page:editTechnical.html.twig',
            'form' => $form->createView(),
            'id' => $page->getId(),
            'kitpages_target' => $target
        ));
    }

    public function editLinkAction(Request $request, Page $page)
    {
        $em = $this->getDoctrine()->getManager();
        $target = $request->query->get('kitpages_target', null);

        // build basic form
        $pageParent = $page->getParent();
        $parentId = '';
        if ($pageParent instanceof Page) {
            $parentId = $pageParent->getId();
        }

        if (!$page->getData()) {
            $page->setData(array('root'=>null));
        }

        $formData = null;
        if ($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_class')) {
            $classNameFormInheritance = $this->container->getParameter('kitpages_cms.page.data_inheritance_form_class');
            // build custom form
            $formData = new $classNameFormInheritance();
        } elseif($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_service')) {
            $formData = $this->get($this->container->getParameter('kitpages_cms.page.data_inheritance_form_class'));
        }

        $form = $this->createForm(
            'kitpagesCmsEditPageLink',
            $page,
            array(
                'formTypeCustom' => $formData
            )
        );
        $form->get('parent_id')->setData($parentId);

        $formHandler = $this->container->get('kitpages_cms.formHandler.editPageLink');

        $process = $formHandler->process($form, $page);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            if ($target) {
                return $this->redirect($target);
            }
            return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
        }

        $renderer = 'KitpagesCmsBundle:Page:editLink.html.twig';
        if ($this->container->hasParameter('kitpages_cms.page.data_inheritance_form_twig')) {
            $renderer = $this->container->getParameter('kitpages_cms.page.data_inheritance_form_twig');
        }

        return $this->render($renderer, array(
            'renderMain' => 'KitpagesCmsBundle:Page:editLink.html.twig',
            'form' => $form->createView(),
            'id' => $page->getId(),
            'kitpages_target' => $target
        ));
    }

    public function toolbarZone(Zone $zone, $htmlZone, $authorizedBlockTemplateList = null, $title = 'zone') {
        $actionList[] = array(
            'id' => '',
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
            'kitCmsZoneSlug' => $zone->getSlug(),
            'isPublished' => $zone->getIsPublished(),
            'actionList' => $actionList,
            'htmlBlock' => $htmlZone,
            'title' => $title
        );
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbar.html.twig', $dataRenderer
        );
        return $resultingHtml;
    }

    public function publishAction(Page $page)
    {
        ini_set ('max_execution_time', 3000);
        ini_set ('memory_limit', "750M");
        $layoutList = $this->container->getParameter('kitpages_cms.page.layout_list');
        $listRenderer = $this->container->getParameter('kitpages_cms.block.renderer');
        $dataInheritanceList = $this->container->getParameter('kitpages_cms.page.data_inheritance_list');
        $pageManager = $this->get('kitpages.cms.manager.page');
        $childrenPublish = $this->get('request')->query->get('children', false);
        $pageManager->publish($page, $layoutList, $listRenderer, $dataInheritanceList, $childrenPublish);
        $this->get('session')->getFlashBag()->add('notice', 'Page published');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_nav_tree'));
    }

    public function delete(Page $page, $childrenDelete)
    {
        $pageManager = $this->get('kitpages.cms.manager.page');
        if ($childrenDelete) {

            $em = $this->getDoctrine()->getManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
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

            $em = $this->getDoctrine()->getManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
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

            $em = $this->getDoctrine()->getManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
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
        $this->get('session')->getFlashBag()->add('notice', 'Page pending delete');
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
        $this->get('session')->getFlashBag()->add('notice', 'Page unpending delete');
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
//        $this->get('session')->getFlashBag()->add('notice', 'Page deleted');
//        $target = $this->getRequest()->query->get('kitpages_target', null);
//        if ($target) {
//            return $this->redirect($target);
//        }
//        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
//    }

}
