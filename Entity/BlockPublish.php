<?php

namespace Kitpages\CmsBundle\Entity;

use Kitpages\CmsBundle\Entity\Block;
/**
 * Kitpages\CmsBundle\Entity\BlockPublish
 */
class BlockPublish
{


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
        $this->setCanonicalUrl($block->getCanonicalUrl());
        $this->setBlockType($block->getBlockType());
        $this->setBlock($block);
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
     * @var Kitpages\CmsBundle\Entity\Block
     */
    private $block;


    /**
     * Set block
     *
     * @param Kitpages\CmsBundle\Entity\Block $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * Get block
     *
     * @return Kitpages\CmsBundle\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }
    /**
     * @var array $dataMedia
     */
    private $dataMedia;


    /**
     * Set dataMedia
     *
     * @param array $dataMedia
     */
    public function setDataMedia($dataMedia)
    {
        $this->dataMedia = $dataMedia;
    }

    /**
     * Get dataMedia
     *
     * @return array
     */
    public function getDataMedia()
    {
        return $this->dataMedia;
    }
    /**
     * @var string $canonicalUrl
     */
    private $canonicalUrl;


    /**
     * Set canonicalUrl
     *
     * @param string $canonicalUrl
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        $this->canonicalUrl = $canonicalUrl;
    }

    /**
     * Get canonicalUrl
     *
     * @return string 
     */
    public function getCanonicalUrl()
    {
        return $this->canonicalUrl;
    }
}