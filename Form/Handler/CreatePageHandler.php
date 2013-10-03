<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class CreatePageHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine, $pageManager, $layoutList)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
        $this->pageManager = $pageManager;
        $this->layoutList = $layoutList;
    }

    public function process($form, Page $page)
    {
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $page->setPageType('edito');
            $dataForm = array(
                'parent_id' => $form->get('parent_id')->getData(),
                'next_sibling_slug' => $form->get('next_sibling_slug')->getData(),
                'prev_sibling_slug' => $form->get('prev_sibling_slug')->getData()
            );
            $this->pageManager->createNewPage($page, $dataForm);

            $layoutKey = $form->get('layout')->getData();
            $zoneList = $this->layoutList[$layoutKey]['zone_list'];
            foreach($zoneList as $locationInPage => $render) {
                $this->pageManager->createZoneInPage($page, $locationInPage);
            }
            $em = $this->doctrine->getManager();
            $em->persist($page);
            $em->flush();
            return array('result' => true, 'msg' => 'Page created');

        }

        return array('result' => false);
    }
}

