<?php
namespace Kitpages\CmsBundle\Event;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Event\AbstractEvent;

class BlockEvent extends AbstractEvent
{
    protected $block;
    protected $listRenderer;
    
    public function __construct(Block $block, $listRenderer = null)
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
