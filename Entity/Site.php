<?php
namespace Kitpages\CmsBundle\Entity;
/**
 * Kitpages\CmsBundle\Entity\Site
 */
class Site
{

    const IS_NAV_PUBLISHED = 'IS_NAV_PUBLISHED';
    
    /**
     * @var integer $label
     */
    private $label;


    /**
     * Get label
     *
     * @return integer 
     */
    public function getLabel()
    {
        return $this->label;
    }
    /**
     * @var string $value
     */
    private $value;


    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
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