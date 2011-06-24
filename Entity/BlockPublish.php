<?php

namespace Kitpages\CmsBundle\Entity;

/**
 * Kitpages\CmsBundle\Entity\Block
 */
class BlockPublish
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
     * @var array $data
     */
    private $data;

    /**
     * @var datetime $creationDate
     */
    private $creationDate;

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
}