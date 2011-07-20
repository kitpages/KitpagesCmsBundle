<?php

namespace Kitpages\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Kitpages\CmsBundle\Entity\PagePublish
 */
class PagePublish
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
     * @var array $zoneList
     */
    private $zoneList;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    private $page;


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
     * Set zoneList
     *
     * @param array $zoneList
     */
    public function setZoneList($zoneList)
    {
        $this->zoneList = $zoneList;
    }

    /**
     * Get zoneList
     *
     * @return array 
     */
    public function getZoneList()
    {
        return $this->zoneList;
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
    /**
     * @var string $urlTitle
     */
    private $urlTitle;


    /**
     * Set urlTitle
     *
     * @param string $urlTitle
     */
    public function setUrlTitle($urlTitle)
    {
        $this->urlTitle = $urlTitle;
    }

    /**
     * Get urlTitle
     *
     * @return string 
     */
    public function getUrlTitle()
    {
        return $this->urlTitle;
    }
    
    public function initByPage(Page $page){
        $this->setSlug($page->getSlug());
        $this->setPageType($page->getPageType());
        $this->setLayout($page->getLayout());      
        $this->setLanguage($page->getLanguage());          
        $this->setUrlTitle($page->getUrlTitle());        
        $this->setPage($page);
    }
    
    /**
     * @var string $pageType
     */
    private $pageType;


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
     * @return string 
     */
    public function getPageType()
    {
        return $this->pageType;
    }
    /**
     * @var string $language
     */
    private $language;

    /**
     * @var string $layout
     */
    private $layout;


    /**
     * Set language
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
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
     * @return string 
     */
    public function getLayout()
    {
        return $this->layout;
    }
}