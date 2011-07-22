<?php

namespace Kitpages\CmsBundle\Entity;

/**
 * Kitpages\CmsBundle\Entity\ZoneBlock
 */
class ZoneBlock
{
    /**
     * @var integer $id
     */
    private $id;    
   
    /**
     * @var integer $position
     */
    private $position;


    /**
     * @var Kitpages\CmsBundle\Entity\Zone
     */
    private $zone;

    /**
     * @var Kitpages\CmsBundle\Entity\Block
     */
    private $block;


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
     * @return integer $position
     */
    public function getPosition()
    {
        return $this->position;
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
     * @return Kitpages\CmsBundle\Entity\Zone $zone
     */
    public function getZone()
    {
        return $this->zone;
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
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }
}