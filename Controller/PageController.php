<?php

/*

 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PageZone;

class PageController extends Controller
{
 
    public function viewAction(Page $page)
    {
        return $this->render($page->getLayout());        
    }

    public function testTreeAction(Page $page)
    {
              $em = $this->get('doctrine')->getEntityManager();
        $zone = $em->getRepository('KitpagesCmsBundle:Zone')->find(1);
        $test = new PageZone();
        $test->setPage($page);
        $test->setZone($zone);  
        $test->setPosition(0);
        $em->persist($test);
        $em->flush();        
        $repo = $em->getRepository('KitpagesCmsBundle:PageZone');
        // move it up by one position
        $repo->moveUp($test, 1);
        return $this->render($page->getLayout());        
    }
    
    public function createAction()
    {
        $page = new Page();

        // build basic form
        $builder = $this->createFormBuilder($page);
        $builder->add('slug', 'text');
        $builder->add('layout', 'text');        
        // get form
        $form = $builder->getForm();
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $page->setPageType('edito');
                $page->setIsActive(true);
                $page->setIsPublished(false);
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($page);
                $em->flush();

                //return $this->redirect($this->generateUrl('kitpages_cms_block_edit', array('id' => $block->getId() )));
            }
        }
        return $this->render('KitpagesCmsBundle:Page:create.html.twig', array(
            'form' => $form->createView()
        ));
    }


    public function widgetAction($label) {
        // récupérer le label ou l'id du block
        
        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $em = $this->getDoctrine()->getEntityManager();
        
        $resultingHtml = '';
        if ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_EDIT) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $label));
            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                $dataRenderer = $this->container->getParameter('kitpages_cms.block.renderer.'.$block->getTemplate());
                $resultingHtml = $this->renderView($dataRenderer['default']['twig'], array('data' => $block->getData()));
            }
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PREVIEW) {
            $block = $em->getRepository('KitpagesCmsBundle:Block')->findOneBy(array('slug' => $label));
            echo var_dump($block);            
        } elseif ($cmsManager->getViewMode() == CmsManager::VIEW_MODE_PROD) {
            $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->findOneBy(array('slug' => $label));
            if (!is_null($blockPublish)) {
                $data = $blockPublish->getData();
                if ($blockPublish->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                    $resultingHtml = $data['html'];
                }
            }
        }
        // si context = prod, $html pris dans le la table de publication
        
        // si context = preview ou edit : $html généré par le renderer
        
        // si context = edit, ajouter le code html des menus d'édition autour du bloc
        
        return new Response($resultingHtml);
    }

}
