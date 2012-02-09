<?php
namespace Kitpages\CmsBundle\Entity;
/**
 * Kitpages\CmsBundle\Entity\Site
 */
class UserPreference
{

    /**
     * @var integer $userId
     */
    private $userId;

    /**
     * @var array $dataTree
     */
    private $dataTree;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var integer $id
     */
    private $id;



    /**
     * Set dataTree
     *
     * @param array $dataTree
     */
    public function setDataTree($dataTree)
    {
        $this->dataTree = $dataTree;
    }

    /**
     * Get dataTree
     *
     * @return array 
     */
    public function getDataTree()
    {
        return $this->dataTree;
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
     * @var string $userName
     */
    private $userName;


    /**
     * Set userName
     *
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }
}