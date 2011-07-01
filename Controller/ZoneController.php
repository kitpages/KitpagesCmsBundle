<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Model\CmsManager;

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
            array('id' => $zone->getId(), 'zone_id' => $zone->getId())
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

        $dataRenderer['listAction']['moveUp'] = $this->get('router')->generate(
            'kitpages_cms_zoneblock_moveup', 
            array('id' => $block->getId(), 'zone' => $zone->getId())
        );

        $dataRenderer['listAction']['moveDown'] = $this->get('router')->generate(
            'kitpages_cms_zoneblock_movedown', 
            array('id' => $block->getId(), 'zone' => $zone->getId())
        );
        
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbarBlock.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 
    
    public function widgetAction($label) {
        // récupérer le label ou l'id du block
        
        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $label));
        $resultingHtml = '';

        if ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_EDIT) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZoneId($zone->getId()) as $block){
                $resultingHtml .= $this->toolbarBlock($zone, $block).$this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array("label" => $block->getSlug()), array()
                );
            }
            $resultingHtml = $this->toolbar($zone, $resultingHtml);
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PREVIEW) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZoneId($zone->getId()) as $block){
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array("label" => $block->getSlug()), array()
                );
            }        
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PROD) {
            foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByZoneId($zone->getId()) as $blockPublish){
                $data = $blockPublish->getData();

                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                    $resultingHtml .= $data['html'];
                }
            }            

        }
        
        return new Response($resultingHtml);
    }
    
    public function publishAction(Zone $zone)
    {
        $em = $this->getDoctrine()->getEntityManager();
        foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZoneId($zone->getId()) as $block){
            $blockManager = $this->get('kitpages.cms.manager.block');
            $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
            $blockManager->publish($block, $dataRenderer);
        }

        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }
    
}
