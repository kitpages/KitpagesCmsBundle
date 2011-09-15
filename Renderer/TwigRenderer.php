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
        $output = $this->twig->render(
            $this->getTemplateName(),
            array(
                'data' => $data,
                'kitCmsViewMode' => $viewMode
            )
        );
        $output = $this->parse($output, $data);
        return $output;
    }

    public function parse($output, $data)
    {
        $tag = preg_match_all(
            '/\[\[cms\:mediaField\:(\w+)\]\]/',
            $output,
            $matches
        );

        $mediaFieldList = $matches[1];
        foreach ($mediaFieldList as $mediaFieldName) {
            $fieldName = 'url_media_'.$mediaFieldName;
            if (isset($data['root']) && isset($data['root']) && isset($data['root'][$fieldName]) ) {
                $replacement = $data['root'][$fieldName];
                $output = preg_replace(
                    '/\[\[cms\:mediaField\:'.$mediaFieldName.'\]\]/',
                    $replacement,
                    $output
                );
            }
        }
        return $output;
    }

}

?>
