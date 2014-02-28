<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class EditBlockHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine, $blockManager)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
        $this->blockManager = $blockManager;
    }

    public function process($form, $formData, Block $block)
    {
        $oldBlockData = $block->getData();
        $form->handleRequest($this->request);
        $blockData = $block->getData();

        $fieldData = $form->get('data');

        $reflector = new \ReflectionObject($formData);
        if ($reflector->hasMethod('filterList')) {
            foreach($formData->filterList() as $field => $method) {
                if (isset($blockData['root'][$field])) {
                    $blockData['root'][$field] = $this->blockManager->$method($blockData['root'][$field]);
                }
            }
        }
        $block->setData($blockData);
        if ($form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->flush();

            $this->blockManager->afterModify($block, $oldBlockData);

            return array('result' => true, 'msg' => 'Block modified');
        }

        return false;
    }
}

