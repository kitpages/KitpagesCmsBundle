<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Kitpages\CmsBundle\Entity\Block;

class CmsManager
{
  
    private $_doctrine = null;
    private $_layout = null;
    private $_targetParameter = null;
    
    public function __construct(Registry $doctrine, $defaultLayout, $targetParam)
    {
        $this->_layout = $defaultLayout;
        $this->_targetParameter = $targetParam;
        $this->_doctrine = $doctrine;
    }      
    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->_doctrine;
    }  

   
    public function onCoreController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            //echo "gloubi=".$this->getLayout();
        }
    }
    
    public function getLayout()
    {
        return $this->_layout;
    }
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }
    
   
  
}