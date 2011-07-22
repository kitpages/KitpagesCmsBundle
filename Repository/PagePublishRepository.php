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
}
