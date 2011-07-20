<?php
namespace Kitpages\CmsBundle\Repository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;


class PageRepository extends NestedTreeRepository
{
   
    public function findByZone($zone)
    {   

        $listZone = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p JOIN p.pageZoneList pz WHERE pz.zone = :zone')
            ->setParameter("zone", $zone)
            ->getResult();        
        return $listZone;
     }
   
}
