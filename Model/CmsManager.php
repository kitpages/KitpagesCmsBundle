<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Kitpages\CmsBundle\Entity\Block;

class CmsManager
{
    const VIEW_MODE_PROD = 0;
    const VIEW_MODE_PREVIEW = 1;
    const VIEW_MODE_EDIT = 2;    
    private $_session = null;
    private $_doctrine = null;
    private $_layout = null;
    private $_targetParameter = null;
    
    public function __construct(Registry $doctrine, Session $session, $defaultLayout, $targetParam)
    {
        $this->_layout = $defaultLayout;
        $this->_targetParameter = $targetParam;
        $this->_doctrine = $doctrine;
        $this->_session = $session;
    }      
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->_doctrine;
    }  

    /**
     * @return Registry $doctrine
     */
    public function getSession() {
        return $this->_session;
    } 
    
    public function onCoreController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            //echo "gloubi=".$this->getLayout();
        }
    }

    public function getViewMode()
    {
        return $this->getSession()->get('kitpages_cms_context_view_mode');
    }
    public function setViewMode($viewMode)
    {
        $this->getSession()->set('kitpages_cms_context_view_mode', $viewMode);
    }
    
    public function getLayout()
    {
        return $this->_layout;
    }
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }
    
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
//        if (method_exists($entity,'setCreatedAt')) {
//            $entity->setCreatedAt(new \DateTime());
//        }
//        if (method_exists($entity,'setUpdatedAt')) {
//            $entity->setUpdatedAt(new \DateTime());
//        }
        if ($entity instanceof Block) {
//            $entity->setRealUpdatedAt(new \DateTime());
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
  
    public function onFlush(OnFlushEventArgs $event)
    {
    }
    
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $uom = $em->getUnitOfWork();
//        if (method_exists($entity,'setUpdatedAt')) {
//            $entity->setUpdatedAt(new \DateTime());
//        }
        
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
            }
         
            if (($eventArgs->hasChangedField('data')
                || $eventArgs->hasChangedField('template'))
                && $entity->getIsPublished() == 1
            ) {
                $entity->setIsPublished(false);
                $entity->setUnpublishedAt(new \DateTime());
                $uom->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
            }
            
        }
    }    
  
}