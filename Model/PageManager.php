<?php
namespace Kitpages\CmsBundle\Model;

use JMS\Serializer\SerializationContext;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PageZone;
use Kitpages\CmsBundle\Entity\PagePublish;
use Kitpages\CmsBundle\Entity\Zone;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\ZonePublish;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Kitpages\CmsBundle\Event\PageEvent;
use Kitpages\CmsBundle\Event\PagePublishEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraint;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PageManager
{
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $zoneManager = null;
    protected $cmsFileManager = null;
    protected $logger = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        ZoneManager $zoneManager,
        CmsFileManager $cmsFileManager,
        $jmsSerializer,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->zoneManager = $zoneManager;
        $this->cmsFileManager = $cmsFileManager;
        $this->jmsSerializer = $jmsSerializer;
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
     * @return CmsFileManager $cmsFileManager
     */
    public function getCmsFileManager() {
        return $this->cmsFileManager;
    }

    /**
     * @return ZoneManager $zoneManager
     */
    public function getZoneManager() {
        return $this->zoneManager;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    ////
    // repo
    ////
    /**
     * @return Page $page
     */

    public function getChildrenPageBySlug($slug)
    {
        $em = $this->getDoctrine()->getManager();

        $children = $em->getRepository('KitpagesCmsBundle:Page')->children(
            $this->getPageBySlug($slug),
            true
        );

        return $children;
    }

//    public function getChildrenPageJsonBySlug($slug, array $optionExport)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $children = $em->getRepository('KitpagesCmsBundle:Page')->children(
//            $this->getPageBySlug($slug),
//            true
//        );
//
//        $childrenJson = array();
//
//        foreach($children as $child) {
//            $childrenJson[] = $this->getPageJsonByPage($child, $optionExport);
//        }
//        return $childrenJson;
//    }

    public function getPageBySlug($slug)
    {
        $page = $this->doctrine->getManager()->getRepository('Kitpages\CmsBundle\Entity\Page')->findOneBy(array('slug' => $slug));
        return $page;
    }
    ////
    // actions
    ////

    public function getObjectBySerializeFormat($content, $format, $entity)
    {
        $page = $this->jmsSerializer->deserialize(
            $content,
            $entity,
            $format
        );
        return $page;
    }

    public function updatePageByOption(Page $page, $option)
    {
        if (isset($option['page_language'])) {
            $page->setLanguage($option['page_language']);
        }
    }

    public function getContentPageAndChildrenJsonBySlug($slug, array $optionExport)
    {
        $contentJsonRoot = $this->getContentPageJsonBySlug($slug, $optionExport);
        $pageRootJson = $this->getObjectBySerializeFormat($contentJsonRoot, 'json', 'Kitpages\CmsBundle\Entity\Page');

//        $pageRootJson->setChildren($this->getChildrenPageJsonBySlug($slug, $optionExport));

        return $this->getJson($pageRootJson, $optionExport);
    }

    public function getPageJsonByPage(Page $page, $optionExport)
    {
        $contentJson = $this->getJson($page, $optionExport);

        $pageJson = $this->getObjectBySerializeFormat($contentJson, 'json', 'Kitpages\CmsBundle\Entity\Page');
        return $pageJson;
    }

    public function getContentPageJsonBySlug($slug, array $optionExport)
    {
        $page = $this->getPageBySlug($slug);

        return $this->getJson($this->getPageJsonByPage($page, $optionExport), $optionExport);
    }

    public function getJson($page, array $optionExport)
    {
        $pageJson = $this->jmsSerializer->serialize(
            $page,
            'json',
            SerializationContext::create()->setGroups($optionExport)
        );
        return $pageJson;
    }

//    public function createNewPageByXml($xml)
//    {
//        return $this->getObjectBySerializeFormat($xml, 'xml');
//    }
//
//    public function getPageXmlBySlug($slug, array $optionExport)
//    {
//        $pageXml = $this->getPageXml($this->getPageBySlug($slug), $optionExport);
//        return $pageXml;
//    }
//
//    public function getPageXml(Page $page, array $optionExport)
//    {
//        $pageXml = $this->jmsSerializer->serialize(
//            $page,
//            'xml',
//            SerializationContext::create()->setGroups($optionExport)
//        );
//        return $pageXml;
//    }

    public function unpendingDelete(Page $page)
    {
        // throw on event
        $event = new PageEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onPagePendingDelete, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            $page->setIsPendingDelete(0);
            $em->flush();
        }
        // throw after event
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPagePendingDelete, $event);
    }

    public function pendingDelete(Page $page)
    {
        // throw on event
        $event = new PageEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onPagePendingDelete, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            $page->setIsPendingDelete(1);
            $em->flush();
        }
        // throw after event
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPagePendingDelete, $event);
    }

    public function delete(Page $page)
    {
        // throw on event
        $event = new PageEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onPageDelete, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            $zoneManager = $this->getZoneManager();
            foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByPage($page) as $zone){
                $nbr = $em->getRepository('KitpagesCmsBundle:PageZone')->nbrPageZoneByZoneWithPageDiff($zone, $page);
                if ($nbr == 0) {
                    $zoneManager->delete($zone);
                }
            }
            $pageData=$page->getData();
            if (!isset($pageData['root'])) {
                $pageData['root'] = array();
            }
            $cmsFileManager = $this->cmsFileManager;
            $cmsFileManager->delete($pageData['root']);
            $em->remove($page);
            $em->flush();
        }
        // throw after event
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPageDelete, $event);
    }

    public function publishPage(Page $page, array $listLayout, array $listRenderer, array $dataInheritanceList)
    {
        $event = new PageEvent($page, $listLayout);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onPagePublish, $event);
        $cmsFileManager = $this->getCmsFileManager();
        if (! $event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getManager();
            if ($page->getIsPendingDelete()) {
                $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);
                $eventPublish = new PagePublishEvent($pagePublish);
                $this->getDispatcher()->dispatch(KitpagesCmsEvents::onModifyPagePublish, $eventPublish);
                if (!is_null($pagePublish)) {
                    $pagePublishData = $pagePublish->getData();
                    if (isset($pagePublishData['media'])) {
                        $cmsFileManager->unpublishFileList($pagePublishData['media']);
                    }
                    $em->remove($pagePublish);
                    $em->flush();
                }
                $this->delete($page);
            } else {
                // publish zone
                $em = $this->getDoctrine()->getManager();
                foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByPage($page) as $zone){
                    $this->getZoneManager()->publish($zone, $listRenderer);
                }
                $em->flush();
                // remove old pagePublish
                $zonePublish = null;
                $pagePublish = $em->getRepository('KitpagesCmsBundle:PagePublish')->findByPage($page);

                $eventPublish = new PagePublishEvent($pagePublish);
                $this->getDispatcher()->dispatch(KitpagesCmsEvents::onModifyPagePublish, $eventPublish);
                if (!is_null($pagePublish)) {
                    $em->remove($pagePublish);
                    $em->flush();
                }

                $zoneList = array();
                // create page publish
                foreach($em->getRepository('KitpagesCmsBundle:Zone')->findByPage($page) as $zone){
                    $zoneList[] = $zone->getId();
                }

                $data['root'] = $em->getRepository('KitpagesCmsBundle:Page')->getDataWithInheritance($page, $dataInheritanceList);

                $cmsFileManager->publishDataMediaList($data['root']);
                $listMedia = $cmsFileManager->mediaList($data['root'], true);
                $data['media'] = $listMedia;

                $pagePublishNew = new PagePublish();
                $pagePublishNew->initByPage($page, $data);
                $pagePublishNew->setZoneList(array("zoneList"=>$zoneList));
                $page->setIsPublished(true);
                $page->setPagePublish($pagePublishNew);
                $em->flush();
                $eventPublish->setPagePublishNew($pagePublishNew);
            }
            $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterModifyPagePublish, $eventPublish);
        }
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPagePublish, $event);
    }

    public function publish(Page $page, array $layoutList, array $listRenderer, array $dataInheritanceList, $childrenPublish)
    {

        $event = new PageEvent($page, $layoutList);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onMultiplePagePublish, $event);
        if ($childrenPublish) {
            $em = $this->getDoctrine()->getManager();
            $pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
            //$pageChildren = $em->getRepository('KitpagesCmsBundle:Page')->children($page, false, 'level', 'DESC');
            foreach($pageChildren as $pageChild) {
                //$this->publishPage($pageChild, $layoutList, $listRenderer, $dataInheritanceList);
                $this->publish($pageChild, $layoutList, $listRenderer, $dataInheritanceList, $childrenPublish);
            }
        }
        $this->publishPage($page, $layoutList, $listRenderer, $dataInheritanceList);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterMultiplePagePublish, $event);
    }

    public function afterModify($page, $oldPage)
    {
        if ($oldPage != $page) {
            $page->setRealUpdatedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->unpublish($page);
            $event = new PageEvent($page);
            $event->setData('oldPageData', $oldPage->getData());
            $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPageModify, $event);
        }
    }

    public function unpublish($page){
        $event = new PageEvent($page);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onPageUnpublish, $event);
        $em = $this->getDoctrine()->getManager();
        $page->setIsPublished(false);
        $em->flush();
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterPageUnpublish, $event);
    }

    public function createZoneInPage(Page $page, $locationInPage)
    {
        $em = $this->getDoctrine()->getManager();
        $zone = new Zone();
        $zone->setSlug('');
        $zone->setIsPublished(false);
        $em->persist($zone);
        $em->flush();
        $pageZone = new PageZone();
        $pageZone->setPage($page);
        $pageZone->setZone($zone);
        $pageZone->setLocationInPage($locationInPage);
        $em->persist($pageZone);
        $em->flush();
    }

    public function createNewPage(Page $page, $dataForm)
    {
        $page->setIsPublished(false);
        $em = $this->doctrine->getManager();

        $parent_id = $dataForm['parent_id'];
        $repositoryPage = $em->getRepository('KitpagesCmsBundle:Page');
        if (!empty($parent_id)) {
            $pageParent = $repositoryPage->find($parent_id);
            $page->setLanguage($pageParent->getLanguage());
            $page->setParent($pageParent);
        }
        $nextSiblingSlug = $dataForm['next_sibling_slug'];
        $prevSiblingSlug = $dataForm['prev_sibling_slug'];
        if($nextSiblingSlug != null) {
            $sibling = $repositoryPage->findOneBySlug($nextSiblingSlug);
            $repositoryPage->persistAsNextSiblingOf($page, $sibling);
        }elseif($prevSiblingSlug != null){
            $sibling = $repositoryPage->findOneBySlug($prevSiblingSlug);
            $repositoryPage->persistAsPrevSiblingOf($page, $sibling);
        }
        $em->persist($page);

        $em->flush();
    }

    ////
    // event listener
    ////
    public function afterZoneUnpublish(Event $event)
    {
        $zone = $event->getZone();
        $em = $this->getDoctrine()->getManager();
        foreach($em->getRepository('KitpagesCmsBundle:Page')->findByZone($zone) as $page) {
            $this->unpublish($page);
        }
    }

    ////
    //  Validator
    ////
    public function validateUniqueForceUrlPublish(Page $page, Constraint $constraint)
    {

        if ($page->getForcedUrl() == null) {
            return true;
        }

        $em = $this->getDoctrine()->getManager();
        $pagePublishConflictList = $em->getRepository('KitpagesCmsBundle:PagePublish')->findBy(array('forcedUrl' => $page->getForcedUrl()));
        // there is no conflictual user
        if (empty($pagePublishConflictList)) {
            return true;
        }

        foreach ($pagePublishConflictList as $pagePublishConflict) {
            if ($pagePublishConflict->getPage()->getId() != $page->getId()) {
                return false;
            }
        }


        return true;
    }



}
