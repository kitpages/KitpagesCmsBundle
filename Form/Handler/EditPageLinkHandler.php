<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class EditPageLinkHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
    }

    public function process($form, Page $page)
    {
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $parent_id = $form->get('parent_id')->getData();
            if (!empty($parent_id)) {
                $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                $page->setParent($pageParent);
            }
            $em->flush();
            return array('result' => true, 'msg' => 'Page Link modified');
        }

        return array('result' => false);
    }
}

