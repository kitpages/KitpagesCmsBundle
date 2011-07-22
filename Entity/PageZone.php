<?php

namespace Kitpages\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kitpages\CmsBundle\Entity\ZoneBlock
 */
class PageZone
{
    /**
     * @var integer $id
     */
    private $id;    
   
    /**
     * @var string $locationInPage
     */
    private $locationInPage;



    /**
     * @var Kitpages\CmsBundle\Entity\Zone
     */
    private $zone;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    private $page;


    /**
     * Set locationInPage
     *
     * @param string $locationInPage
     */
    public function setLocationInPage($locationInPage)
    {
        $this->locationInPage = $locationInPage;
    }

    /**
     * Get locationInPage
     *
     * @return string 
     */
    public function getLocationInPage()
    {
        return $this->locationInPage;
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