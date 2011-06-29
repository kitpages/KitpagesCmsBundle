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
    
    public function widgetAction($label) {
        // récupérer le label ou l'id du block
        
        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $label));
        $resultingHtml = '';

        if ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_EDIT) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZoneId($zone->getId()) as $block){

                //echo var_dump($test);
            }
//            $zone->
//            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
//                $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
//                $resultingHtml = $this->renderView($dataRenderer['default']['twig'], array('data' => $block->getData()));
//            }
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PREVIEW) {
//            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
//                $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
//                $resultingHtml = $this->renderView($dataRenderer['default']['twig'], array('data' => $block->getData()));
//            }          
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PROD) {
//            $test=$em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('id' => 1));
            //$test->getBlockId();
            foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByZoneId($zone->getId()) as $blockPublish){
                echo var_dump($blockPublish);
//                $data = $blockPublish->getData();
//
//                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
//                    $resultingHtml = $data['html'];
//                }
            }            

        }
       
        return new Response($resultingHtml);
    }
    
}
