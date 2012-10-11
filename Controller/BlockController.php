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
            'KitpagesFileBundle:Upload:widget',
            array(
                'fieldId' => $fieldId,
                'itemClass' => $cmsFileManager->getItemClassBlock(),
                'itemId' => $blockId,
                'parameterList' => $parameterList
            )
        );

        // add a help message
        $fieldName = str_replace('form_data_root_media_', '', $fieldId);
        if (isset($parameterList['multi']) && $parameterList['multi'] ) {
            $resultingHtml .= '<div class="kit-cms-form-help">Note : To insert this media in the editor, use [[cms:media:'.$fieldName.'.NUM.default.url]], you must replace NUM by the correct number File</div>';
        } else {
            $resultingHtml .= '<div class="kit-cms-form-help">Note : To insert this media in the editor, use [[cms:media:'.$fieldName.'.0.default.url]]</div>';
        }
        return new Response($resultingHtml);
    }

    public function createAction()
    {
        $block = new Block();
        $request = $this->getRequest();

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
                $em->persist($zoneBlock);
                $em->flush();
                $zoneBlock->setPosition($position);
            }
            $em->flush();
            $this->getRequest()->getSession()->setFlash('notice', 'Block created');
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

        // build basic form
        $builder = $this->createFormBuilder($block);
        $builder->add(
            'slug',
            'text',
            array(
                'required' => false,
                'attr' => array('class'=>'kit-cms-advanced'),
            )
        );
        $builder->add('zone_id','hidden',array(
            'property_path' => false,
            'data' => $this->get('request')->query->get('zone_id')
        ));
        $builder->add('position','hidden',array(
            'required' => false,
            'property_path' => false,
            'data' => $this->get('request')->query->get('position', null)
        ));
        $builder->add('template', 'choice',array(
            'choices' => $selectTemplateList,
            'required' => true
        ));
        // get form
        $form = $builder->getForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $block->setBlockType('edito');
                $block->setIsPublished(false);
                $em = $this->get('doctrine')->getManager();
                $em->persist($block);

                $dataForm = $request->request->get('form');
                $zone_id = $dataForm['zone_id'];
                $position = $dataForm['position'];
                if ($position == null) {
                    $position = 0;
                }
                if (!empty($zone_id)) {
                    $zoneBlock = new ZoneBlock();
                    $zone = $em->getRepository('KitpagesCmsBundle:Zone')->find($zone_id);
                    $zoneBlock->setZone($zone);
                    $zoneBlock->setBlock($block);
                    $em->persist($zoneBlock);
                    $em->flush();
                    $zoneBlock->setPosition($position);
                }
                $em->flush();
                $this->getRequest()->getSession()->setFlash('notice', 'Block created');
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

        $this->getRequest()->getSession()->setFlash('notice', 'Block deleted');

        $target = $this->getRequest()->query->get('kitpages_target');
        if ($target) {
            return $this->redirect($target);
        }
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

    public function editAction(Block $block)
    {
        $em = $this->getDoctrine()->getManager();

        $request = $this->getRequest();
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

        // build basic form
        $builder = $this->createFormBuilder($block);
        $builder->add(
            'slug',
            'text',
            array(
                'attr' => array('class'=>'kit-cms-advanced'),
                'error_bubbling' => true
            )
        );
        $builder->add('template', 'choice',array(
            'attr' => array('class'=>'kit-cms-advanced'),
            'choices' => $selectTemplateList,
            'required' => true
        ));

        $builder->add(
            'canonicalUrl',
            'text',
            array(
                'attr' => array('class'=>'kit-cms-advanced'),
                'required' => false
            )
        );

        // build custom form
        $className = $templateList[$block->getTemplate()]['class'];
        $formData = new $className();
        $builder->add('data', 'collection', array(
           'type' => $formData,
        ));
        // get form
        $form = $builder->getForm();

        // persist form if needed
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $blockManager = $this->get('kitpages.cms.manager.block');

            $oldBlockData = $block->getData();

            $form->bind($request);

            $formChildren = $form->getChildren();
            $blockData = $block->getData();

            $reflector = new \ReflectionObject($formData);
            if ($reflector->hasMethod('filterList')) {
                foreach($formData->filterList() as $field => $method) {
                    $blockData['root'][$field] = $blockManager->$method($blockData['root'][$field]);
                }
            }
            $block->setData($blockData);

            if ($form->isValid()) {
                $em->flush();

                $blockManager->afterModify($block, $oldBlockData);
                $this->getRequest()->getSession()->setFlash('notice', 'Block modified');
                $target = $request->query->get('kitpages_target', null);
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            } else {
                $msg = 'Block not saved <br />';
                $this->getRequest()->getSession()->setFlash('error', $msg);
            }
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
                return new Response(
                    'Please '.
                    '<a href="'.
                    $this->generateUrl(
                        "kitpages_cms_block_create",
                        array(
                            "kitpages_target"=> $_SERVER["REQUEST_URI"],
                            "kitpagesBlockSlugDefault" => $slug
                        )
                    ).
                    '">create a block</a> with the slug "'. $slug.'"'
                );
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

        $this->getRequest()->getSession()->setFlash('notice', 'Block published');

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
