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

class BlockController extends Controller
{


    public function viewAction()
    {
        return $this->render('KitpagesCmsBundle:Block:view.html.twig');
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
            $form->bindRequest($request);

            if ($form->isValid()) {
                $block->setBlockType('edito');
                $block->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
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
        $em = $this->getDoctrine()->getEntityManager();

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
        $builder->add('slug', 'text', array('attr' => array('class'=>'kit-cms-advanced')));
        $builder->add('template', 'choice',array(
            'attr' => array('class'=>'kit-cms-advanced'),
            'choices' => $selectTemplateList,
            'required' => true
        ));

        // build custom form
        $className = $templateList[$block->getTemplate()]['class'];
        $builder->add('data', 'collection', array(
           'type' => new $className(),
        ));

        // get form
        $form = $builder->getForm();

        // persist form if needed
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $oldBlockData = $block->getData();
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em->flush();
                $blockManager = $this->get('kitpages.cms.manager.block');
                $blockManager->afterModify($block, $oldBlockData);
                $this->getRequest()->getSession()->setFlash('notice', 'Block modified');
                $target = $request->query->get('kitpages_target', null);
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            }
        }
        $view = $form->createView();

        $fileManager = $this->get('kitpages.cms.manager.file');
        $mediaUrlList = $fileManager->urlListInBlockData($block->getData(), false);

        return $this->render($twigTemplate, array(
            'form' => $form->createView(),
            'id' => $block->getId(),
            'mediaList' => $mediaUrlList,
            'kitpages_target' => $request->query->get('kitpages_target', null)
        ));
    }

    public function toolbar(Block $block, $authorizedBlockTemplateList) {

        $dataRenderer['actionList'][] = array(
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
            'label' => 'publish',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_publish',
                array(
                    'id' => $block->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            )
        );

        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Block:toolbar.html.twig', $dataRenderer
        );
        return $resultingHtml;
    }


    public function widgetAction(
        $slug,
        $renderer = 'default',
        $displayToolbar = true,
        $authorizedBlockTemplateList = null
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
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
//                        print_r($dataRenderer, true).
                        $blockManager->render($dataRenderer[$renderer], $block, $context->getViewMode()).
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
                }
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('slug' => $slug, 'renderer' => $renderer));
            if (!is_null($blockPublish)) {
                $data = $blockPublish->getData();
                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                    $resultingHtml = $data['html'];
                }
            } else {
                return new Response('The block with the slug "'.htmlspecialchars($slug).'" is not published');
            }
        }
        return new Response($resultingHtml);
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
