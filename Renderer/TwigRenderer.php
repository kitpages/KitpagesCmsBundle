<?php
namespace Kitpages\CmsBundle\Renderer;

use Kitpages\CmsBundle\Controller\Context;

class TwigRenderer {
    
    public function getTwig()
    {
        return $this->_twig;
    }
    
    public function setTwig($twig)
    {
        $this->_twig = $twig;
    }
    
    public function render($data, $viewMode = Context::VIEW_MODE_PROD)
    {
        
    }
    
    
}

?>
