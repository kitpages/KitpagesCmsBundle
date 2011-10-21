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
use Kitpages\CmsBundle\Model\Paginator;
use Kitpages\CmsBundle\Controller\Context;

class ZoneController extends Controller
{


    public function createAction()
    {
        $request = $this->get('request');
        $zone = new Zone();
        $zone->setSlug($request->query->get('kitpagesZoneSlugDefault', null));
        // build basic form
        $builder = $this->createFormBuilder($zone);
        $builder->add('slug', 'text');
        $builder->add('canonicalUrl', 'text');
        $builder->add('title', 'text');
        // get form
        $form = $builder->getForm();


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

    public function toolbar(Zone $zone, $htmlBlock, $authorizedBlockTemplateList = null) {
        $actionList = array();
        $actionList[] = array(
            'id' => '',
            'label' => 'addBlock',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_create',
                array(
                    'zone_id' => $zone->getId(),
                    'kitpages_target' => $_SERVER['REQUEST_URI'],
                    'authorized_block_template_list' => $authorizedBlockTemplateList
                )
            ),
            'icon' => 'icon/add.png'
        );
        $actionList[] = array(
            'id' => 'publish',
            'label' => 'publish',
            'url' => $this->get('router')->generate(
                'kitpages_cms_zone_publish',
                array(
                    'id' => $zone->getId(),
                    'position' => 0,
                    'kitpages_target' => $_SERVER['REQUEST_URI']
                )
            ),
            'class' => ($zone->getIsPublished() == '1')?'kit-cms-advanced':'',
            'icon' => 'icon/publish.png'
        );

        $dataRenderer = array(
            'kitCmsZoneSlug' => $zone->getSlug(),
            'isPublished' => $zone->getIsPublished(),
            'actionList' => $actionList,
            'htmlBlock' => $htmlBlock
        );
        $resultingHtml = $this->renderView(
            'KitpagesCmsBundle:Zone:toolbar.html.twig', $dataRenderer
        );
        return $resultingHtml;
    }

    public function toolbarBlock(Zone $zone, Block $block, $authorizedBlockTemplateList = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($zone, $block);
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
            'id' => '',
            'label' => 'addBlock',
            'url' => $this->get('router')->generate(
                'kitpages_cms_block_create',
                array(
                    'zone_id' => $zone->getId(),
                    'position' => $zoneBlock->getPosition()+1,
                    'kitpages_target' => $_SERVER['REQUEST_URI'],
                    'authorized_block_template_list' => $authorizedBlockTemplateList
                )
            ),
            'icon' => 'icon/add.png'
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

        $dataUrl = array(
            'id' => $zone->getId(),
            'block_id' => $block->getId(),
            'kitpages_target' => $_SERVER["REQUEST_URI"]
        );
        $dataRenderer['actionList'][] = array(
            'id' => '',
            'label' => 'moveUp',
            'url' => $this->get('router')->generate(
                'kitpages_cms_zoneblock_moveup',
                $dataUrl
            ),
            'icon' => 'icon/arrow-up.png'
        );

        $dataRenderer['actionList'][] = array(
            'id' => '',
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


    public function widgetAction(
        $slug,
        $renderer = 'default',
        $displayToolbar = true,
        $blockDisplayCount = null,
        $paginator = null,
        $reverseOrder = false,
        $authorizedBlockTemplateList = null
    )
    {
        $em = $this->getDoctrine()->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $slug));

        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        $paginatorHtml = '';

        // exit when no zone
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            if ($zone == null) {
                return new Response(
                    'Please '.
                    '<a href="'.
                    $this->generateUrl(
                        "kitpages_cms_zone_create",
                        array(
                            "kitpages_target"=>$_SERVER["REQUEST_URI"],
                            "kitpagesZoneSlugDefault"=>$slug
                        )
                    ).
                    '">create a zone</a> with the slug "'. $slug.'"'
                );
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            if ($zone == null) {
                return new Response('Please create a zone with the slug "'.htmlspecialchars($slug).'"');
            }
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            if ($zone == null) {
                //return new Response('The zone with the slug "'.htmlspecialchars($slug).'" is missing');
                return new Response();
            }
        }
        // display block order
        $blockOrder = 'asc';
        if ($reverseOrder) {
            $blockOrder = 'desc';
        }
        // get blockList
        if (
            ($context->getViewMode() == Context::VIEW_MODE_EDIT) ||
            ($context->getViewMode() == Context::VIEW_MODE_PREVIEW)
        ) {
            // PAGER
            if (!is_null($paginator)) {
                $blockRepository = $em->getRepository('KitpagesCmsBundle:Block');
                $totalBlockCount = $blockRepository->getBlockCountByZone($zone);
                $paginator->setTotalItemCount($totalBlockCount);
                $blockList = $blockRepository->findByZone(
                    $zone,
                    $blockOrder,
                    $paginator->getItemCountPerPage(),
                    $paginator->getSqlLimitOffset()
                );

                $paginatorHtml .= $this->renderView(
                    "KitpagesCmsBundle:Zone:pager.html.twig",
                    array(
                        'paginator' =>$paginator
                    )
                );
            } else {
                $blockList = $em
                    ->getRepository('KitpagesCmsBundle:Block')
                    ->findByZone($zone, $blockOrder, $blockDisplayCount);
            }

            $tmpDisplayToobar = false;
            $blockCount = count($blockList);
            $cnt = 1;
            foreach($blockList as $block){
                $tmpDisplayToobar = true;
                if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
                    $resultingHtml .= $this->toolbarBlock($zone, $block, $authorizedBlockTemplateList);
                    $tmpDisplayToobar = false;
                }
                // add class firstLastClass if needed (kit-cms-first or kit-cms-last)
                $firstLastClass = '';
                if ($cnt == 1) {
                    $firstLastClass .= ' kit-cms-first ';
                }
                if ($cnt == $blockCount) {
                    $firstLastClass .= ' kit-cms-last ';
                }
                $cnt++;

                $resultingHtml .= $this->get('templating.helper.actions')->render(
                    "KitpagesCmsBundle:Block:widget",
                    array(
                        "slug" => $block->getSlug(),
                        "renderer" =>$renderer,
                        "displayToolbar" => $tmpDisplayToobar,
                        "authorizedBlockTemplateList" => $authorizedBlockTemplateList,
                        "filterParameterList" => array('firstLastClass' => $firstLastClass)
                    ),
                    array()
                );
            }
            if ($displayToolbar && ($tmpDisplayToobar === false ) && ($context->getViewMode() == Context::VIEW_MODE_EDIT)) {
                $resultingHtml = $this->toolbar($zone, $resultingHtml, $authorizedBlockTemplateList);
            }

        }
        elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $zonePublish = $zone->getZonePublish();
            if ($zonePublish instanceof ZonePublish) {
                $zonePublishData = $zonePublish->getData();
                if (!isset($zonePublishData['blockPublishList'][$renderer])) {
                    return new Response('');
                }
                $blockList = $zonePublishData['blockPublishList'][$renderer];
                if ($reverseOrder) {
                    $blockList = array_reverse($blockList);
                }
                if (!is_null($blockDisplayCount)) {
                    $blockList = array_slice($blockList, 0, $blockDisplayCount);
                }

                // PAGER
                if (!is_null($paginator)) {
                    $paginator->setTotalItemCount(count($blockList));
                    $paginatorHtml .= $this->renderView(
                        "KitpagesCmsBundle:Zone:pager.html.twig",
                        array(
                            'paginator' =>$paginator
                        )
                    );
                }

                $blockCount = count($blockList);
                $cnt = 1;
                foreach($blockList as $blockPublishId){
                    $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->find($blockPublishId);
                    $blockPublishData = $blockPublish->getData();
                    // add class firstLastClass if needed (kit-cms-first or kit-cms-last)
                    $firstLastClass = '';
                    if ($cnt == 1) {
                        $firstLastClass .= ' kit-cms-first ';
                    }
                    if ($cnt == $blockCount) {
                        $firstLastClass .= ' kit-cms-last ';
                    }
                    $cnt++;
                    // render blocks
                    if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                        $resultingHtml .= $this->widgetPostFilter(
                            $blockPublishData['html'],
                            array('firstLastClass' => $firstLastClass)
                        );
                    }
                }
            }
            else {
                //return new Response('This zone is not published');
                return new Response();
            }
        }

        if (!is_null($paginator)) {
            $resultingHtml .= $paginatorHtml;
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
