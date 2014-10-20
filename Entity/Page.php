<?php

namespace Kitpages\CmsBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Kitpages\CmsBundle\Entity\Page
 */
class Page
{
    /**
     * @var string $slug
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("string")
     */
    private $slug;

    /**
     * @var string $pageType
     */
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
     */
    private $pageType;

    /**
     * @var string $layout
     */
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
     */
    private $layout;

    /**
     * @var boolean $isPublished
     */
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("boolean")
     */
    private $isPublished = false;

    /**
     * @var array $data
     */
    /** @JMS\Groups({"base"})
     */
    private $data;


     /**
     * @var integer $id
     */
    /** @JMS\Groups({"complet", "page_id"})
     *  @JMS\Type("integer")
     */
    private $id;


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
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var datetime $realUpdatedAt
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("DateTime")
     */
    private $realUpdatedAt;

    /**
     * @var datetime $publishedAt
     */
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("DateTime")
     */
    private $publishedAt;

    /**
     * @var datetime $unpublishedAt
     */
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("DateTime")
     */
    private $unpublishedAt;

    /**
     * @var datetime $createdAt
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("DateTime")
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
    /** @JMS\Groups({"language"})
     *  @JMS\Type("string")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("ArrayCollection<Kitpages\CmsBundle\Entity\PageZone>")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
     */
    private $urlTitle;

    /**
     * @var Kitpages\CmsBundle\Entity\PagePublish
     */
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("Kitpages\CmsBundle\Entity\PagePublish")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
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
    /** @JMS\Groups({"tree"})
     *  @JMS\Type("integer")
     */
    private $left;

    /**
     * @var integer $right
     */
    /** @JMS\Groups({"tree"})
     *  @JMS\Type("integer")
     */
    private $right;

    /**
     * @var integer $root
     */
    /** @JMS\Groups({"tree"})
     *  @JMS\Type("integer")
     */
    private $root;

    /**
     * @var integer $level
     */
    /** @JMS\Groups({"tree"})
     *  @JMS\Type("integer")
     */
    private $level;

    /**
     * @var Kitpages\CmsBundle\Entity\Page
     */
    /** @JMS\Groups({"tree"})
     *  @JMS\Type("Kitpages\CmsBundle\Entity\Page")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
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
    /** @JMS\Groups({"publish"})
     *  @JMS\Type("Kitpages\CmsBundle\Entity\NavPublish")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("string")
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
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("boolean")
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
    /** @JMS\Groups({"base"})
     *  @JMS\Type("boolean")
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
     * @var string $forcedUrl
     */
    /** @JMS\Groups({"complet"})
     *  @JMS\Type("string")
     */
    private $forcedUrl;


    /**
     * Set forcedUrl
     *
     * @param string $forcedUrl
     */
    public function setForcedUrl($forcedUrl)
    {
        if (substr($forcedUrl, 0, 1) != '/' && $forcedUrl != null) {
            $forcedUrl = '/'.$forcedUrl;
        }
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
            'forced_url' => $this->getForcedUrl(),
            'left' => $this->getLeft(),
            'right' => $this->getRight(),
            'root' => $this->getRoot(),
            'level' => $this->getLevel(),
            'hasChildren' => $this->getHasChildren()
        );
    }

    /**
     * Tells if the the given page is this page.
     *
     * Useful when not hydrating all fields.
     *
     * @param Page $Page
     * @return Boolean
     */
    public function isPage(Page $page = null)
    {
        return null !== $page && $this->getId() === $page->getId();
    }

    /**
     * Add pageZoneList
     *
     * @param Kitpages\CmsBundle\Entity\PageZone $pageZoneList
     */
    public function addPageZone(\Kitpages\CmsBundle\Entity\PageZone $pageZoneList)
    {
        $this->pageZoneList[] = $pageZoneList;
    }

    public function __toString() {
        return '['.get_class($this).':'.$this->getId().':'.$this->getSlug().']';
    }

    /**
     * @var boolean $isLinkUrlFirstChild
     */
    /** @JMS\Groups({"base"})
     *  @JMS\Type("boolean")
     */
    private $isLinkUrlFirstChild;


    /**
     * Set isLinkUrlFirstChild
     *
     * @param boolean $isLinkUrlFirstChild
     */
    public function setIsLinkUrlFirstChild($isLinkUrlFirstChild)
    {
        $this->isLinkUrlFirstChild = $isLinkUrlFirstChild;
    }

    /**
     * Get isLinkUrlFirstChild
     *
     * @return boolean 
     */
    public function getIsLinkUrlFirstChild()
    {
        return $this->isLinkUrlFirstChild;
    }


    public function prePersist()
    {
        // Add your code here
    }

    public function preUpdate()
    {
        // Add your code here
    }

    /** @JMS\Groups({"complet","children"})
     *  @JMS\Type("array<Kitpages\CmsBundle\Entity\Page>")
     */
    private $children;


    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function addChild(\Kitpages\CmsBundle\Entity\Page $page)
    {
        $this->children[] = $page;
    }

}