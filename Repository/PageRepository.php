<?php
namespace Kitpages\CmsBundle\Repository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Kitpages\CmsBundle\Entity\Page;

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

    public function childrenOfDepth(Page $page, $depth)
    {   
        $listZone = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right < :right AND p.left > :left AND p.level = :level')
            ->setParameter("level", $page->getLevel()+$depth)
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->getResult();        
        return $listZone;
     }
     
    public function parent(Page $page)
    {   
        $listZone = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right > :right AND p.left < :left')
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->getResult();        
        return $listZone;
    }
    public function parentBetweenTwoDepth(Page $page, $startLevel, $endLevel)
    {   
        $listZone = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right > :right AND p.left < :left AND p.level >= :levelMin AND p.level <= :levelMax')
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->setParameter("levelMin", $startLevel)
            ->setParameter("levelMax", $endLevel)
            ->getResult();        
        return $listZone;
    }    
    
     
}
