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
        $this->data = array();
    }

    public function setData($index, $value)
    {
        $this->data[$index]=$value;
    }

    public function getData($index)
    {
        return $this->data[$index];
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
