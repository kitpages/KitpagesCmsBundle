<?php
namespace Kitpages\CmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Kitpages\CmsBundle\Entity\Site;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PageZone;
use Kitpages\CmsBundle\Entity\Zone;

class LoadSiteData implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }    
    public function load($em)
    {
        $em->getRepository('KitpagesCmsBundle:Site')->set(Site::IS_NAV_PUBLISHED, 0);
        
        //page root
        $pageRoot = new Page();
        $pageRoot->setSlug('site');
        $pageRoot->setTitle('null');        
        $pageRoot->setIsInNavigation(true);
        $pageRoot->setLanguage('en'); 
        $pageRoot->setPageType('technical');   
        $em->persist($pageRoot);
        $em->flush();
        
        //page language
        $pageLngEn = new Page();
        $pageLngEn->setSlug('en');
        $pageLngEn->setIsInNavigation(true);
        $pageLngEn->setLanguage('en'); 
        $pageLngEn->setParent($pageRoot);
        $pageLngEn->setPageType('technical');   
        $em->persist($pageLngEn);
        $em->flush();
        
        //pages navigation
        $pageNavMainEn = new Page();
        $pageNavMainEn->setSlug('main');
        $pageNavMainEn->setIsInNavigation(true);
        $pageNavMainEn->setLanguage('en'); 
        $pageNavMainEn->setParent($pageLngEn);
        $pageNavMainEn->setPageType('technical');   
        $em->persist($pageNavMainEn);
        $em->flush();
        
        $pageNavFooterEn = new Page();
        $pageNavFooterEn->setSlug('footer');
        $pageNavFooterEn->setIsInNavigation(true);
        $pageNavFooterEn->setLanguage('en'); 
        $pageNavFooterEn->setParent($pageLngEn);
        $pageNavFooterEn->setPageType('technical');   
        $em->persist($pageNavFooterEn);
        $em->flush();
        
        //page home
        $pageHomeEn = new Page();
        $pageHomeEn->setSlug('home');
        $pageHomeEn->setTitle('home');
        $pageHomeEn->setmenuTitle('home');
        $pageHomeEn->setIsInNavigation(true);
        $pageHomeEn->setLanguage('en'); 
        $pageHomeEn->setParent($pageNavMainEn);
        $pageHomeEn->setPageType('edito');   
        $pageHomeEn->setLayout('default');           
        $em->persist($pageHomeEn);
        $em->flush();        
        
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$pageHomeEn->getLayout());
        $zoneList = $layout['zone_list'];
        //zones home
        foreach($zoneList as $locationInPage => $render) {
            $zone = new Zone();
            $zone->setSlug('');
            $em->persist($zone);
            $em->flush();
            $pageZone = new PageZone();
            $pageZone->setPage($pageHomeEn);
            $pageZone->setZone($zone);
            $pageZone->setLocationInPage($locationInPage);
            $em->persist($pageZone);
            $em->flush();                    
        }
      
    }
}
