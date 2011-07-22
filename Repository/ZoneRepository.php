<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class ZoneRepository extends EntityRepository
{
    public function findByBlock($block) {
        
        $listZone = $this->_em
            ->createQuery('SELECT z FROM KitpagesCmsBundle:Zone z JOIN z.zoneBlockList zb WHERE zb.block = :block')
            ->setParameter("block", $block)
            ->getResult();        
        return $listZone;        
        
    }
    public function findByPageAndLocation($page, $location) {
        
        $zone = $this->_em
            ->createQuery('SELECT z FROM KitpagesCmsBundle:Zone z JOIN z.pageZoneList pz WHERE pz.page = :page AND pz.locationInPage = :location')
            ->setParameter("page", $page)
            ->setParameter("location", $location)
            ->getResult();   
        if (count($zone) > 0) {
            return $zone[0];        
        } else {
            return null;
        }
    }
    public function findByPage($page)
    {   

        $listZone = $this->_em
            ->createQuery('SELECT z FROM KitpagesCmsBundle:Zone z JOIN z.pageZoneList pz WHERE pz.page = :page')
            ->setParameter("page", $page)
            ->getResult();        
        return $listZone;
    }

     
}
