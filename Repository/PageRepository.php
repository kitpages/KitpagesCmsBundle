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

    public function findByForcedUrl($url)
    {
        $query = $this->_em->createQuery("
                SELECT p
                FROM KitpagesCmsBundle:Page p
                WHERE p.forcedUrl = :forcedUrl
            ")
            ->setParameter("forcedUrl", $url);
        $pageList = $query->getResult();
        $cnt = count($pageList);
        if ($cnt === 1) {
            return $pageList[0];
        }
        if ($cnt === 0) {
            return null;
        }
        throw new Exception("Two pages have the same forced URL");
     }

    public function childrenOfDepth(Page $page, $depth)
    {
        $listPage = $this->_em
            ->createQuery("
                SELECT p
                FROM KitpagesCmsBundle:Page p
                WHERE p.right < :right
                  AND p.left > :left
                  AND p.level = :level
                  AND p.isInNavigation = :isInNavigation
                ORDER BY p.left
                ")
            ->setParameter("level", $page->getLevel()+$depth)
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->setParameter("isInNavigation", 1)
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
            ->createQuery("
                SELECT p
                FROM KitpagesCmsBundle:Page p
                WHERE p.right > :right
                  AND p.left < :left
                  AND p.level >= :levelMin
                  AND p.level <= :levelMax
                  AND p.isInNavigation = :isInNavigation
                ORDER BY p.left
              ")
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->setParameter("levelMin", $startLevel)
            ->setParameter("levelMax", $endLevel)
            ->setParameter("isInNavigation", 1)
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
                  AND p.isInNavigation = :isInNavigation
              ")
            ->setParameter("level", $pageParent->getLevel()+$depth)
            ->setParameter("rightChild", $pageChild->getRight())
            ->setParameter("leftChild", $pageChild->getLeft())
            ->setParameter("rightParent", $pageParent->getRight())
            ->setParameter("leftParent", $pageParent->getLeft())
            ->setParameter("isInNavigation", 1)
            ->getResult();
        if (count($listPage) == 1) {
            return $listPage[0];
        } else {
            return null;
        }
    }


    public function parentDataInheritance(Page $page, $fieldInheritanceList)
    {

        $dataList = $this->_em
            ->createQuery('
                SELECT p.data as data FROM KitpagesCmsBundle:Page p
                WHERE p.right > :right
                    AND p.left < :left
                ORDER BY p.level DESC
            ')
            ->setParameter("right", $page->getRight())
            ->setParameter("left", $page->getLeft())
            ->getResult();

        $dataReturn = array();
        $fieldInheritanceList = array_flip($fieldInheritanceList);
        foreach($dataList as $data) {
            $dataFieldList = unserialize($data['data']);
            if ($dataFieldList != null && $dataFieldList['root'] != null) {
                foreach($dataFieldList['root'] as $keyDataField => $dataField) {
                    if ($dataField == null) {
                        unset($dataFieldList['root'][$keyDataField]);
                    }
                }
                $dataReturnTmp = array_intersect_key($dataFieldList['root'], $fieldInheritanceList);
                $dataReturn = array_merge( $dataReturnTmp, $dataReturn);
            }
        }
        return $dataReturn;
    }

    public function getDataWithInheritance(Page $page, $fieldInheritanceList)
    {
        $pageData = $page->getData();
        $dataReturn = $this->parentDataInheritance($page, $fieldInheritanceList);
        if($pageData != null && $pageData['root'] != null) {
            foreach($pageData['root'] as $keyDataField => $dataField) {
                if ($dataField == null) {
                    unset($pageData['root'][$keyDataField]);
                }
            }
            $dataReturn = array_merge($dataReturn, $pageData['root']);
        }
        return $dataReturn;
    }

}
