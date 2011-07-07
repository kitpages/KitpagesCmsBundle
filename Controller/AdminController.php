<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Kitpages\CmsBundle\Controller\Context;

class AdminController extends Controller
{
 
    public function widgetToolbarAction() {
        $context = $this->get('kitpages.cms.controller.context');
        $dataRender = array(
            'viewMode' => $context->getViewMode(),
            'target' => $_SERVER["REQUEST_URI"]
        );
        return $this->render('KitpagesCmsBundle:Admin:toolbar.html.twig', $dataRender);
    }    

    public function viewModeChangeAction($viewMode) {
        $context = $this->get('kitpages.cms.controller.context');
        $context->setViewMode($viewMode);
        $request = Request::createFromGlobals();
        return new RedirectResponse($request->query->get('kitpages_target'));
    }    
    
}