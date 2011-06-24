<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;


class CmsManager
{
    const CONTEXT_PROD = 0;
    const CONTEXT_PREVIEW = 1;
    const CONTEXT_EDIT = 2;    
    private $_layout = null;
    private $_context = null;
    private $_targetParameter = null;
    public function __construct($defaultLayout, $targetParam)
    {
        $this->_layout = $defaultLayout;
        $this->_targetParameter = $targetParam;
    }
    
    public function onCoreController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            //echo "gloubi=".$this->getLayout();
        }
    }

    public function getContext()
    {
        return $this->_layout;
    }
    public function setContext($layout)
    {
        $this->_layout = $layout;
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