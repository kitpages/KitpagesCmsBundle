<?php
namespace Kitpages\CmsBundle\Renderer;

use Kitpages\CmsBundle\Controller\Context;

interface RendererInterface {
    /**
     * @param array $data : data used to generate HTML
     * @return string html rendered or any format according to the renderer
     */
    public function render($data, $viewMode = Context::VIEW_MODE_PROD);
}

?>
