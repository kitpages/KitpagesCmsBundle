<?php
namespace Kitpages\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class FileController extends Controller
{
    public function htmlWidgetAction(
        $fileInfo,
        $parameterList = array(),
        $twigTemplate = 'KitpagesCmsBundle:File:htmlWidget.html.twig'
    ){
        return $this->render($twigTemplate, array(
            'fileInfo' => $fileInfo,
            'parameterList' => $parameterList
        ));
    }
}