<?php
namespace Kitpages\CmsBundle\Model;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Event\FilterPublishEvent;
use Kitpages\CmsBundle\KitpagesCmsStoreEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
class BlockManager
{
 
    public function __construct(EventDispatcher $dispatcher){
        $this->dispatcher = $dispatcher;
    }      
    
    public function publish($block)
    {
        $event = new FilterPublishEvent($block);
        $this->dispatcher->dispatch(KitpagesCmsStoreEvents::onBlockPublish, $event);
    }
}
