<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class BlockRepository extends EntityRepository
{

    public function queryFindByZone($zone, $order = 'asc', $limit = null, $offset = null)
    {
//        $sqlLimit = '';
//        if($limit != null) {
//            $sqlLimit = " LIMIT 0, ".$limit;
//        }
        $query = $this->_em
            ->createQuery("
                SELECT b
                FROM KitpagesCmsBundle:Block b
                JOIN b.zoneBlockList zb
                WHERE zb.zone = :zone
                ORDER BY zb.position ".$order)
            ->setParameter("zone", $zone);
        if ($limit !== null) {
            $query->setMaxResults($limit);
        }
        if ($offset != null) {
            $query->setFirstResult($offset);
        }
        return $query;
    }

    public function getBlockCountByZone($zone)
    {
        $query = $this->_em
            ->createQuery("
                SELECT count(b)
                FROM KitpagesCmsBundle:Block b
                JOIN b.zoneBlockList zb
                WHERE zb.zone = :zone
            ")
            ->setParameter("zone", $zone);
        return $query->getSingleScalarResult();
    }

    public function findByZone($zone, $order = 'asc', $limit = null, $offset = null)
    {
        $listBlock = $this->queryFindByZone($zone, $order, $limit, $offset)->getResult();
        return $listBlock;
    }


}
