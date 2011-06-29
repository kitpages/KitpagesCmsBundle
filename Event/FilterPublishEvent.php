<?php
namespace Kitpages\CmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Kitpages\CmsBundle\Entity\Block;

class FilterPublishEvent extends Event
{
    protected $block;
    protected $listRenderer;    

    
    public function __construct(Block $block, $listRenderer)
    {
        $this->block = $block;
        $this->listRenderer = $listRenderer;
    }

    public function getBlock()
    {
        return $this->block;
    }
    
    public function getListRenderer()
    {
        return $this->listRenderer;
    }
}
