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
        $em = $this->getDoctrine()->getManager();
        $countBlock = 0;
        if ($viewMode == Context::VIEW_MODE_EDIT || $viewMode == Context::VIEW_MODE_PREVIEW) {
            $zone = $em->getRepository('KitpagesCmsBundle:Zone')->findOneBy(array('slug' => $slug));
            if ($zone == null) {
                return true;
            }

            $countBlock = $em
                ->getRepository('KitpagesCmsBundle:Block')
                ->getBlockCountByZone($zone);
        } elseif ($viewMode == Context::VIEW_MODE_PROD) {
            $zonePublish = $em->getRepository('KitpagesCmsBundle:ZonePublish')->findOneBy(array('slug' => $slug));
            if ($zonePublish == null) {
                return true;
            }
            $data = $zonePublish->getData();

            $countBlock = count($data['blockPublishList']);
        }
        if($countBlock > 0) {
            return false;
        }

    }
}
