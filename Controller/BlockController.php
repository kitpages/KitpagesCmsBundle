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
        $request = Request::createFromGlobals();

        $templateList = $this->container->getParameter('kitpages_cms.block.template.template_list');
        $selectTemplateList = array();
        foreach ($templateList as $key => $template) {
            $selectTemplateList[$key] = $template['name'];
        }

        
        // build basic form
        $builder = $this->createFormBuilder($block);
        $builder->add('slug', 'text');
        $builder->add('zone_id','hidden',array(
            'property_path' => false,
            'data' => $this->get('request')->query->get('zone_id')
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
                $block->setIsActive(true);
                $block->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($block);

                $dataForm = $request->request->get('form');
                $zone_id = $dataForm['zone_id'];
                if (!empty($zone_id)) {
                    $zoneBlock = new ZoneBlock();
                    $zone = $em->getRepository('KitpagesCmsBundle:Zone')->find($zone_id);
                    $zoneBlock->setZone($zone);
                    $zoneBlock->setBlock($block);
                    $em->persist($zoneBlock);
                }
                $em->flush();
                return $this->redirect($this->generateUrl('kitpages_cms_block_edit', array('id' => $block->getId() )));
            }
        }
        return $this->render('KitpagesCmsBundle:Block:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $block = $em->getRepository('KitpagesCmsBundle:Block')->find($id);
        if (!$block->getData()) {
            $block->setData(array('root'=>null));
        }
        // build template list
        $templateList = $this->container->getParameter('kitpages_cms.block.template.template_list');
        $selectTemplateList = array();
        foreach ($templateList as $key => $template) {
            $selectTemplateList[$key] = $template['name'];
        }
        $twigTemplate = $templateList[$block->getTemplate()]['twig'];
        
        // build basic form
        $builder = $this->createFormBuilder($block);
        $builder->add('slug', 'text');
        $builder->add('template', 'choice',array(
            'choices' => $selectTemplateList,
            'required' => true
        ));
        $builder->add('isActive', 'checkbox');

        // build custom form
        $className = $templateList[$block->getTemplate()]['class'];
        $builder->add('data', 'collection', array(
           'type' => new $className(),
        ));
        
        // get form
        $form = $builder->getForm();
        
        // persist form if needed
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($block);
                $em->flush();

                return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
            }
        }
        $view = $form->createView();
        //echo '<pre>'.print_r($view, true).'</pre>';
        return $this->render($twigTemplate, array(
            'form' => $form->createView(),
            'id' => $block->getId()
        ));
    }

    public function toolbar(Block $block) {  
        $dataRenderer['listAction']['edit'] = $this->get('router')->generate(
            'kitpages_cms_block_edit', 
            array('id' => $block->getId())
        );
        $dataRenderer['listAction']['unpublish'] = $this->get('router')->generate(
            'kitpages_cms_block_unpublish', 
            array('id' => $block->getId())
        );
        $dataRenderer['listAction']['delete'] = $this->get('router')->generate(
            'kitpages_cms_zoneblock_delete', 
            array('id' => $block->getId())
        );
        
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Block:toolbar.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 

    public function widgetAction($label, $renderer = 'default') {
       
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $label));
            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {

                if (!is_null($block->getData())) {
                    $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
                    $resultingHtml = $this->toolbar($block)
                        .'<div class="kit-cms-block-container">'.
                        $this->renderView(
                            $dataRenderer[$renderer]['twig'],
                            array('data' => $block->getData())
                        ).
                        '</div>';
                }
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $label));
            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                if (!is_null($block->getData())) {                
                    $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
                    $resultingHtml = $this->renderView($dataRenderer[$renderer]['twig'], array('data' => $block->getData()));
                }
            }          
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('slug' => $label, 'renderer' => $renderer));
            if (!is_null($blockPublish)) {
                $data = $blockPublish->getData();
                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                    $resultingHtml = $data['html'];
                }
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
        $blockManager->firePublish($block, $dataRenderer);
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

    public function unpublishAction(Block $block)
    {
        $blockManager = $this->get('kitpages.cms.manager.block');
        $blockManager->fireUnpublish($block);

        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

}
