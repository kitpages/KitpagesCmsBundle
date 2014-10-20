<?php
namespace Kitpages\CmsBundle\Entity;

use Kitpages\CmsBundle\Entity\Zone;
/**
 * Kitpages\CmsBundle\Entity\Zone
 */
class ZonePublish
{

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var Kitpages\CmsBundle\Entity\ZonePublish
     */
    private $zone;


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
     * @return array
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
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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

    public function initByZone(Zone $zone){
        $this->setSlug($zone->getSlug());
        $this->setCanonicalUrl($zone->getCanonicalUrl());
        $this->setTitle($zone->getTitle());
        $this->setZone($zone);
    }


    /**
     * Set zone
     *
     * @param Kitpages\CmsBundle\Entity\Zone $zone
     */
    public function setZone(\Kitpages\CmsBundle\Entity\Zone $zone)
    {
        $this->zone = $zone;
    }

    /**
     * Get zone
     *
     * @return Kitpages\CmsBundle\Entity\Zone
     */
    public function getZone()
    {
        return $this->zone;
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
    /**
     * @var string $title
     */
    private $title;


    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
}