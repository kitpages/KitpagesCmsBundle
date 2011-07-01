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
            $blockPublish = $this->findOneByBlockId($blockId['block_id']);
            if (!is_null($blockPublish)) {
              $listBlock[] = $blockPublish;
            }
        }
        return $listBlock;
    }
  
}
