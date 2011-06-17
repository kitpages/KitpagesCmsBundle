<?php

namespace Kitpages\CmsBundle\Entity;

/**
 * Kitpages\CmsBundle\Entity\Block
 */
class Block
{
    /**
     * @var string $label
     */
    private $label;

    /**
     * @var string $blockType
     */
    private $blockType;

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
     * @var string $template
     */
    private $template;


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
     */
    public function prePersist()
    {
        if (!$this->getId()) {
            $this->setCreationDate(new \DateTime());
        }
        $this->setModificationDate(new \DateTime());
    }

    /**
     */
    public function preUpdate()
    {
        // Add your code here
    }
}