<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\HiddenField;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Kitpages\FileBundle\Entity\File;
use Kitpages\CmsBundle\Form\BlockType;
use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Model\CmsManager;
use Kitpages\CmsBundle\Model\CmsFileManager;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class BlockController extends Controller
{


    public function viewAction()
    {
        return $this->render('KitpagesCmsBundle:Block:view.html.twig');
    }

    public function uploadWidgetAction($blockId, $fieldId, $parameterList = array())
    {
        $cmsFileManager = $this->get('kitpages.cms.manager.file');
        $resultingHtml = $this->get('templating.helper.actions')->render(
            new ControllerReference(
                'KitpagesFileBundle:Upload:widget',
                array(
                    'fieldId' => $fieldId,
                    'itemClass' => $cmsFileManager->getItemClassBlock(),
                    'itemId' => $blockId,
                    'parameterList' => $parameterList
                )
            )
        );

        // add a help message
        $fieldName = str_replace('kitpagesCmsEditBlock_data_root_media_', '', $fieldId);
        if (isset($parameterList['multi']) && $parameterList['multi'] ) {
            $resultingHtml .= '<div class="kit-cms-form-help">Note : To insert this media in the editor, use [[cms:media:'.$fieldName.'.NUM.default.url]], you must replace NUM by the correct number File</div>';
        } else {
            $resultingHtml .= '<div class="kit-cms-form-help">Note : To insert this media in the editor, use [[cms:media:'.$fieldName.'.0.default.url]]</div>';
        }
        return new Response($resultingHtml);
    }

    public function createAction(Request $request)
    {
        $block = new Block();

        $authorizedBlockTemplateList = $request->query->get("authorized_block_template_list", null);

        $templateList = $this->container->getParameter('kitpages_cms.block.template.template_list');
        $selectTemplateList = array();
        foreach ($templateList as $key => $template) {
            if( is_null($authorizedBlockTemplateList) || in_array($key, $authorizedBlockTemplateList) ) {
                $selectTemplateList[$key] = $template['name'];
            }
        }

        $block->setSlug($request->query->get('kitpagesBlockSlugDefault', null));
        //create automatic if one template
        if (count($selectTemplateList) == 1) {
            $templateKeyList = array_keys($selectTemplateList);
            $block->setBlockType('edito');
            $block->setIsPublished(false);
            $block->setTemplate($templateKeyList[0]);
            $em = $this->get('doctrine')->getManager();
            $em->persist($block);

            $dataForm = $request->request->get('form');
            $zone_id = $this->get('request')->query->get('zone_id');
            $position = $this->get('request')->query->get('position', null);
            if ($position == null) {
                $position = 0;
            }
            if (!empty($zone_id)) {
                $zoneBlock = new ZoneBlock();
                $zone = $em->getRepository('KitpagesCmsBundle:Zone')->find($zone_id);
                $zoneBlock->setZone($zone);
                $zoneBlock->setBlock($block);
                $zoneBlock->setPosition($position);
                $em->persist($zoneBlock);
                $em->flush();
            }
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Block created');
            return $this->redirect(
                $this->generateUrl(
                    'kitpages_cms_block_edit',
                    array(
                        'id' => $block->getId(),
                        'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
                    )
                )
            );
        }

        $form = $this->createForm('kitpagesCmsCreateBlock', $block, array('templateList' => $selectTemplateList));
        $form->get('zone_id')->setData($request->query->get('zone_id'));
        $form->get('position')->setData($request->query->get('position', null));

        $formHandler = $this->container->get('kitpages_cms.formHandler.createBlock');

        $process = $formHandler->process($form, $block);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            return $this->redirect(
                $this->generateUrl(
                    'kitpages_cms_block_edit',
                    array(
                        'id' => $block->getId(),
                        'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
                    )
                )
            );
        }

        return $this->render('KitpagesCmsBundle:Block:create.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
        ));
    }

    public function deleteAction(Block $block)
    {
        $blockManager = $this->get('kitpages.cms.manager.block');
        $blockManager->delete($block);

        $this->get('session')->getFlashBag()->add('notice', 'Block deleted');

        $target = $this->getRequest()->query->get('kitpages_target');
        if ($target) {
            return $this->redirect($target);
        }
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

    public function deletePublishedAction($kitpagesBlockSlug)
    {
        $em = $this->getDoctrine()->getManager();
        $blockPublishedList =$em->getRepository('KitpagesCmsBundle:BlockPublish')->findBy(array('slug' => $kitpagesBlockSlug));
        $blockManager = $this->get('kitpages.cms.manager.block');

        foreach($blockPublishedList as $blockPublished) {
            $blockManager->deletePublished($blockPublished);
        }
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', 'Block deleted');

        $target = $this->getRequest()->query->get('kitpages_target');
        if ($target) {
            return $this->redirect($target);
        }
        return Response(null);
    }

    public function editAction(Request $request, Block $block)
    {
        $em = $this->getDoctrine()->getManager();
        $authorizedBlockTemplateList = $request->query->get("authorized_block_template_list", null);

        if (!$block->getData()) {
            $block->setData(array('root'=>null));
        }
        // build template list
        $templateList = $this->container->getParameter('kitpages_cms.block.template.template_list');
        $selectTemplateList = array();
        foreach ($templateList as $key => $template) {
            if( is_null($authorizedBlockTemplateList) || in_array($key, $authorizedBlockTemplateList) ) {
                $selectTemplateList[$key] = $template['name'];
            }
        }
        $twigTemplate = $templateList[$block->getTemplate()]['twig'];

        // build custom form
        if (isset($templateList[$block->getTemplate()]['class'])) {
            $className = $templateList[$block->getTemplate()]['class'];
            $formData = new $className();
        } else {
            $formData = $this->get($templateList[$block->getTemplate()]['service']);
        }

        $form = $this->createForm(
            'kitpagesCmsEditBlock',
            $block,
            array(
                'templateList' => $selectTemplateList,
                'formTypeCustom' => $formData
            )
        );

        $formHandler = $this->container->get('kitpages_cms.formHandler.editBlock');

        $process = $formHandler->process($form, $formData, $block);
        if ($process['result'] === true) {
            $this->get('session')->getFlashBag()->add('notice', $process['msg']);
            $target = $request->query->get('kitpages_target', null);
            if ($target) {
                return $this->redirect($target);
            }
            return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
        }

        $cmsFileManager = $this->get('kitpages.cms.manager.file');

        $blockData = $block->getData();
        if (!isset($blockData['root'])) {
            $blockData['root'] = array();
        }
        $mediaList = $cmsFileManager->mediaList($blockData['root'], false);

        return $this->render($twigTemplate, array(
            'form' => $form->createView(),
            'id' => $block->getId(),
            'mediaList' => $mediaList,
            'kitpages_target' => $request->query->get('kitpages_target', null)
        ));
    }

    public function toolbar(Block $block, $authorizedBlockTemplateList) {

        $dataRenderer['actionList'][] = array(
            'id' => '',
            'label' => 'edit',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_edit',
                array(
                    'id' => $block->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI'],
                    'authorized_block_template_list' => $authorizedBlockTemplateList
                )
            ),
            'icon' => 'icon/edit.png'
        );
        $dataRenderer['actionList'][] = array(
            'id' => 'publish',
            'label' => 'publish',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_publish',
                array(
                    'id' => $block->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'class' => ($block->getIsPublished() == '1')?'kit-cms-advanced':'',
            'icon' => 'icon/publish.png'
        );
        $dataRenderer['actionList'][] = array(
            'id' => '',
            'label' => 'delete',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_delete',
                array(
                    'id' => $block->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'icon' => 'icon/delete.png',
            'class' => 'kit-cms-delete-button'
        );
        $dataRenderer['isPublished'] = $block->getIsPublished();
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Block:toolbar.html.twig', $dataRenderer
        );
        return $resultingHtml;
    }


    public function widgetAction(
        $slug,
        $renderer = 'default',
        $displayToolbar = true,
        $authorizedBlockTemplateList = null,
        $filterParameterList = array()
    )
    {
        $em = $this->getDoctrine()->getManager();
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        $blockManager = $this->get('kitpages.cms.manager.block');
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $slug));
            if ($block == null) {

                $responseHtml = 'Please '.
                    '<a href="'.
                    $this->generateUrl(
                        "kitpages_cms_block_create",
                        array(
                            "kitpages_target"=> $_SERVER["REQUEST_URI"],
                            "kitpagesBlockSlugDefault" => $slug,
                            "authorized_block_template_list" => $authorizedBlockTemplateList
                        )
                    ).
                    '">create a block</a> with the slug "'. $slug.'"';

                $blockPublishList = $em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('slug' => $slug));
                if($blockPublishList != null) {
                    $responseHtml .= '<br />Block deleted but no published '.
                        '<a href="'.
                        $this->generateUrl(
                            "kitpages_cms_block_delete_published",
                            array(
                                "kitpages_target"=> $_SERVER["REQUEST_URI"],
                                "kitpagesBlockSlug" => $slug
                            )
                        ).
                        '"><img src="'.$this->container->get('templating.helper.assets')->getUrl("bundles/kitpagescms/icon/publish.png").'"> </a> with the slug "'. $slug.'"';
                }
                return new Response($responseHtml);
            }

            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {

                $resultingHtml = '';
                if ($displayToolbar == true) {
                    $resultingHtml .= $this->toolbar($block, $authorizedBlockTemplateList);
                }
                if (!is_null($block->getData())) {
                    $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
                    $resultingHtml .= '<div class="kit-cms-block-container">'.
                        $this->widgetPostFilter($blockManager->render($dataRenderer[$renderer], $block, $context->getViewMode()) , $filterParameterList).
                        '</div>';
                }
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $slug));
            if ($block == null) {
                return new Response('Please create a block with the slug "'.htmlspecialchars($slug).'"');
            }

            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                if (!is_null($block->getData())) {
                    $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
                    $resultingHtml = $blockManager->render($dataRenderer[$renderer], $block, $context->getViewMode());
                    $resultingHtml = $this->widgetPostFilter($resultingHtml, $filterParameterList);
                }
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('slug' => $slug, 'renderer' => $renderer));
            if (!is_null($blockPublish)) {
                $data = $blockPublish->getData();
                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                    $resultingHtml = $data['html'];
                    $resultingHtml = $this->widgetPostFilter($resultingHtml, $filterParameterList);
                }
            } else {
                //return new Response('The block with the slug "'.htmlspecialchars($slug).'" is not published');
                return new Response();
            }
        }
        return new Response($resultingHtml);
    }

    public function widgetPostFilter($resultingHtml, $filterParameterList)
    {
        foreach ($filterParameterList as $paramName => $paramValue) {
            $resultingHtml = str_replace("[[cms:parameter:$paramName]]", $paramValue, $resultingHtml);
        }
        return $resultingHtml;
    }

    public function editSuccessAction()
    {
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }

    public function publishAction(Block $block)
    {
        $blockManager = $this->get('kitpages.cms.manager.block');
        $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
        $blockManager->publish($block, $dataRenderer);

        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $zoneManager->updateBlockPublishId($block);

        $this->get('session')->getFlashBag()->add('notice', 'Block published');

        $target = $this->getRequest()->query->get('kitpages_target');
        if ($target) {
            return $this->redirect($target);
        }
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

//    public function unpublishAction(Block $block)
//    {
//        $blockManager = $this->get('kitpages.cms.manager.block');
//        $blockManager->fireUnpublish($block);
//
//        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
//    }

}
