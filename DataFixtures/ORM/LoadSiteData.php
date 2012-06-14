<?php
namespace Kitpages\CmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Kitpages\CmsBundle\Entity\Site;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PageZone;
use Kitpages\CmsBundle\Entity\Zone;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSiteData implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }    
    public function load(ObjectManager $em)
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
        $pageLangEn = new Page();
        $pageLangEn->setSlug('en');
        $pageLangEn->setIsInNavigation(true);
        $pageLangEn->setLanguage('en'); 
        $pageLangEn->setParent($pageRoot);
        $pageLangEn->setPageType('technical');   
        $em->persist($pageLangEn);
        $em->flush();
        
        //pages navigation
        $pageNavMainEn = new Page();
        $pageNavMainEn->setSlug('en_main');
        $pageNavMainEn->setIsInNavigation(true);
        $pageNavMainEn->setLanguage('en'); 
        $pageNavMainEn->setParent($pageLangEn);
        $pageNavMainEn->setPageType('technical');   
        $em->persist($pageNavMainEn);
        $em->flush();
        //pages navigation
        $pageNavFooterEn = new Page();
        $pageNavFooterEn->setSlug('en_footer');
        $pageNavFooterEn->setIsInNavigation(true);
        $pageNavFooterEn->setLanguage('en'); 
        $pageNavFooterEn->setParent($pageLangEn);
        $pageNavFooterEn->setPageType('technical');   
        $em->persist($pageNavFooterEn);
        $em->flush();
                
        //page home
        $pageHomeEn = new Page();
        $pageHomeEn->setSlug('en_home');
        $pageHomeEn->setTitle('home en');
        $pageHomeEn->setMenuTitle('home');
        $pageHomeEn->setIsInNavigation(true);
        $pageHomeEn->setLanguage('en'); 
        $pageHomeEn->setParent($pageNavMainEn);
        $pageHomeEn->setPageType('edito');   
        $pageHomeEn->setLayout('default');           
        $em->persist($pageHomeEn);
        $em->flush();        
        
        //page language
        $pageLangFr = new Page();
        $pageLangFr->setSlug('fr');
        $pageLangFr->setIsInNavigation(true);
        $pageLangFr->setLanguage('fr'); 
        $pageLangFr->setParent($pageRoot);
        $pageLangFr->setPageType('technical');   
        $em->persist($pageLangFr);
        $em->flush();
        
        //pages navigation
        $pageNavMainFr = new Page();
        $pageNavMainFr->setSlug('fr_main');
        $pageNavMainFr->setIsInNavigation(true);
        $pageNavMainFr->setLanguage('fr'); 
        $pageNavMainFr->setParent($pageLangFr);
        $pageNavMainFr->setPageType('technical');   
        $em->persist($pageNavMainFr);
        $em->flush();
        //pages navigation
        $pageNavFooterFr = new Page();
        $pageNavFooterFr->setSlug('fr_footer');
        $pageNavFooterFr->setIsInNavigation(true);
        $pageNavFooterFr->setLanguage('fr'); 
        $pageNavFooterFr->setParent($pageLangFr);
        $pageNavFooterFr->setPageType('technical');   
        $em->persist($pageNavFooterFr);
        $em->flush();
                
        //page home
        $pageHomeFr = new Page();
        $pageHomeFr->setSlug('fr_home');
        $pageHomeFr->setTitle('home fr');
        $pageHomeFr->setMenuTitle('home');
        $pageHomeFr->setIsInNavigation(true);
        $pageHomeFr->setLanguage('fr'); 
        $pageHomeFr->setParent($pageNavMainFr);
        $pageHomeFr->setPageType('edito');
        $pageHomeFr->setLayout('default');
        $em->persist($pageHomeFr);
        $em->flush();
        
        
        $layout = $this->container->getParameter('kitpages_cms.page.layout_list.'.$pageHomeEn->getLayout());
        $zoneList = $layout['zone_list'];
        //zones home
        foreach($zoneList as $locationInPage => $render) {
            // zones home en
            $zone = new Zone();
            $em->persist($zone);
            $em->flush();
            $pageZone = new PageZone();
            $pageZone->setPage($pageHomeEn);
            $pageZone->setZone($zone);
            $pageZone->setLocationInPage($locationInPage);
            $em->persist($pageZone);
            $em->flush();
            
            // zones home fr
            $zone = new Zone();
            $em->persist($zone);
            $em->flush();
            $pageZone = new PageZone();
            $pageZone->setPage($pageHomeFr);
            $pageZone->setZone($zone);
            $pageZone->setLocationInPage($locationInPage);
            $em->persist($pageZone);
            $em->flush();
        }
      
    }
}
