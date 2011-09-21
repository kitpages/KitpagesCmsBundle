<?php

namespace Kitpages\CmsBundle\Entity;

use Gedmo\Sluggable\Util\Urlizer;
/**
 * Kitpages\CmsBundle\Entity\Page
 */
class Page
{
    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $pageType
     */
    private $pageType;

    /**
     * @var string $layout
     */
    private $layout;

    /**
     * @var boolean $isPublished
     */
    private $isPublished = false;

    /**
     * @var array $data
     */
    private $data;


     /**
     * @var integer $id
     */
    private $id;


    public function __construct()
    {
        $this->zoneList = new \Doctrine\Common\Collections\ArrayCollection();
    }

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


    public function prePersist()
    {
        $this->urlTitle = Urlizer::transliterate($this->title, '-');
        $this->slug = Urlizer::transliterate($this->slug, '-');
    }

    public function preUpdate()
    {
        $this->urlTitle = Urlizer::transliterate($this->title, '-');
        $this->slug = Urlizer::transliterate($this->slug, '-');
    }


    /**
     * @var datetime $realUpdatedAt
     */
    private $realUpdatedAt;

    /**
     * @var datetime $publishedAt
     */
    private $publishedAt;

    /**
     * @var datetime $unpublishedAt
     */
    private $unpublishedAt;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;


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
     * @var string $language
     */
    private $language;



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
     * @var Kitpages\CmsBundle\Entity\PageZone
     */
    private $pageZoneList;


    /**
     * Add pageZoneList
     *
     * @param Kitpages\CmsBundle\Entity\PageZone $pageZoneList
     */
    public function addPageZoneList(\Kitpages\CmsBundle\Entity\PageZone $pageZoneList)
    {
        $this->pageZoneList[] = $pageZoneList;
    }

    /**
     * Get pageZoneList
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPageZoneList()
    {
        return $this->pageZoneList;
    }
    /**
     * @var string $urlTitle
     */
    private $urlTitle;

    /**
     * @var Kitpages\CmsBundle\Entity\PagePublish
     */
    private $pagePublish;


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

    /**
     * Set pagePublish
     *
     * @param Kitpages\CmsBundle\Entity\PagePublish $pagePublish
     */
    public function setPagePublish(\Kitpages\CmsBundle\Entity\PagePublish $pagePublish)
    {
        $this->pagePublish = $pagePublish;
    }

    /**
     * Get pagePublish
     *
     * @return Kitpages\CmsBundle\Entity\PagePublish
     */
    public function getPagePublish()
    {
        return $this->pagePublish;
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
    /**
     * @var integer $left
     */
    private $left;

    /**
     * @var integer $right
     */
    private $right;

    /**
     * @var integer $root
     */
    private $root;

    /**
     * @var integer $level
     */
    private $level;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    private $parent;


    /**
     * Set left
     *
     * @param integer $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * Get left
     *
     * @return integer
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * Set right
     *
     * @param integer $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * Get right
     *
     * @return integer
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set root
     *
     * @param integer $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
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
     * Set parent
     *
     * @param Kitpages\CmsBundle\Entity\Page $parent
     */
    public function setParent(\Kitpages\CmsBundle\Entity\Page $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Kitpages\CmsBundle\Entity\Page
     */
    public function getParent()
    {
        return $this->parent;
    }
    /**
     * @var string $linkUrl
     */
    private $linkUrl;


    /**
     * Set linkUrl
     *
     * @param string $linkUrl
     */
    public function setLinkUrl($linkUrl)
    {
        $this->linkUrl = $linkUrl;
    }

    /**
     * Get linkUrl
     *
     * @return string
     */
    public function getLinkUrl()
    {
        return $this->linkUrl;
    }
    /**
     * @var Kitpages\CmsBundle\Entity\NavPublish
     */
    private $navPublish;


    /**
     * Set navPublish
     *
     * @param Kitpages\CmsBundle\Entity\NavPublish $navPublish
     */
    public function setNavPublish(\Kitpages\CmsBundle\Entity\NavPublish $navPublish)
    {
        $this->navPublish = $navPublish;
    }

    /**
     * Get navPublish
     *
     * @return Kitpages\CmsBundle\Entity\NavPublish
     */
    public function getNavPublish()
    {
        return $this->navPublish;
    }
    /**
     * @var string $menuTitle
     */
    private $menuTitle;


    /**
     * Set menuTitle
     *
     * @param string $menuTitle
     */
    public function setMenuTitle($menuTitle)
    {
        $this->menuTitle = $menuTitle;
    }

    /**
     * Get menuTitle
     *
     * @return string
     */
    public function getMenuTitle()
    {
        return $this->menuTitle;
    }
    /**
     * @var boolean $isPendingDelete
     */
    private $isPendingDelete = false;


    /**
     * Set isPendingDelete
     *
     * @param boolean $isPendingDelete
     */
    public function setIsPendingDelete($isPendingDelete)
    {
        $this->isPendingDelete = $isPendingDelete;
    }

    /**
     * Get isPendingDelete
     *
     * @return boolean
     */
    public function getIsPendingDelete()
    {
        return $this->isPendingDelete;
    }
    /**
     * @var boolean $isInNavigation
     */
    private $isInNavigation = true;


    /**
     * Set isInNavigation
     *
     * @param boolean $isInNavigation
     */
    public function setIsInNavigation($isInNavigation)
    {
        $this->isInNavigation = $isInNavigation;
    }

    /**
     * Get isInNavigation
     *
     * @return boolean
     */
    public function getIsInNavigation()
    {
        return $this->isInNavigation;
    }

    public function defaultSlug(){
        $this->setSlug('page_'.$this->getId());
    }

    /**
     * @var boolean $isActive
     */
    private $isActive;


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
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
    /**
     * @var string $forcedUrl
     */
    private $forcedUrl;


    /**
     * Set forcedUrl
     *
     * @param string $forcedUrl
     */
    public function setForcedUrl($forcedUrl)
    {
        $this->forcedUrl = $forcedUrl;
    }

    /**
     * Get forcedUrl
     *
     * @return string
     */
    public function getForcedUrl()
    {
        return $this->forcedUrl;
    }

    /**
     * Get hasChildren
     *
     * @return boolean
     */
    public function getHasChildren()
    {
        if (($this->right - $this->left) > 1) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * Get list fields
     *
     * @return array
     */
    public function getDataPage()
    {
        return array(
            'title' => $this->getTitle(),
            'slug' => $this->getSlug(),
            'language' => $this->getLanguage(),
            'menu_title' => $this->getMenuTitle(),
            'is_in_navigation' => $this->getIsInNavigation(),
            'forced_url' => $this->getForcedUrl()
        );
    }


}