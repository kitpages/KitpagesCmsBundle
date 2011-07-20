<?php

namespace Kitpages\CmsBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Kitpages\CmsBundle\Entity\PagePublish;
/**
 * NavPublishRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NavPublishRepository extends NestedTreeRepository
{
    public function findByNoPagePublish()
    {   

        $listNavPublish = $this->_em
            ->createQuery('SELECT np FROM KitpagesCmsBundle:NavPublish np LEFT JOIN np.page p LEFT JOIN p.pagePublish pb WHERE pb.id is null')
            ->getResult();        
        return $listNavPublish;
     }    
     
}