<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateZoneHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
    }

    public function process($form, Zone $zone)
    {
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $zone->setIsPublished(false);
            $em = $this->doctrine->getManager();
            $em->persist($zone);
            $em->flush();
            return array('result' => true);

        }

        return array('result' => false);
    }
}

