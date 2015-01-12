<?php
namespace Kitpages\CmsBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Model\ZoneService;

class CmsExtension extends \Twig_Extension
{

    protected $context = null;
    protected $zoneService = null;


    public function __construct(
        ZoneService $zoneService,
        Context $context
    )
    {
        $this->zoneService = $zoneService;
        $this->context = $context;
    }

    public function kitCmsViewMode()
    {
        return $this->context->getViewMode();
    }

    public function kitZoneIsEmpty($slug)
    {
        return $this->zoneService->isEmpty($slug, $this->context->getViewMode());
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('kit_cms_zone_is_empty', array($this, 'kitZoneIsEmpty')),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'kit_cms';
    }
}
