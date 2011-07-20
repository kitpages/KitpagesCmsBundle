<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Entity\NavPublish;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\PagePublish;

class NavController extends Controller
{
 
    public function publishAction() {
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('DELETE Kitpages\CmsBundle\Entity\NavPublish np');
        $resultDelete = $query->getResult();
        $query = $em->getConnection()->executeUpdate("INSERT INTO cms_nav_publish (id, parent_id, page_id, lft, rgt, lvl, root, title, slug) SELECT id, parent_id, id, lft, rgt, lvl, root, title, slug FROM cms_page order by lft");
        
        $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->findByNoPagePublish();
        foreach($navPublishList as $navPublish) {
            $em->getRepository('KitpagesCmsBundle:NavPublish')->removeFromTree($navPublish);
        }
        
        $em->flush();
        return $this->render('KitpagesCmsBundle:Block:edit-success.html.twig');
    }  
    
    public function widgetAction($label, $slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $context = $this->get('kitpages.cms.controller.context');
        $resultingHtml = '';
        if ($context->getViewMode() == Context::VIEW_MODE_EDIT) {
            $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($label);
            $navigation = $this->pageChildren($page, $context->getViewMode());
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PREVIEW) {
            $page = $em->getRepository('KitpagesCmsBundle:Page')->findOneBySlug($label);
            $navigation = $this->pageChildren($page, $context->getViewMode());      
        } elseif ($context->getViewMode() == Context::VIEW_MODE_PROD) {
            $navPublish = $em->getRepository('KitpagesCmsBundle:NavPublish')->findOneBySlug($label);
            $navigation = $this->navPublishChildren($navPublish, $slug);
        }
        return $this->render('KitpagesCmsBundle:Nav:navigation.html.twig', array('slugCurrent' => $slug, 'navigation' => $navigation, 'navigationLabel' => $label, 'root' => true));
    }

    public function navPublishChildren($navPublish){
        $em = $this->getDoctrine()->getEntityManager();
        $navPublishList = $em->getRepository('KitpagesCmsBundle:NavPublish')->children($navPublish, true);
        $listNavigationElem = array();
        foreach($navPublishList as $navPublishChild) {
            $pagePublish = $navPublishChild->getPage()->getPagePublish();
            $navigationElem = array(
                'slug' => $navPublishChild->getSlug(),                
                'title' => $navPublishChild->getTitle(),
                'level' => $navPublishChild->getLevel(),   
                'url' => $this->generateUrl(
                        'kitpages_cms_page_view_lng',
                        array(
                            'id' => $navPublishChild->getId(),
                            'lng' => $pagePublish->getLanguage(),
                            'urlTitle' => $pagePublish->getUrlTitle()
                        ))                 
            );
            $navigationElem['children'] = $this->navPublishChildren($navPublishChild);
            $listNavigationElem[] = $navigationElem;
        }
        return $listNavigationElem;
    }

    public function pageChildren($page, $viewMode){
        $em = $this->getDoctrine()->getEntityManager();
        $pageList = $em->getRepository('KitpagesCmsBundle:Page')->children($page, true);
        $listNavigationElem = array();
        foreach($pageList as $pageChild) {
            $addPageChild = true;
            if ($viewMode == Context::VIEW_MODE_PREVIEW) {
                $pagePublish = $pageChild->getPagePublish();
                if (!($pagePublish instanceof PagePublish)) {
                    $addPageChild = false;
                }
            }
            if ($addPageChild) {
                $navigationElem = array(
                    'slug' => $pageChild->getSlug(),                
                    'title' => $pageChild->getTitle(),
                    'level' => $pageChild->getLevel(),   
                    'url' => $this->generateUrl(
                            'kitpages_cms_page_view_lng',
                            array(
                                'id' => $pageChild->getId(),
                                'lng' => $pageChild->getLanguage(),
                                'urlTitle' => $pageChild->getUrlTitle()
                            ))                 
                );
                $navigationElem['children'] = $this->pageChildren($pageChild, $viewMode);
                $listNavigationElem[] = $navigationElem;
            }
        }
        return $listNavigationElem;
    }
    
}