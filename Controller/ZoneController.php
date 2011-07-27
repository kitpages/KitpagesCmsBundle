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
use Symfony\Component\HttpFoundation\RedirectResponse;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Kitpages\CmsBundle\Entity\ZonePublish;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Controller\Context;

class ZoneController extends Controller
{
    
    
    public function createAction()
    {
        $zone = new Zone();

        // build basic form
        $builder = $this->createFormBuilder($zone);
        $builder->add('slug', 'text');
        // get form
        $form = $builder->getForm();
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $zone->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($zone);
                $em->flush();

                return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');;
            }
        }
        return $this->render('KitpagesCmsBundle:Zone:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function toolbar(Zone $zone, $htmlBlock) {  
        $listAction['addBlock'] = $this->get('router')->generate(
            'kitpages_cms_block_create', 
            array(
                'zone_id' => $zone->getId(),
                'kitpages_target' => $_SERVER['REQUEST_URI']
            )

        );
        $listAction['publish'] = $this->get('router')->generate(
            'kitpages_cms_zone_publish',
            array(
                'id' => $zone->getId(),
                'kitpages_target' => $_SERVER['REQUEST_URI']
            )
        );
        
        $dataRenderer = array(
            'title' => $zone->getSlug(),
            'listAction' => $listAction,
            'htmlBlock' => $htmlBlock
        );
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbar.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 
    
    public function toolbarBlock(Zone $zone, Block $block)
    {
        $dataRenderer['listAction']['edit'] = $this->get('router')->generate(
            'kitpages_cms_block_edit', 
            array(
                'id' => $block->getId(),
                'kitpages_target' => $_SERVER['REQUEST_URI']
            )
        );
        $dataRenderer['listAction']['delete'] = $this->get('router')->generate(
            'kitpages_cms_block_delete', 
            array(
                'id' => $block->getId(),
                'kitpages_target' => $_SERVER['REQUEST_URI']
            )
        );

        $dataUrl = array(
            'id' => $zone->getId(), 
            'block_id' => $block->getId(),
            'kitpages_target' => $_SERVER["REQUEST_URI"]    
        );
        $dataRenderer['listAction']['moveUp'] = $this->get('router')->generate(
            'kitpages_cms_zoneblock_moveup', 
            $dataUrl
        );

        $dataRenderer['listAction']['moveDown'] = $this->get('router')->generate(
            'kitpages_cms_zoneblock_movedown', 
            $dataUrl
        );
        
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbarBlock.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 
    
    
    public function widgetAction($label, $renderer = 'default', $displayToolbar = true) {
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $label));
        
        if ($zone == null) {
            return new Response('Please create a zone with the label "'.htmlspecialchars($label).'"');
        }
        
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->toolbarBlock($zone, $block);
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array(
                        "label" => $block->getSlug(),
                        "renderer" =>$renderer,
                        'displayToolbar' => false
                    ),
                    array()
                );
            }
            if ($displayToolbar) {
                $resultingHtml = $this->toolbar($zone, $resultingHtml);
            }
        }
        
        elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array(
                        "label" => $block->getSlug(),
                        "renderer" =>$renderer
                    ),
                    array()
                );
            }
        }
        
        elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $zonePublish = $zone->getZonePublish();
            if ($zonePublish instanceof ZonePublish) {
                $zonePublishData = $zonePublish->getData();
                foreach($zonePublishData['blockPublishList'] as $blockPublishId){
                    $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->find($blockPublishId);
                    $blockPublishData = $blockPublish->getData();
                    if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                        $resultingHtml .= $blockPublishData['html'];
                    }
                }   
            }
            else {
                return new Response('This zone is not published');
            }
        } 
        return new Response($resultingHtml);
    }

    
    public function publishAction(Zone $zone)
    {
        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer');        
        $zoneManager->publish($zone, $dataRenderer);
        $this->getRequest()->getSession()->setFlash('notice', 'Zone published');
        $target = $this->getRequest()->query->get('kitpages_target', null);
        if ($target) {
            return $this->redirect($target);
        }
        return $this->redirect($this->generateUrl('kitpages_cms_block_edit_success'));
    }

    public function moveUpBlockAction(Zone $zone, $block_id)
    {
        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $zoneManager->moveUpBlock($zone, $block_id);
        $this->getRequest()->getSession()->setFlash('notice', 'Block moved up');
        return new RedirectResponse($this->getRequest()->query->get('kitpages_target'));
    }
    public function moveDownBlockAction(Zone $zone, $block_id)
    {
        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $zoneManager->moveDownBlock($zone, $block_id);
        $this->getRequest()->getSession()->setFlash('notice', 'Block moved down');
        return new RedirectResponse($this->getRequest()->query->get('kitpages_target'));
    }    
}
