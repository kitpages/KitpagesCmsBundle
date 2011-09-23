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
    protected $blockManager = null;
    protected $logger = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        $blockManager,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
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
                $this->deletePublished($zonePublish);
            }

            // create zone publish
            $blockPublishList = array();
            foreach($em->getRepository('KitpagesCmsBundle:BlockPublish')->findByZone($zone) as $blockPublish){
                $blockPublishList[$blockPublish->getRenderer()][] = $blockPublish->getId();
            }
            $zonePublishNew = new ZonePublish();
            $zonePublishNew->initByZone($zone);
            $zonePublishNew->setData(array("blockPublishList"=>$blockPublishList));
            $zone->setIsPublished(true);
            $zone->setZonePublish($zonePublishNew);
            $em->persist($zonePublishNew);
            $em->persist($zone);
            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterZonePublish, $event);
    }

    public function unpublish($zone){
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onZoneUnpublish, $event);
        $em = $this->getDoctrine()->getEntityManager();
        $zone->setIsPublished(false);
        $em->flush();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterZoneUnpublish, $event);
    }
    public function deletePublished($zonePublish){
        $data = $zonePublish->getData();
        $em = $this->getDoctrine()->getEntityManager();
        foreach ($data['blockPublishList'] as $renderer => $blockPublishList){
            foreach($blockPublishList as $blockPublishId) {
                $blockPublish = $em->getRepository('KitpagesCmsBundle:BlockPublish')->find($blockPublishId);
                if ($blockPublish != null) {
                    $this->getBlockManager()->deletePublished($blockPublish);
                }
            }
        }
        $em->remove($zonePublish);
        $em->flush();
    }
    public function delete(Zone $zone)
    {
        // throw on event
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onZoneDelete, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $blockManager = $this->getBlockManager();
            foreach($em->getRepository('KitpagesCmsBundle:Block')->findByZone($zone) as $block){
                $nbr = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->nbrZoneBlockByBlockWithZoneDiff($block, $zone);
                if ($nbr == 0) {
                    $blockManager->delete($block);
                }
            }
            $em->remove($zone);
            $em->flush();
        }
        // throw after event
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterZoneDelete, $event);
    }
    public function moveUpBlock(Zone $zone, $block_id)
    {
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockMove, $event);
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($zone, $block_id);
            $position = $zoneBlock->getPosition()-1;
            if ($position >= 0) {
                $zoneBlock->setPosition($position);
                $em->flush();
            }
            $this->reorderBlockList($zone);
            $this->unpublish($zone);
            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockMove, $event);
    }
    public function moveDownBlock(Zone $zone, $block_id)
    {
        $event = new ZoneEvent($zone);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockMove, $event);
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $zoneBlock = $em->getRepository('KitpagesCmsBundle:ZoneBlock')->findByZoneAndBlock($zone, $block_id);
            $position = $zoneBlock->getPosition()+1;
            if ($position >= 0) {
                $zoneBlock->setPosition($position);
                $em->flush();
            }
            $this->reorderBlockList($zone);
            $this->unpublish($zone);
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockMove, $event);
    }

    public function reorderBlockList(Zone $zone)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery("
            SELECT zb FROM KitpagesCmsBundle:ZoneBlock zb
            WHERE zb.zone = :zone
            ORDER BY zb.position ASC
        ")->setParameter('zone', $zone);
        $zoneBlockList = $query->getResult();
        $cnt = 0;
        foreach ($zoneBlockList as $zoneBlock) {
            $zoneBlock->setPosition($cnt);
            $cnt ++;
        }
        $em->flush();
    }

    ////
    // event listener
    ////
    public function afterBlockModify(Event $event)
    {
        $block = $event->getBlock();
        $em = $this->getDoctrine()->getEntityManager();
        foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByBlock($block) as $zone) {
            $this->unpublish($zone);
        }
    }

    public function onBlockDelete(Event $event)
    {
        $block = $event->getBlock();
        $em = $this->getDoctrine()->getEntityManager();
        $zoneList = $em->getRepository('KitpagesCmsBundle:Zone')->findByBlock($block);
        $event->set("zoneList", $zoneList);
        foreach($zoneList as $zone) {
            $zone->setIsPublished(false);
        }
        $em->flush();
    }
    public function afterBlockDelete(Event $event)
    {
        $zoneList = $event->get('zoneList');
        $em = $this->getDoctrine()->getEntityManager();
        foreach($zoneList as $zone) {
            $this->reorderBlockList($zone);
        }
        $em->flush();
    }



}
