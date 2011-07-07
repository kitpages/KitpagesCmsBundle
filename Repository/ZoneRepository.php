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
}
