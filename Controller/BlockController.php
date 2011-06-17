<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Form\BlockType;

class BlockController extends Controller
{
    public function viewAction()
    {
        return $this->render('KitpagesCmsBundle:Block:view.html.twig');
    }
    
    public function createAction()
    {
        $block = new Block();
        
        $form = $this->createForm(new BlockType(), $block);
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $block->setBlockType('edito');
                $block->setIsActive(true);
                $block->setIsPublished(true);
                $block->setRealModificationDate(new \DateTime());
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($block);
                $em->flush();

                return $this->redirect($this->generateUrl('kitpages_cms_block_create_success'));
            }
        }
        return $this->render('KitpagesCmsBundle:Block:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction()
    {
        $id = 1;
        $em = $this->getDoctrine()->getEntityManager();
        $block = $em->getRepository('KitpagesCmsBundle:Block')->find($id);
        $block->setData(array(
            "title" => '',
            'body' => ''
        ));
        
        $builder = $this->createFormBuilder($block);
        $builder->add('label', 'text');
        $builder->add('template', 'text');

        $builder->add('data', 'collection', array(
           'type' => new \Kitpages\CmsBundle\Form\BlockContentType(),
        ));
        $form = $builder->getForm();
        
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $block->setBlockType('edito');
                $block->setIsActive(true);
                $block->setIsPublished(true);
                $block->setRealModificationDate(new \DateTime());
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($block);
                $em->flush();

                return $this->redirect($this->generateUrl('kitpages_cms_block_create_success'));
            }
        }
        return $this->render('KitpagesCmsBundle:Block:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
    public function createSuccessAction()
    {
        return $this->render('KitpagesCmsBundle:Block:create-success.html.twig');
    }
}
