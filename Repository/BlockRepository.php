<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class BlockRepository extends EntityRepository
{
   
    public function findByZone($zone)
    {   

        $listBlock = $this->_em
            ->createQuery('SELECT b FROM KitpagesCmsBundle:Block b JOIN b.zoneBlockList zb WHERE zb.zone = :zone ORDER BY zb.position')
            ->setParameter("zone", $zone)
            ->getResult();        
        return $listBlock;
     }
    
}
