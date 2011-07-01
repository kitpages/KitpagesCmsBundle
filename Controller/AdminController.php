<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends Controller
{

    public function widgetToolbarAction() {

        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $dataRender = array(
            'viewMode' => $cmsManager->getViewMode(),
            'target' => $_SERVER["REQUEST_URI"]
        );
        return $this->render('KitpagesCmsBundle:Admin:toolbar.html.twig', $dataRender);
    }    

    public function viewModeChangeAction($viewMode) {

        $cmsManager = $this->get('kitpages.cms.model.cmsManager');
        $cmsManager->setViewMode($viewMode);
        $request = Request::createFromGlobals();
        return new RedirectResponse($request->query->get('kitpages_target'));
    }    
    
}