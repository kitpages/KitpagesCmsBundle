<?php
/**
 * @Gedmo\Tree
 */
namespace Kitpages\CmsBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Kitpages\CmsBundle\Entity\Block
 */
class Block
{

    const BLOCK_TYPE_EDITO = 'edito';

    /**
     * @var string $slug
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("string")
     */
    private $slug;

    /**
     * @var string $blockType
     */
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
     */
    private $blockType;

    /**
     * @var boolean $isPublished
     */
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("boolean")
     */
    private $isPublished = false;

    /**
     * @var array $data
     */
    /** @JMS\Groups({"base"})
     */
    private $data;

    /**
     * @var datetime $realUpdatedAt
     */
    /** @JMS\Groups({"complet"}) */
    private $realUpdatedAt;

    /**
     * @var datetime $publishedAt
     */
    /** @JMS\Groups({"publish"}) */
    private $publishedAt;

    /**
     * @var datetime $unpublishedAt
     */
    /** @JMS\Groups({"publish"}) */
    private $unpublishedAt;

    /**
     * @var datetime $createdAt
     */
    /** @JMS\Groups({"complet"}) */
    private $createdAt;
    /**
     * @var datetime $updatedAt
     */
    /** @JMS\Groups({"complet"}) */
    private $updatedAt;

    /**
     * @var integer $id
     */
    /** @JMS\Groups({"complet"}) */
    private $id;


    /**
     * @var string $template
     */
    /** @JMS\Groups({"base"}) */
    private $template;

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
     * Set template
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Get template
     *
     * @return string $template
     */
    public function getTemplate()
    {
        return $this->template;
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
     * @return boolean $isPublished
     */
    public function getIsPublished()
    {
        return $this->isPublished;
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
     * Set realUpdatedAt
     *
     * @param datetime $realUpdatedAt
     */
    public function setRealUpdatedAt($realUpdatedAt)
    {
        $this->realUpdatedAt = $realUpdatedAt;
    }

    /**
     * Get realUpdatedAt
     *
     * @return datetime $realUpdatedAt
     */
    public function getRealUpdatedAt()
    {
        return $this->realUpdatedAt;
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
     * @return datetime $publishedAt
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
     * @return datetime $unpublishedAt
     */
    public function getUnpublishedAt()
    {
        return $this->unpublishedAt;
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
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
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

    public function defaultSlug(){
        $this->setSlug('block_'.$this->getId());
    }

    /**
     * @var Kitpages\CmsBundle\Entity\BlockPublish
     */
    /** @JMS\Groups({"publish"}) */
    private $blockPublishList;

    /**
     * @var Kitpages\CmsBundle\Entity\ZoneBlock
     */
    /** @JMS\Groups({"base"}) */
    private $zoneBlockList;


    /**
     * Add blockPublishList
     *
     * @param Kitpages\CmsBundle\Entity\BlockPublish $blockPublishList
     */
    public function addBlockPublishList(\Kitpages\CmsBundle\Entity\BlockPublish $blockPublishList)
    {
        $this->blockPublishList[] = $blockPublishList;
    }

    /**
     * Get blockPublishList
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getBlockPublishList()
    {
        return $this->blockPublishList;
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
     * @var array $dataMedia
     */
    /** @JMS\Groups({"base"}) */
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
     * Add blockPublishList
     *
     * @param Kitpages\CmsBundle\Entity\BlockPublish $blockPublishList
     */
    public function addBlockPublish(\Kitpages\CmsBundle\Entity\BlockPublish $blockPublishList)
    {
        $this->blockPublishList[] = $blockPublishList;
    }

    /**
     * Add zoneBlockList
     *
     * @param Kitpages\CmsBundle\Entity\ZoneBlock $zoneBlockList
     */
    public function addZoneBlock(\Kitpages\CmsBundle\Entity\ZoneBlock $zoneBlockList)
    {
        $this->zoneBlockList[] = $zoneBlockList;
    }

    public function __toString() {
        return '['.get_class($this).':'.$this->getId().':'.$this->getSlug().']';
    }
    /**
     * @var string $canonicalUrl
     */
    /** @JMS\Groups({"base"}) */
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


    public function prePersist()
    {
        // Add your code here
    }

    public function preUpdate()
    {
        // Add your code here
    }
}