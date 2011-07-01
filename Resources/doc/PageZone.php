<?php

namespace Kitpages\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kitpages\CmsBundle\Entity\PageZone
 */
class PageZone
{
    
    /**
     * @var integer $id
     */
    private $id;
    
    /**
     * @var integer $level
     */
    private $level;
    
    /**
     * @var integer $page_id
     */
    private $page_id;

    /**
     * @var integer $zone_id
     */
    private $zone_id;
    
    /**
     * @var integer $position
     */
    private $position;
    


    /**
     * @var Kitpages\CmsBundle\Entity\Zone
     */
    private $zone;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    private $page;


    /**
     * Set page_id
     *
     * @param integer $pageId
     */
    public function setPageId($pageId)
    {
        $this->page_id = $pageId;
    }

    /**
     * Get page_id
     *
     * @return integer 
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * Set zone_id
     *
     * @param integer $zoneId
     */
    public function setZoneId($zoneId)
    {
        $this->zone_id = $zoneId;
    }

    /**
     * Get zone_id
     *
     * @return integer 
     */
    public function getZoneId()
    {
        return $this->zone_id;
    }

    /**
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
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
     * Set page
     *
     * @param Kitpages\CmsBundle\Entity\Page $page
     */
    public function setPage(\Kitpages\CmsBundle\Entity\Page $page)
    {
        $this->page = $page;
    }

    /**
     * Get page
     *
     * @return Kitpages\CmsBundle\Entity\Page 
     */
    public function getPage()
    {
        return $this->page;
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
}