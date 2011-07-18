<?php
namespace Kitpages\CmsBundle\Event;

use Kitpages\CmsBundle\Event\AbstractEvent;
use Kitpages\CmsBundle\Entity\Page;

class PageEvent extends AbstractEvent
{
    protected $page;
    protected $listLayout; 
    protected $listRenderer;

    
    public function __construct(Page $page, $listLayout = null, $listRenderer = null)
    {
        $this->page = $page;
        $this->listLayout = $listLayout;        
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getListLayout()
    {
        return $this->listLayout;
    }
 
    public function getListRenderer()
    {
        return $this->listRenderer;
    }
    
}
