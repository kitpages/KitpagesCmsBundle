<?php
namespace Kitpages\CmsBundle\Repository;
use Doctrine\ORM\EntityRepository;
use Kitpages\CmsBundle\Entity\ZoneBlock;

class BlockPublishRepository extends EntityRepository
{
   
    public function findByBlockAndRenderer($block, $renderer)
    {      
        $blockPublish = $this->_em
                         ->createQuery('SELECT bp FROM KitpagesCmsBundle:BlockPublish bp WHERE bp.block = :block AND bp.renderer = :renderer')
                         ->setParameter("block", $block)
                         ->setParameter("renderer", $renderer)
                         ->getResult();
        if (count($blockPublish) == 1) {
            return $blockPublish[0];
        } else {
            return null;
        }
    }
  
}
