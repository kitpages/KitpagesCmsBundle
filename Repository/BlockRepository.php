<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class BlockRepository extends EntityRepository
{

    public function queryFindByZone($zone, $order = 'asc', $limit = null)
    {   
//        $sqlLimit = '';
//        if($limit != null) {
//            $sqlLimit = " LIMIT 0, ".$limit;
//        }
        $query = $this->_em
            ->createQuery('SELECT b FROM KitpagesCmsBundle:Block b JOIN b.zoneBlockList zb WHERE zb.zone = :zone ORDER BY zb.position '.$order)
            ->setParameter("zone", $zone)
            ->setMaxResults($limit);        
        return $query;
    }
    
    public function findByZone($zone, $order = 'asc', $limit = null)
    {   

//        $listBlock = $this->_em
//            ->createQuery('SELECT b FROM KitpagesCmsBundle:Block b JOIN b.zoneBlockList zb WHERE zb.zone = :zone ORDER BY zb.position')
//            ->setParameter("zone", $zone)
//            ->getResult(); 
        $listBlock = $this->queryFindByZone($zone, $order, $limit)->getResult();
        return $listBlock;
    }
    
}
