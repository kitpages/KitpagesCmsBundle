<?php

namespace Kitpages\CmsBundle\Entity;

use Kitpages\CmsBundle\Entity\Block;
/**
 * Kitpages\CmsBundle\Entity\BlockPublish
 */
class BlockPublish
{
    
    /**
     * @var integer $blockId
     */
    private $blockId;
    
    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $blockType
     */
    private $blockType;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    
    /**
     * @var integer $id
     */
    private $id;


    public function initByBlock(Block $block) {
        $this->setSlug($block->getSlug());
        $this->setBlockType($block->getBlockType());
        $this->setBlock($block);
    }

    /**
     * @var Kitpages\CmsBundle\Entity\Block
     */
    private $block;


    /**
     * Set blockId
     *
     * @param integer $blockId
     */
    public function setBlockId($blockId)
    {
        $this->blockId = $blockId;
    }

    /**
     * Get blockId
     *
     * @return integer $blockId
     */
    public function getBlockId()
    {
        return $this->blockId;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set blockType
     *
     * @param string $blockType
     */
    public function setBlockType($blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * Get blockType
     *
     * @return string $blockType
     */
    public function getBlockType()
    {
        return $this->blockType;
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set block
     *
     * @param Kitpages\CmsBundle\Entity\Block $block
     */
    public function setBlock(\Kitpages\CmsBundle\Entity\Block $block)
    {
        $this->block = $block;
    }

    /**
     * Get block
     *
     * @return Kitpages\CmsBundle\Entity\Block $block
     */
    public function getBlock()
    {
        return $this->block;
    }
    /**
     * @ORM\prePersist
     */
    public function prePersist()
    {
        // Add your code here
    }

    /**
     * @ORM\preUpdate
     */
    public function preUpdate()
    {
        // Add your code here
    }
    /**
     * @var string $renderer
     */
    private $renderer;


    /**
     * Set renderer
     *
     * @param string $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Get renderer
     *
     * @return string 
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
    /**
     * @var Kitpages\CmsBundle\Entity\ZoneBlock
     */
    private $zoneBlockList;

    public function __construct()
    {
        $this->zoneBlockList = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add zoneBlockList
     *
     * @param Kitpages\CmsBundle\Entity\ZoneBlock $zoneBlockList
     */
    public function addZoneBlockList(\Kitpages\CmsBundle\Entity\ZoneBlock $zoneBlockList)
    {
        $this->zoneBlockList[] = $zoneBlockList;
    }

    /**
     * Get zoneBlockList
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getZoneBlockList()
    {
        return $this->zoneBlockList;
    }
}