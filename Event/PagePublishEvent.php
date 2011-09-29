<?php
namespace Kitpages\CmsBundle\Event;

use Kitpages\CmsBundle\Event\AbstractEvent;
use Kitpages\CmsBundle\Entity\PagePublish;

class PagePublishEvent extends AbstractEvent
{
    protected $pagePublish;
    protected $pagePublishNew;

    public function __construct($pagePublish)
    {
        $this->pagePublish = $pagePublish;

    }

    public function getPagePublish()
    {
        return $this->pagePublish;
    }

    public function getPagePublishNew()
    {
        return $this->pagePublishNew;
    }

    public function setPagePublishNew($pagePublish)
    {
        return $this->pagePublishNew = $pagePublish;
    }

}
