<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\ZonePublish;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Kitpages\CmsBundle\Event\ZoneEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ZoneManager
{
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $templating = null;
    protected $blockManager = null;
    protected $logger = null;
    
    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        $templating,
        $blockManager,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->templating = $templating;
        $this->blockManager = $blockManager;
        $this->logger = $logger;
    }      

    /**
     * @return EventDispatcher $dispatcher
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }  
    
    /**
     * @return $templating
     */
    public function getTemplating() {
        return $this->templating;
    }    
    
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    }    

    /**
     * @return $blockManager
     */
    public function getBlockManager() {
        return $this->blockManager;
    }
    
    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    ////
    // fire events
    ////
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
    public function fireBlockMove(Zone $zone)
    {
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockMove, $event);
    }
    
    ////
    // actions
    ////
    public function publish(Zone $zone, array $listRenderer)
    {
        $event = new ZoneEvent($zone, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onZonePublish, $event);
        if (! $event->isDefaultPrevented()) {
            // publish blocks
            $em = $this->getDoctrine()->getEntityManager();
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $this->getBlockManager()->publish($block, $listRenderer[$block->getTemplate()]);
            }
            $em->flush();
            // remove old zonePublish
            $zonePublish = null;
            $query = $em->createQuery("
                SELECT zp FROM KitpagesCmsBundle:ZonePublish zp
                WHERE zp.zone = :zone
            ")->setParameter('zone', $zone);
            $zonePublishList = $query->getResult();
            if (count($zonePublishList) == 1) {
                $zonePublish = $zonePublishList[0];
                $em->remove($zonePublish);
                $em->flush();
            }

            // create zone publish
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $listBlock[] = $block->getId();
            }
            $zonePublishNew = new ZonePublish();
            $zonePublishNew->initByZone($zone);
            $zonePublishNew->setData(array("blockList"=>$listBlock));
            $zone->setIsPublished(true);
            $zone->setZonePublish($zonePublishNew);
            $em->persist($zonePublishNew);
            $em->persist($zone);
            $em->flush();
        }
        $event = new ZoneEvent($zone, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterZonePublish, $event);
    }
    
    public function onBlockModify(Event $event)
    {
        $block = $event->getBlock();
        $em = $this->getDoctrine()->getEntityManager(); 
        foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByBlock($block) as $zone) {
            if ($zone instanceof Zone) {
                $zone->setIsPublished(false);
            }
        }
        $em->flush();
    }
    public function onBlockDelete(Event $event)
    {
        $block = $event->getBlock();
        $em = $this->getDoctrine()->getEntityManager(); 
        foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByBlock($block) as $zone) {
            if ($zone instanceof Zone) {
                $zone->setIsPublished(false);
            }
        }
//        foreach($em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByBlock($block) as $zoneBlock) {
//            if ($zoneBlock instanceof ZoneBlock) {
//                $em->remove($zoneBlock);
//            }
//        }
        $em->flush();
    }
    public function onBlockMove(Event $event)
    {
        $zone = $event->getZone();
        $em = $this->getDoctrine()->getEntityManager(); 
        $zone->setIsPublished(false);
        $em->flush();
    }
   
    
    ////
    // doctrine events
    ////
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
//        $uom = $em->getUnitOfWork();
        
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
