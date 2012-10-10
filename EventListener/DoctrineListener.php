<?php
namespace Kitpages\CmsBundle\EventListener;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\Site;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\Block;

use Kitpages\CmsBundle\Event\NavEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Kitpages\UtilBundle\Service\Util;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class DoctrineListener {
    
    ////
    // dependency injection
    ////
    protected $doctrine = null;
    protected $dispatcher = null;
    protected $util = null;
    
    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        Util $util
    )
    {
        $this->doctrine = $doctrine;
        $this->dispatcher = $dispatcher;
        $this->util = $util;
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


    ////
    // doctrine events
    ////
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Block) {
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
                $entity->setSlug('block_ID');
            }
        }
        if ($entity instanceof Zone) {
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
                $entity->setSlug('zone_ID');
            }
        } 
        if ($entity instanceof Page) {
            $entity->setUrlTitle($this->util->urlPathEncode($entity->getTitle()));
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
                $entity->setSlug('page_ID');
            }
            if($entity->getIsInNavigation() == 1) {
                $this->unpublishNav();
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
        if ($event->getEntity() instanceof Zone) {
            if($entity->getSlug() == 'zone_ID') {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
        }
        if ($event->getEntity() instanceof Page) {
            if($entity->getSlug() == 'page_ID') {
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
        
        /* Event BLOCK */
        if ($entity instanceof Block) {
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
           
        }
        
        /* Event Zone */
        if ($entity instanceof Zone) {
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
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
        /* Event Page */
        if ($entity instanceof Page) {
            $entity->setUrlTitle($this->util->urlPathEncode($entity->getTitle()));
            $entity->setSlug($this->util->urlPathEncode($entity->getSlug()));
            $slug = $entity->getSlug();
            if(empty($slug)) {
                $entity->defaultSlug();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
            
        }        
        /* Event PAGE */
        if ($entity instanceof Page) {
            if($eventArgs->hasChangedField('isInNavigation') 
                || (!$eventArgs->hasChangedField('isInNavigation') && $entity->getIsInNavigation() == 1 && $eventArgs->hasChangedField('menuTitle'))
                || (!$eventArgs->hasChangedField('isInNavigation') && $entity->getIsInNavigation() == 1 && $eventArgs->hasChangedField('parent'))) {
                $this->unpublishNav();
            }
     
        }
        
    }
       
    public function unpublishNav()
    {
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavPublish, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('KitpagesCmsBundle:Site')->set(Site::IS_NAV_PUBLISHED, 0);
//            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavPublish, $event);
    }
}