<?php

namespace Kitpages\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kitpages\CmsBundle\Entity\Zone
 */
class Zone
{
    /**
     * @var string $label
     */
    private $label;

    /**
     * @var datetime $creationDate
     */
    private $creationDate;

    /**
     * @var datetime $modificationDate
     */
    private $modificationDate;

    /**
     * @var integer $id
     */
    private $id;


    public function __construct()
    {
        $this->blockList = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set creationDate
     *
     * @param datetime $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get creationDate
     *
     * @return datetime $creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param datetime $modificationDate
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;
    }

    /**
     * Get modificationDate
     *
     * @return datetime $modificationDate
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
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
     * @var string $slug
     */
    private $slug;


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
     * @var Kitpages\CmsBundle\Entity\PageZone
     */
    private $listPage;

    /**
     * @var Kitpages\CmsBundle\Entity\ZoneBlock
     */
    private $listBlock;






    /**
     * Get listBlockByPosition
     *
     * @return Doctrine\Common\Collections\Collection $listBlock
     */
    public function getListBlockByPosition()
    {
//        foreach($this->listBlock as $block) {
//            $listReturn[$block->getPosition]
//        }
        //echo var_dump($this->listBlock);
        return $this->listBlock;
    }
    

    /**
     * @var string $pageType
     */
    private $pageType;

    /**
     * @var string $layout
     */
    private $layout;

    /**
     * @var boolean $isActive
     */
    private $isActive;

    /**
     * @var boolean $isPublished
     */
    private $isPublished;

    /**
     * @var array $data
     */
    private $data;

    /**
     * @var datetime $realModificationDate
     */
    private $realModificationDate;

    /**
     * @var datetime $publicationDate
     */
    private $publicationDate;

    /**
     * @var datetime $unpublicationDate
     */
    private $unpublicationDate;


    /**
     * Set pageType
     *
     * @param string $pageType
     */
    public function setPageType($pageType)
    {
        $this->pageType = $pageType;
    }

    /**
     * Get pageType
     *
     * @return string $pageType
     */
    public function getPageType()
    {
        return $this->pageType;
    }

    /**
     * Set layout
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get layout
     *
     * @return string $layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean $isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
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
     * Set realModificationDate
     *
     * @param datetime $realModificationDate
     */
    public function setRealModificationDate($realModificationDate)
    {
        $this->realModificationDate = $realModificationDate;
    }

    /**
     * Get realModificationDate
     *
     * @return datetime $realModificationDate
     */
    public function getRealModificationDate()
    {
        return $this->realModificationDate;
    }

    /**
     * Set publicationDate
     *
     * @param datetime $publicationDate
     */
    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * Get publicationDate
     *
     * @return datetime $publicationDate
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set unpublicationDate
     *
     * @param datetime $unpublicationDate
     */
    public function setUnpublicationDate($unpublicationDate)
    {
        $this->unpublicationDate = $unpublicationDate;
    }

    /**
     * Get unpublicationDate
     *
     * @return datetime $unpublicationDate
     */
    public function getUnpublicationDate()
    {
        return $this->unpublicationDate;
    }
    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;


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
     * Add listPage
     *
     * @param Kitpages\CmsBundle\Entity\PageZone $listPage
     */
    public function addListPage(\Kitpages\CmsBundle\Entity\PageZone $listPage)
    {
        $this->listPage[] = $listPage;
    }

    /**
     * Get listPage
     *
     * @return Doctrine\Common\Collections\Collection $listPage
     */
    public function getListPage()
    {
        return $this->listPage;
    }

    /**
     * @var Kitpages\CmsBundle\Entity\ZoneBlock
     */
    private $listZoneBlock;


    /**
     * Add listZoneBlock
     *
     * @param Kitpages\CmsBundle\Entity\ZoneBlock $listZoneBlock
     */
    public function addListZoneBlock(\Kitpages\CmsBundle\Entity\ZoneBlock $listZoneBlock)
    {
        $this->listZoneBlock[] = $listZoneBlock;
    }

    /**
     * Get listZoneBlock
     *
     * @return Doctrine\Common\Collections\Collection $listZoneBlock
     */
    public function getListZoneBlock()
    {
        return $this->listZoneBlock;
    }
}