<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class ZonePublishRepository extends EntityRepository
{
    public function findByZone($zone) {
        
        $listZone = $this->_em
            ->createQuery('SELECT zp FROM KitpagesCmsBundle:ZonePublish zp WHERE zp.zone = :zone')
            ->setParameter("zone", $zone)
            ->getResult();        
        return $listZone;        
        
    }
}
