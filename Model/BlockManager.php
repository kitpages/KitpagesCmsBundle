<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Event\FilterPublishEvent;
use Kitpages\CmsBundle\Event\FilterUnpublishEvent;
use Kitpages\CmsBundle\KitpagesCmsStoreEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;

class BlockManager
{
 
    public function __construct(Registry $doctrine, EventDispatcher $dispatcher, $templating){
        $this->_dispatcher = $dispatcher;
        $this->_doctrine = $doctrine;
        $this->_templating = $templating;
    }      

    /**
     * @return EventDispatcher $dispatcher
     */
    public function getDispatcher() {
        return $this->_dispatcher;
    }  
    
    /**
     * @return $templating
     */
    public function getTemplating() {
        return $this->_templating;
    }    
    
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->_doctrine;
    }    
    
    public function publish(Block $block, $listRenderer)
    {
        $event = new FilterPublishEvent($block, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsStoreEvents::onBlockPublish, $event);
    }
    public function unpublish(Block $block)
    {
        $event = new FilterUnpublishEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsStoreEvents::onBlockUnpublish, $event);
    }    
    public function onPublish(Event $event)
    {
        $em = $this->getDoctrine()->getEntityManager();        
        $block = $event->getBlock();
        foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByBlockId($block->getId()) as $blockPublish){
            $em->remove($blockPublish);
        }
        $listRenderer = $event->getListRenderer();        
        if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
            foreach($listRenderer as $renderer) {
                $resultingHtml = $this->getTemplating()->render($renderer['twig'], array('data' => $block->getData())); 
                $blockPublish = new BlockPublish();
                $blockPublish->initByBlock($block);
                $blockPublish->setData(array("html"=>$resultingHtml));
                $em->persist($blockPublish);
            }
        }
        
        $block->setIsPublished(true);
        $em->persist($block);
        $em->flush();
    }  
    
    public function onUnpublish(Event $event)
    {
   
        $em = $this->getDoctrine()->getEntityManager();        
        $block = $event->getBlock();
        
        $block->setIsPublished(false);
        $em->persist($block);
        
        foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByBlockId($block->getId()) as $blockPublish){
            $em->remove($blockPublish);
        }
        $em->flush();
    }
    
}
