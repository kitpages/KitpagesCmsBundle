<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Kitpages\CmsBundle\Entity\Block;

class CmsManager
{
  
    protected $doctrine = null;
    protected $layout = null;
    
    public function __construct(
        Registry $doctrine,
        $defaultLayout,
        LoggerInterface $logger
    )
    {
        $this->layout = $defaultLayout;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }      
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    } 
    
    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

   
    public function onCoreController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            //echo "gloubi=".$this->getLayout();
        }
    }
    
    public function getLayout()
    {
        return $this->layout;
    }
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
   
    
  
}