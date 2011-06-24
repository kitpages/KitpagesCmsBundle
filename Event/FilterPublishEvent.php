<?php
namespace Kitpages\CmsBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Kitpages\CmsBundle\Entity\Block;

class FilterPublishEvent extends Event
{
    protected $block;

    public function __construct(Block $block)
    {
        $this->block = $block;
    }

    public function getBlock()
    {
        return $this->block;
    }
}
