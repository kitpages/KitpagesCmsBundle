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
}
