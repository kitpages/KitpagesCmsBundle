<?php

namespace Kitpages\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kitpages\CmsBundle\Entity\Zone
 */
class Zone
{
    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $renderer
     */
    private $renderer;
    
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var boolean $isPublished
     */
    private $isPublished;


    /**
     * @var datetime $publishedAt
     */
    private $publishedAt;

    /**
     * @var datetime $unpublishedAt
     */
    private $unpublishedAt;
    
    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var Kitpages\CmsBundle\Entity\ZoneBlock
     */
    private $zoneBlockList;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    private $pageList;

    public function __construct()
    {
        $this->zoneBlockList = new \Doctrine\Common\Collections\ArrayCollection();
    $this->pageList = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
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
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * Add pageList
     *
     * @param Kitpages\CmsBundle\Entity\Page $pageList
     */
    public function addPageList(\Kitpages\CmsBundle\Entity\Page $pageList)
    {
        $this->pageList[] = $pageList;
    }

    /**
     * Get pageList
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPageList()
    {
        return $this->pageList;
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
     * @var Kitpages\CmsBundle\Entity\ZonePublish
     */
    private $zonePublish;


    /**
     * Set zonePublish
     *
     * @param Kitpages\CmsBundle\Entity\ZonePublish $zonePublish
     */
    public function setZonePublish(\Kitpages\CmsBundle\Entity\ZonePublish $zonePublish)
    {
        $this->zonePublish = $zonePublish;
    }

    /**
     * Get zonePublish
     *
     * @return Kitpages\CmsBundle\Entity\ZonePublish 
     */
    public function getZonePublish()
    {
        return $this->zonePublish;
    }
    
    public function defaultSlug(){
        $this->setSlug('zone_'.$this->getId());
    }
    

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * Get isPublished
     *
     * @return boolean 
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set publishedAt
     *
     * @param datetime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt
     *
     * @return datetime 
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set unpublishedAt
     *
     * @param datetime $unpublishedAt
     */
    public function setUnpublishedAt($unpublishedAt)
    {
        $this->unpublishedAt = $unpublishedAt;
    }

    /**
     * Get unpublishedAt
     *
     * @return datetime 
     */
    public function getUnpublishedAt()
    {
        return $this->unpublishedAt;
    }


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


}