<?php
namespace Kitpages\CmsBundle\Repository;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

class ZoneBlockRepository extends SortableRepository
{
    
    public function findByZoneAndBlock($zone, $block) {
        $zoneBlock = $this->_em->createQuery('SELECT zb FROM KitpagesCmsBundle:ZoneBlock zb WHERE :zone = zb.zone AND :block = zb.block')
            ->setParameter('zone', $zone)
            ->setParameter('block', $block)
            ->getResult(); 
        return $zoneBlock[0];
    }
    
    public function nbrZoneBlockByBlockWithZoneDiff($block, $zone)
    {   

        $nbr = $this->_em
            ->createQuery('SELECT count(zb.id) FROM KitpagesCmsBundle:ZoneBlock zb JOIN zb.block b JOIN zb.zone z WHERE zb.block = :block AND zb.zone != :zone')
            ->setParameter("block", $block)
            ->setParameter("zone", $zone)                
            ->getResult();        
        return $nbr;
    }
    
}
