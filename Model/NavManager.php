<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\Site;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\NavPublish;
use Kitpages\CmsBundle\Event\NavEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;
use Kitpages\SimpleCacheBundle\Model\CacheManager;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
    protected $pageManager = null;
    protected $cacheManager = null;
    protected $logger = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        PageManager $pageManager,
        CacheManager $cacheManager,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->pageManager = $pageManager;
        $this->cacheManager = $cacheManager;
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
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('KitpagesCmsBundle:Page')->moveUp($page, $nbrPosition);
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavMove, $event);
    }
    public function moveDown($page, $nbrPosition)
    {
        $event = new NavEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavMove, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('KitpagesCmsBundle:Page')->moveDown($page, $nbrPosition);
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavMove, $event);
    }
    public function publish()
    {
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavPublish, $event);
        if (! $event->isDefaultPrevented()) {
            $this->cacheManager->clear('kit-cms-navigation-%');
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('DELETE Kitpages\CmsBundle\Entity\NavPublish np');
            $resultDelete = $query->getResult();
            $query = $em->getConnection()->executeUpdate("
                INSERT INTO cms_nav_publish
                (id, parent_id, page_id, lft, rgt, lvl, root, title, slug, forced_url, link_url, is_link_url_first_child)
                SELECT p.id, p.parent_id, p.id, p.lft, p.rgt, p.lvl, p.root, p.menu_title, p.slug, p.forced_url, p.link_url, p.is_link_url_first_child
                FROM cms_page p ORDER BY lft
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
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterNavPublish, $event);
    }

    public function unpublish()
    {
        $event = new NavEvent();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onNavPublish, $event);
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            $em->getRepository('KitpagesCmsBundle:Site')->set(Site::IS_NAV_PUBLISHED, 0);
            $em->flush();
        }
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

    public function afterPublishPage(Event $event)
    {
        $page = $event->getPage();
        $navPublish = $page->getNavPublish();
        if ($navPublish != null) {
            $navPublish->setForcedUrl($page->getForcedUrl());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->cacheManager->clear('kit-cms-navigation-%');
        }
    }

    public function afterModyPagePublish(Event $event)
    {
        $pagePublish = $event->getPagePublish();
        $pagePublishNew = $event->getPagePublishNew();
        if ($pagePublishNew instanceof PagePublish) {
            $pagePublishDataNew = $pagePublishNew->getData();
            $isInNavigation = $pagePublishDataNew['page']['is_in_navigation'];
            if ($isInNavigation &&
                (
                    !($pagePublish instanceof PagePublish)
                    || $pagePublish->getForcedUrl() != $pagePublishNew->getForcedUrl()
                    || $pagePublish->getUrlTitle() != $pagePublishNew->getUrlTitle()
                )
            ) {
                $this->unpublish();
            }
        }
    }



}
