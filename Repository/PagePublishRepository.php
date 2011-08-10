<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;

class PagePublishRepository extends EntityRepository
{
    public function findByPage($page)
    {   

        $query = $this->_em
            ->createQuery("SELECT pp FROM KitpagesCmsBundle:PagePublish pp WHERE pp.page = :page")
            ->setParameter('page', $page);
        $page = $query->getResult();     
        if (count($page) == 1) {
            return $page[0];
        } else {
            return null;
        }
    }
     
    public function findByForcedUrl($url)
    {
        $query = $this->_em->createQuery("
                SELECT p
                FROM KitpagesCmsBundle:PagePublish p
                WHERE p.forcedUrl = :forcedUrl
            ")
            ->setParameter("forcedUrl", $url);
        $pagePublishList = $query->getResult();
        $cnt = count($pagePublishList);
        if ($cnt === 1) {
            return $pagePublishList[0];
        }
        if ($cnt === 0) {
            return null;
        }
        throw new Exception("Two pagePublish have the same forced URL");
     }

}
