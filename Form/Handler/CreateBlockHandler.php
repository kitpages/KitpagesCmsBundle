<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class CreateBlockHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
    }

    public function process($form, Block $block)
    {
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $block->setBlockType('edito');
            $block->setIsPublished(false);

            $em = $this->doctrine->getManager();
            $em->persist($block);

            $zone_id = $form->get('zone_id')->getData();
            $position = $form->get('position')->getData();
            if ($position == null) {
                $position = 0;
            }
            if (!empty($zone_id)) {
                $zoneBlock = new ZoneBlock();
                $zone = $em->getRepository('KitpagesCmsBundle:Zone')->find($zone_id);
                $zoneBlock->setZone($zone);
                $zoneBlock->setBlock($block);
                $em->persist($zoneBlock);
                $em->flush();
                $zoneBlock->setPosition($position);
            }
            $em->flush();

            return array('result' => true, 'msg' => 'Block created');
        }

        return array('result' => false);
    }
}

