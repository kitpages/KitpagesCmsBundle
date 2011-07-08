<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\ZonePublish;
use Kitpages\CmsBundle\Event\ZoneEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;

class ZoneManager
{
 
    public function __construct(Registry $doctrine, EventDispatcher $dispatcher, $templating, $blockManager){
        $this->_dispatcher = $dispatcher;
        $this->_doctrine = $doctrine;
        $this->_templating = $templating;
        $this->_blockManager = $blockManager;
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

    /**
     * @return $blockManager
     */
    public function getBlockManager() {
        return $this->_blockManager;
    }  
    
    public function firePublish(Zone $zone, $listRenderer)
    {
        $event = new ZoneEvent($zone, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onZonePublish, $event);
    }
    public function fireUnpublish(Zone $zone)
    {
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onZoneUnpublish, $event);
    }    
    public function onPublish(Event $event)
    {
        
        $em = $this->getDoctrine()->getEntityManager();        
        $zone = $event->getZone();
        $listRenderer = $event->getListRenderer();
        foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
            $this->getBlockManager()->publish($block, $listRenderer[$block->getTemplate()]);
        }

        foreach($em->getRepository('KitpagesCmsBundle:ZonePublish')->findByZone($zone) as $zonePublish){
            $em->remove($zonePublish);
        }
        
        $zonePublish = new ZonePublish();
        $zonePublish->initByZone($zone);
        foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
            $listBlock[] = $block->getId();
        }
        $zonePublish->setData(array("blockList"=>$listBlock));
        $em->persist($zonePublish);
                
        $zone->setIsPublished(true);
        $em->persist($zone);
        $em->flush();
    }  
    
    public function onUnpublish(Event $event)
    {
   
        $em = $this->getDoctrine()->getEntityManager();        
        $zone = $event->getZone();
        // suppression de la zone et si pas de zone pas d'affichage en production
//        $block->setIsPublished(false);
//        $em->persist($block);
        
//        foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByBlockId($block->getId()) as $blockPublish){
//            $em->remove($blockPublish);
//        }
//        $em->flush();
    }

    public function onBlockModify(Event $event)
    {
        $block = $event->getBlock();
        $em = $this->getDoctrine()->getEntityManager(); 
        foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByBlock($block) as $zone) {
            $zone->setIsPublished(false); 
            $em->persist($zone);
        }
        $em->flush();
    }
    
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Zone) {
            $zoneSlug = $entity->getSlug();
            if(empty($zoneSlug)) {
                $entity->setSlug('zone_ID');
            }
        }
    }
    public function postPersist(LifecycleEventArgs $event)
    {    
        /* Event Zone */
        $entity = $event->getEntity();
        if ($event->getEntity() instanceof Zone) {
            if($entity->getSlug() == 'zone_ID') {
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
        
        /* Event Zone */
        if ($entity instanceof Zone) {
            $zoneSlug = $entity->getSlug();
            if(empty($zoneSlug)) {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
            
//            if (($eventArgs->hasChangedField('data')
//                || $eventArgs->hasChangedField('template'))
//                && $entity->getIsPublished() == 1
//            ) {
//                $entity->setIsPublished(false);
//                $entity->setUnpublishedAt(new \DateTime());
//                $uom->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
//            }
            
        }
    }   
    
}
