<?php

namespace Kitpages\CmsBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\Page;
use Kitpages\CmsBundle\Entity\ZoneBlock;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class EditPageHandler
{
    protected $request;
    protected $form;

    public function __construct(Request $request, Registry $doctrine, $validator, $pageManager)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->pageManager = $pageManager;
    }

    public function process($form, Page $page)
    {
        $oldPage = clone $page;
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $errorList = $this->validator->validate($page);
            if (count($errorList) == 0) {
                $em = $this->doctrine->getManager();
                $dataForm = $form->getData();
                $parent_id = $form->get('parent_id')->getData();
                if (!empty($parent_id)) {
                    $pageParent = $em->getRepository('KitpagesCmsBundle:Page')->find($parent_id);
                    $page->setParent($pageParent);
                }
                $em->flush();
                $this->pageManager->afterModify($page, $oldPage);
                $forcedUrl = $page->getForcedUrl();
                return array('result' => true, 'msg' => 'Page modified', 'forcedUrl' => $forcedUrl);
            } else {
                $msg = 'Page not saved <br />';
                foreach ($errorList as $err) {
                    $msg.= $err->getMessage() . '<br />';
                }
                $forcedUrl = $oldPage->getForcedUrl();
                return array('result' => false, 'msg' => $msg, 'forcedUrl' => $forcedUrl);
            }
        }

        return array('result' => false);
    }
}

