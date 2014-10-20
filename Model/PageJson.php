<?php

namespace Kitpages\CmsBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Kitpages\CmsBundle\Entity\PageJson
 */
class PageJson extends Page
{
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



}