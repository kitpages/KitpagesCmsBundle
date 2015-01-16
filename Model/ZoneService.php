<?php
namespace Kitpages\CmsBundle\Model;

use Doctrine\ORM\EntityManager;
use Kitpages\CmsBundle\Controller\Context;

class ZoneService
{
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $blockManager = null;
    protected $logger = null;

    public function __construct(
        EntityManager $em
    )
    {
        $this->em = $em;
    }

    ////
    // test
    ////
    public function isEmpty($slug, $viewMode)
    {
        $countBlock = $this->countBlock($slug, $viewMode);
        if($countBlock > 0) {
            return false;
        } else {
            return true;
        }

    }

    public function countBlock($slug, $viewMode)
    {
        $countBlock = 0;
        if ($viewMode == Context::VIEW_MODE_EDIT || $viewMode == Context::VIEW_MODE_PREVIEW) {
            $zone = $this->em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $slug));
            if ($zone != null) {
                $countBlock = $this->em
                    ->getRepository('KitpagesCmsBundle:Block')
                    ->getBlockCountByZone($zone);
            }
        } elseif ($viewMode == Context::VIEW_MODE_PROD) {
            $zonePublish = $this->em->getRepository('KitpagesCmsBundle:ZonePublish')->findOneBy(array('slug' => $slug));
            if ($zonePublish != null) {
                $data = $zonePublish->getData();
                if (is_array($data) && $data != null ) {
                    // retrieve first template
                    $blockList = array_shift($data);
                    if (is_array($blockList)) {
                        $countBlock = count($blockList);
                    }
                }
            }
        }
        return $countBlock;
    }

}
