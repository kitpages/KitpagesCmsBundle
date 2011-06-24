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
use Symfony\Component\HttpFoundation\Response;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Form\BlockType;
use Kitpages\CmsBundle\Model\BlockManager;
class BlockController extends Controller
{
    
   
    public function viewAction()
    {
        return $this->render('KitpagesCmsBundle:Block:view.html.twig');
    }
    
    public function createAction()
    {
        $block = new Block();

        // build template list
        $templateList = $this->container->getParameter('kitpages_cms.block.template.template_list');
        $selectTemplateList = array();
        foreach ($templateList as $key => $template) {
            $selectTemplateList[$key] = $template['name'];
        }

        // build basic form
        $builder = $this->createFormBuilder($block);
        $builder->add('label', 'text');
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
                $block->setIsPublished(true);
                $block->setRealModificationDate(new \DateTime());
                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($block);
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
        $builder->add('label', 'text');
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
                $block->setRealModificationDate(new \DateTime());
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
    

    public function widgetAction($label) {
        // récupérer le label ou l'id du block
        
        // si context = prod, $html pris dans le la table de publication
        
        // si context = preview ou edit : $html généré par le renderer
        
        // si context = edit, ajouter le code html des menus d'édition autour du bloc
        
        $resultingHtml = "my block".$label;
        return new Response($resultingHtml);
    }
    
    public function editSuccessAction()
    {
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }
    
    public function publishAction(Block $block)
    {
        $blockManager = $this->get('kitpages.cms.manager.block');
        $blockManager->publish($block);
        return $this->render('KitpagesCmsBundle:Block:publish.html.twig');
    }
    

}
