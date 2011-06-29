<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class BlockPublishRepository extends EntityRepository
{
   
    public function findByZoneId($zoneId)
    {      
        $listBlock = array();
        $query = $this->_em
                         ->createQuery('SELECT zb.block_id FROM KitpagesCmsBundle:ZoneBlock zb WHERE zb.zone_id = :zone_id ORDER BY zb.position')
                         ->setParameter("zone_id", $zoneId)
                         ->getResult();
        foreach($query as $blockId) {
              $listBlock[] = $this->findByBlockId(array('block_id' => $blockId['block_id']));
        }
        return $listBlock;
    }
    
    public function findByBlockId($blockId)
    {      
        $listBlock = array();
//        $query = $this->_em
//                         ->createQuery('SELECT bp FROM KitpagesCmsBundle:BlockPublish bp WHERE bp.block_id = :block_id')
//                         ->setParameter("block_id", $blockId)
//                         ->getResult();
//        return $query;
    }    
}
