<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Event\BlockEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class BlockManager
{
 
    ////
    // dependency injection
    ////
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
    
    
    ////
    // action function
    ////
    /**
     *
     * @param Block $block 
     */
    public function delete(Block $block)
    {
        // throw on event
        $event = new BlockEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockDelete, $event);
        
        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($block);
            $em->flush();
        }
        // throw after event
        $event = new BlockEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockDelete, $event);
    }
    
    public function publish(Block $block, array $listRenderer)
    {
        $event = new BlockEvent($block, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockPublish, $event);
        
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery("
                SELECT bp FROM KitpagesCmsBundle:BlockPublish bp
                WHERE bp.block = :block
            ")->setParameter('block', $block);
            $blockPublishList = $query->getResult();

            foreach($blockPublishList as $blockPublish){
                $em->remove($blockPublish);
            }
            $em->persist($block);
            $em->flush();
            $em->refresh($block);
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
        $event = new BlockEvent($block, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockPublish, $event);
    }
    
    public function afterModify($block, $oldBlockData)
    {
        if ($oldBlockData != $block->getData()) {
            $block->setRealUpdatedAt(new \DateTime());
            $block->setIsPublished(false);
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();
            $event = new BlockEvent($block);
            $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockModify, $event);
        }
    }

    ////
    // doctrine events
    ////
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
//        $uom = $em->getUnitOfWork();
        
        /* Event BLOCK */
        if ($entity instanceof Block) {
            $blockSlug = $entity->getSlug();
            if(empty($blockSlug)) {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
            
//            if ($eventArgs->hasChangedField('data')) {
//                $entity->setRealUpdatedAt(new \DateTime());
//                $entity->setIsPublished(false);
//                if ($entity->getIsPublished() == 1) {
//                    $entity->setUnpublishedAt(new \DateTime());
//                }
//                $uom->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
//            }
           
        }
    }         
    
}
