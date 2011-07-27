<?php

namespace Kitpages\CmsBundle\Model;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\CmsBundle\KitpagesCmsEvents;
use Kitpages\FileBundle\Entity\File;

class CmsFileManager extends FileManager {

    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    
    public function __construct(Registry $doctrine, EventDispatcher $dispatcher, FileManager $fileManager) {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->fileManager = $fileManager;
    }

    /**
     * @return $fileManager
     */
    public function getFileManager() {
        return $this->fileManager;
    }    
    
    /**
     * @return EventDispatcher $dispatcher
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }  

    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    }
    
    ////
    // action function
    ////
    
    public function publishInBlockData($blockData) {
        $fileManager = $this->getFileManager();
        $em = $this->getDoctrine()->getEntityManager();
        $fieldList = $blockData['root'];
        foreach($fieldList as $field => $value) {
            if (substr($field, '0', '6') == 'media_') {
                $file = $em->getRepository('KitpagesFileBundle:File')->find($value);
                if ($file != null) {
                    $fileManager->publish($file);
                }
            }
        }
    }

    public function deleteInBlockData($blockData) { 
        $fileManager = $this->getFileManager();        
        $em = $this->getDoctrine()->getEntityManager();
        $fieldList = $blockData['root'];
        foreach($fieldList as $field => $value) {
            if (substr($field, '0', '6') == 'media_') {
                $file = $em->getRepository('KitpagesFileBundle:File')->find($value);
                if ($file != null) {
                    $fileManager->delete($file);
                }
            }
        }
    }

    public function deletePublishedInBlockData($mediaList) { 
        $fileManager = $this->getFileManager();        
        foreach($mediaList as $field => $url) {
            $fileManager->unpublish(dirname($url));
        }
    }
    
    public function urlListInBlockData($data, $publish) {
        $fileManager = $this->getFileManager();          
        $em = $this->getDoctrine()->getEntityManager();
        $listMediaUrl = array();
        if (isset($data['root']) && count($data['root'])>0 ) {
            foreach($data['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    $file = $em->getRepository('KitpagesFileBundle:File')->find($value);
                    if ($file != null) {
                        if ($publish) {
                            $listMediaUrl['url_'.$field] = $fileManager->getFilePublicLocation($file)."/".$file->getFileName();
                        } else {
                            $listMediaUrl['url_'.$field] = "/file/render?path=".$fileManager->getOriginalAbsoluteFileName($file);
                        }
                    }
                }
            }
        }
        return $listMediaUrl;
    }
    
    public function afterBlockModify(Event $event)
    {
        $block = $event->getBlock();
        $data = $block->getData();
        $fieldList = $data['root'];
        $em = $this->getDoctrine()->getEntityManager();
        foreach($fieldList as $field => $value) {
            if (substr($field, '0', '6') == 'media_') {
                $file = $em->getRepository('KitpagesFileBundle:File')->find($value);
                if ($file != null) {
                    $file->setStatus(File::STATUS_VALID);
                    $em->persist($file);
                    $em->flush();
                }
            }
        }
    }
    
}