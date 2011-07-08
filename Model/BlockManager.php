<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Event\BlockEvent;
use Kitpages\CmsBundle\KitpagesCmsStoreEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

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

    public function fireModify(Block $block)
    {
        $event = new BlockEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsStoreEvents::onBlockModify, $event);
    }
    
    public function firePublish(Block $block, $listRenderer)
    {
        $event = new BlockEvent($block, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsStoreEvents::onBlockPublish, $event);
    }
    public function fireUnpublish(Block $block)
    {
        $event = new BlockEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsStoreEvents::onBlockUnpublish, $event);
    }    
    public function onPublish(Event $event)
    {
        $em = $this->getDoctrine()->getEntityManager();        
        $block = $event->getBlock();
        foreach($block->getBlockPublishList() as $blockPublish){
            $em->remove($blockPublish);
        }
        $em->flush();
        $em->refresh($block);
        $listRenderer = $event->getListRenderer();        
        if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
            if (!is_null($block->getData())) {             
                foreach($listRenderer as $nameRenderer => $renderer) {
                    $resultingHtml = $this->getTemplating()->render($renderer['twig'], array('data' => $block->getData())); 
                    $blockPublish = new BlockPublish();
                    $blockPublish->initByBlock($block);
                    $blockPublish->setData(array("html"=>$resultingHtml));
                    $blockPublish->setRenderer($nameRenderer);
                    $em->persist($blockPublish);
                }                    
            }
        }
        $block->setIsPublished(true);
        $em->persist($block);
        $em->flush();
    }  
    
//    public function onUnpublish(Event $event)
//    {
//   
//        $em = $this->getDoctrine()->getEntityManager();        
//        $block = $event->getBlock();
//        
//        $block->setIsPublished(false);
//        $em->persist($block);
//        
//        foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByBlockId($block->getId()) as $blockPublish){
//            $em->remove($blockPublish);
//        }
//        $em->flush();
//    }

    public function onModify(Event $event)
    {
        
    }
    
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Block) {
            $blockSlug = $entity->getSlug();
            if(empty($blockSlug)) {
                $entity->setSlug('block_ID');
            }
        }
    }
    public function postPersist(LifecycleEventArgs $event)
    {    
        /* Event BLOCK */
        $entity = $event->getEntity();
        if ($event->getEntity() instanceof Block) {
            if($entity->getSlug() == 'block_ID') {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
        }    
    }
    
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $uom = $em->getUnitOfWork();
        
        /* Event BLOCK */
        if ($entity instanceof Block) {
            $blockSlug = $entity->getSlug();
            if(empty($blockSlug)) {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
            
            if ($eventArgs->hasChangedField('data')) {
                $entity->setRealUpdatedAt(new \DateTime());
                $this->fireModify($entity);
                $entity->setIsPublished(false);
                if ($entity->getIsPublished() == 1) {
                    $entity->setUnpublishedAt(new \DateTime());
                }
                $uom->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }
           
        }
    }         
    
}
