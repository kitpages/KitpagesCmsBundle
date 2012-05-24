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
            '/\[\[cms\:media\:([a-zA-Z0-9_\.]+)\]\]/',
            $output,
            $matches
        );

        $mediaFieldStringList = $matches[1];
        foreach ($mediaFieldStringList as $mediaFieldString) {
            $mediaFieldList = explode('.', $mediaFieldString);
            $fieldName = array_shift($mediaFieldList);
            $fieldIndex = array_shift($mediaFieldList);
            $info = '';
            if (isset($data['media']) && isset($data['media'][$fieldName]) && isset($data['media'][$fieldName][$fieldIndex]) ) {

                $info = $data['media'][$fieldName][$fieldIndex];
                foreach($mediaFieldList as $mediaField) {
                    if(isset($info[$mediaField])) {
                        $info = $info[$mediaField];
                    }else{
                        $info = '';
                        break 1;
                    }
                }
            }
            if (!is_string($info)) {
                $info = '';
            }
            $output = preg_replace(
                '/\[\[cms\:media\:'.$mediaFieldString.'\]\]/',
                $info,
                $output
            );
        }
        return $output;
    }

}

?>
