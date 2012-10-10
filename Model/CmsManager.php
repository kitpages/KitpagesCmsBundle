<?php
namespace Kitpages\CmsBundle\Model;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Kitpages\CmsBundle\Entity\Block;

class CmsManager
{

    protected $session = null;
    protected $doctrine = null;
    protected $layout = null;

    public function __construct(
        Session $session            ,
        Registry $doctrine,
        $defaultLayout,
        LoggerInterface $logger
    )
    {
        $this->session = $session;
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
     * @return Session $session
     */
    public function getSession() {
        return $this->session;
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

    public function getCurrentLanguage()
    {
        return $this->getSession()->getLocale();
    }

}