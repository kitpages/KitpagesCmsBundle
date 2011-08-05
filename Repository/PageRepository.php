<?php
namespace Kitpages\CmsBundle\Repository;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Kitpages\CmsBundle\Entity\Page;

class PageRepository extends NestedTreeRepository
{
   
    public function findByZone($zone)
    {   

        $listPage = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p JOIN p.pageZoneList pz WHERE pz.zone = :zone')
            ->setParameter("zone", $zone)
            ->getResult();        
        return $listPage;
     }

    public function childrenOfDepth(Page $page, $depth)
    {   
        $listPage = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right < :right AND p.left > :left AND p.level = :level')
            ->setParameter("level", $page->getLevel()+$depth)
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->getResult();        
        return $listPage;
     }
     
    public function parent(Page $page)
    {   
        $listPage = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right > :right AND p.left < :left')
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->getResult();        
        return $listPage;
    }
    public function parentBetweenTwoDepth(Page $page, $startLevel, $endLevel)
    {   
        $listPage = $this->_em
            ->createQuery('SELECT p FROM KitpagesCmsBundle:Page p WHERE p.right > :right AND p.left < :left AND p.level >= :levelMin AND p.level <= :levelMax')
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->setParameter("levelMin", $startLevel)
            ->setParameter("levelMax", $endLevel)
            ->getResult();        
        return $listPage;
    }   
    
    public function childOfPageWithForParentOtherPage(Page $pageParent, Page $pageChild, $depth)
    {   
        $listPage = $this->_em
            ->createQuery("
                SELECT p 
                FROM KitpagesCmsBundle:Page p 
                WHERE p.right >= :rightChild 
                  AND p.left <= :leftChild 
                  AND p.right <= :rightParent 
                  AND p.left >= :leftParent 
                  AND p.level = :level
              ")
            ->setParameter("level", $pageParent->getLevel()+$depth)
            ->setParameter("rightChild", $pageChild->getRight())
            ->setParameter("leftChild", $pageChild->getLeft())
            ->setParameter("rightParent", $pageParent->getRight())
            ->setParameter("leftParent", $pageParent->getLeft())                    
            ->getResult(); 
        if (count($listPage) == 1) {
            return $listPage[0];
        } else {
            return null;
        }        
    }   
    
     
}
