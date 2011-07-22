<?php
namespace Kitpages\CmsBundle\Event;

use Kitpages\CmsBundle\Event\AbstractEvent;

class NavEvent extends AbstractEvent
{
 
    protected $page;
    
    public function __construct($page = null)
    {
        $this->page = $page;
    }
    public function getPage()
    {
        return $this->page;
    }    
}
