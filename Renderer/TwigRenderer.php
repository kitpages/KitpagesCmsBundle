<?php
namespace Kitpages\CmsBundle\Renderer;

use Kitpages\CmsBundle\Controller\Context;

class TwigRenderer {
    
    protected $twig = null;
    protected $templateName = null;
    public function getTwig()
    {
        return $this->twig;
    }
    
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }
    
    public function setTemplateName($name)
    {
        $this->templateName = $name;
    }
    public function getTemplateName()
    {
        return $this->templateName;
    }


    public function render($data, $viewMode = Context::VIEW_MODE_PROD)
    {
        return $this->twig->render(
            $this->getTemplateName(),
            array('data' => $data)
        );
    }
    
    
}

?>
