<?php
namespace Kitpages\CmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    protected $data = array();
    protected $isDefaultPrevented = false;
    
    public function preventDefault()
    {
        $this->isDefaultPrevented = true;
    }
    
    public function isDefaultPrevented()
    {
        return $this->isDefaultPrevented;
    }
    
    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }
    
    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }
        return $this->data[$key];
    }

}
