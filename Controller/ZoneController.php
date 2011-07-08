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

use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\ZoneBlock;
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
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($zone);
                $em->flush();

                //return $this->redirect($this->generateUrl('kitpages_cms_block_edit', array('id' => $block->getId() )));
            }
        }
        return $this->render('KitpagesCmsBundle:Zone:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function toolbar(Zone $zone, $htmlBlock) {  
        $listAction['addBlock'] = $this->get('router')->generate(
            'kitpages_cms_block_create', 
            array('zone_id' => $zone->getId())
        );
        $listAction['publish'] = $this->get('router')->generate(
            'kitpages_cms_zone_publish', 
            array('id' => $zone->getId())
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
    
    public function toolbarBlock(Zone $zone, Block $block) {  

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
    
    public function widgetAction($label, $renderer = 'default') {
        $context = $this->get('kitpages.cms.controller.context');
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $label));
        $resultingHtml = '';

        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->toolbarBlock($zone, $block).$this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array("label" => $block->getSlug(), "renderer" =>$renderer), array()
                );
            }
            $resultingHtml = $this->toolbar($zone, $resultingHtml);
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array("label" => $block->getSlug(), "renderer" =>$renderer), array()
                );
            }        
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $zonePublish = $em->getRepository('KitpagesCmsBundle:ZonePublish')->findByZone($zone);
//            foreach( as $blockPublish){
//                $data = $blockPublish->getData();
//
//                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
//                    $resultingHtml .= $data['html'];
//                }
//            }            

        }
        
        return new Response($resultingHtml);
    }
    public function publishAction(Zone $zone)
    {
        $zoneManager = $this->get('kitpages.cms.manager.zone');
        $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer');        
        $zoneManager->firePublish($zone, $dataRenderer);
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }

//    public function unpublishAction(Zone $zone)
//    {
//        $blockManager = $this->get('kitpages.cms.manager.zone');
//        $blockManager->fireUnpublish($zone);
//
//        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
//    }    


    public function moveUpBlockAction($id, $block_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($id, $block_id);
        $position = $zoneBlock->getPosition()-1;
        $zoneBlock->setPosition($position);
        $em->persist($zoneBlock);
        $em->flush();
        $request = Request::createFromGlobals();
        return new RedirectResponse($request->query->get('kitpages_target'));
    }
    public function moveDownBlockAction($id, $block_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($id, $block_id);
        $position = $zoneBlock->getPosition()+1;
        $zoneBlock->setPosition($position);
        $em->persist($zoneBlock);
        $em->flush();
        $request = Request::createFromGlobals();
        return new RedirectResponse($request->query->get('kitpages_target'));
    }    
}
