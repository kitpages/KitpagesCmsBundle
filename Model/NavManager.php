<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\Site;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\NavPublish;
use Kitpages\CmsBundle\Event\NavEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class NavManager
{
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $templating = null;
    protected $pageManager = null;
    protected $logger = null;
    
    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        $templating,
        PageManager $pageManager,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->templating = $templating;
        $this->pageManager = $pageManager;        
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
     * @return $pageManager
     */
    public function getPageManager() {
        return $this->pageManager;
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
    public function moveUp($page, $nbrPosition)
    {
        $event = new NavEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavMove, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('KitpagesCmsBundle:Page')->moveUp($page, $nbrPosition);
        }    
        $event = new NavEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavMove, $event);
    } 
    public function moveDown($page, $nbrPosition)
    {
        $event = new NavEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavMove, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('KitpagesCmsBundle:Page')->moveDown($page, $nbrPosition);    
        }    
        $event = new NavEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavMove, $event);
    }      
    public function publish()
    {
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavPublish, $event);
        if (! $event->isDefaultPrevented()) {
            
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery('DELETE Kitpages\CmsBundle\Entity\NavPublish np');
            $resultDelete = $query->getResult();
            $query = $em->getConnection()->executeUpdate("
                INSERT INTO cms_nav_publish
                (id, parent_id, page_id, lft, rgt, lvl, root, title, slug, forced_url)
                SELECT id, parent_id, id, lft, rgt, lvl, root, menu_title, slug, forced_url
                FROM cms_page order by lft
            ");

            $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->findByPageIsNotInNavigation();
            foreach($navPublishList as $navPublish) {
                $em->getRepository('KitpagesCmsBundle:NavPublish')->removeWithChildren($navPublish);
            }

            $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->findByNoPagePublish();
            foreach($navPublishList as $navPublish) {
                $em->getRepository('KitpagesCmsBundle:NavPublish')->removeWithChildren($navPublish);
            }

            $em->getRepository('KitpagesCmsBundle:Site')->set(Site::IS_NAV_PUBLISHED, 1);

            $em->flush();
        }
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavPublish, $event);
    }

    public function unpublish()
    {
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavPublish, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('KitpagesCmsBundle:Site')->set(Site::IS_NAV_PUBLISHED, 0);
//            $em->flush();
        }        
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavPublish, $event);        
    }
    ////
    // event listener
    ////
    public function afterMove(Event $event)
    {
        $page = $event->getPage();
        if ($page->getIsInNavigation()) {
            $this->unpublish();
        }
    }
    ////
    // doctrine events
    ////
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Page) {
            if($entity->getIsInNavigation() == 1) {
                $this->unpublish();
            }
        }
    }
    
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        
        /* Event PAGE */
        if ($entity instanceof Page) {
            if($eventArgs->hasChangedField('isInNavigation') 
                || (!$eventArgs->hasChangedField('isInNavigation') && $entity->getIsInNavigation() == 1 && $eventArgs->hasChangedField('menuTitle'))
                || (!$eventArgs->hasChangedField('isInNavigation') && $entity->getIsInNavigation() == 1 && $eventArgs->hasChangedField('parent'))) {
                $this->unpublish();
            }
     
        }
    }   
    
}
