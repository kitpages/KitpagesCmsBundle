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
                $target = $request->query->get('kitpages_target', null);
                if ($target) {
                    return $this->redirect($target);
                }
                return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');;
            }
        }
        return $this->render('KitpagesCmsBundle:Zone:create.html.twig', array(
            'form' => $form->createView(),
            'kitpages_target' => $this->getRequest()->query->get('kitpages_target', null)
            
        ));
    }

    public function toolbar(Zone $zone, $htmlBlock) {
        $actionList = array();
        $actionList[] = array(
            'label' => 'addBlock',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_create', 
                array(
                    'zone_id' => $zone->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'icon' => 'icon/add.png'
        );
        $actionList[] = array(
            'label' => 'publish',
            'url' => $this->get('router')->generate(
                'kitpages_cms_zone_publish',
                array(
                    'id' => $zone->getId(),
                    'position' => 0,
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'icon' => 'icon/publish.png'
        );
        
        $dataRenderer = array(
            'title' => $zone->getSlug(),
            'actionList' => $actionList,
            'htmlBlock' => $htmlBlock
        );
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbar.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 
    
    public function toolbarBlock(Zone $zone, Block $block)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($zone, $block);
        $dataRenderer['actionList'][] = array(
            'label' => 'edit',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_edit', 
                array(
                    'id' => $block->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'icon' => 'icon/edit.png'
        );
        $dataRenderer['actionList'][] = array(
            'label' => 'addBlock',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_create', 
                array(
                    'zone_id' => $zone->getId(),
                    'position' => $zoneBlock->getPosition()+1,
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'icon' => 'icon/add.png'
        );        
        $dataRenderer['actionList'][] = array(
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

        $dataUrl = array(
            'id' => $zone->getId(), 
            'block_id' => $block->getId(),
            'kitpages_target' => $_SERVER["REQUEST_URI"]    
        );
        $dataRenderer['actionList'][] = array(
            'label' => 'moveUp',
            'url' => $this->get('router')->generate(
                'kitpages_cms_zoneblock_moveup', 
                $dataUrl
            ),
            'icon' => 'icon/arrow-up.png'
        );

        $dataRenderer['actionList'][] = array(
            'label' => 'moveDown',
            'url' => $this->get('router')->generate(
                'kitpages_cms_zoneblock_movedown', 
                $dataUrl
            ),
            'icon' => 'icon/arrow-down.png'
        );
        
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Block:toolbar.html.twig', $dataRenderer
        );  
        return $resultingHtml;
    } 
    
    
    public function widgetAction($slug, $renderer = 'default', $displayToolbar = true) {
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $slug));
        
        
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            if ($zone == null) {
                return new Response(
                    'Please '.
                    '<a href="'.
                    $this->generateUrl(
                        "kitpages_cms_zone_create",
                        array("kitpages_target"=>$_SERVER["REQUEST_URI"])
                    ).
                    '">create a zone</a> with the slug "'. $slug.'"'
                );
            }

            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->toolbarBlock($zone, $block);
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array(
                        "slug" => $block->getSlug(),
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
        
        if ($zone == null) {
            return new Response('Please create a zone with the slug "'.htmlspecialchars($slug).'"');
        }

        if ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget", 
                    array(
                        "slug" => $block->getSlug(),
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
