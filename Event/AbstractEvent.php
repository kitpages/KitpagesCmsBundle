<?php
namespace Kitpages\CmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    protected $isDefaultPrevented = false;
    
    public function preventDefault()
    {
        $this->isDefaultPrevented = true;
    }
    
    public function isDefaultPrevented()
    {
        return $this->isDefaultPrevented;
    }
}
