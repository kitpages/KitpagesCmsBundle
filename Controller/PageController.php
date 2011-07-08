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

}
