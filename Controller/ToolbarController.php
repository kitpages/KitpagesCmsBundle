<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Entity\Site;

class ToolbarController extends Controller
{
 
    public function widgetToolbarAction() {
        $context = $this->get('kitpages.cms.controller.context');
        $em = $this->getDoctrine()->getEntityManager();
        $dataRender = array(
            'isNavPublished' => $em->getRepository('KitpagesCmsBundle:Site')->get(Site::IS_NAV_PUBLISHED),
            'viewMode' => $context->getViewMode(),
            'target' => $_SERVER["REQUEST_URI"]
        );
        return $this->render('KitpagesCmsBundle:Toolbar:toolbar.html.twig', $dataRender);
    }    

    public function viewModeChangeAction($viewMode) {
        $context = $this->get('kitpages.cms.controller.context');
        $context->setViewMode($viewMode);
        $request = Request::createFromGlobals();
        $parseUrl = parse_url($request->query->get('kitpages_target'));
        return new RedirectResponse($request->query->get('kitpages_target'));
    }    
    
}