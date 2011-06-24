<?php
namespace Kitpages\CmsBundle;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;
class BlockListener
{

    public function __construct(Registry $doctrine) {
        $this->doctrine = $doctrine;
    }
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    }    
    
    public function onPublish(Event $event)
    {
        echo "in listener";
        $block = $event->getBlock();
        $block->setIsPublished(true);
        $block->setRealModificationDate(new \DateTime());
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($block);
        $em->flush();
    }
}