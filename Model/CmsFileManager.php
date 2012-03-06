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
        if (isset($blockData['root']) && count($blockData['root'])>0 ) {
            foreach($blockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $idMedia) {
                        $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                        if ($file != null) {
                            $fileManager->publish($file);
                        }
                    }
                }
            }
        }
    }

    public function deleteInBlockData($blockData) { 
        $fileManager = $this->getFileManager();        
        $em = $this->getDoctrine()->getEntityManager();
        if (isset($blockData['root']) && count($blockData['root'])>0 ) {
            foreach($blockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                        if ($file != null) {
                            $fileManager->delete($file);
                        }
                    }
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
    
    public function urlListInBlockData($blockData, $publish) {
        $fileManager = $this->getFileManager();          
        $em = $this->getDoctrine()->getEntityManager();
        $listMediaUrl = array();
        if (isset($blockData['root']) && count($blockData['root'])>0 ) {
            foreach($blockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    if ($publish) {
                        if (!is_array($value)) {
                            $file = $em->getRepository('KitpagesFileBundle:File')->find($value);
                            if ($file != null) {
                                $listMediaUrl['url_'.$field] = $fileManager->getFilePublicLocation($file)."/".$file->getFileName();
                            }
                        } else {
                            foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                                $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                                if ($file != null) {
                                    $listMediaUrl['url_'.$field][$indexMedia] = $fileManager->getFilePublicLocation($file)."/".$file->getFileName();
                                }
                            }
                        }
                    } else {
                        if (!is_array($value)) {
                            $listMediaUrl['url_'.$field] = $fileManager->getFileLocation($value);
                        } else {
                            foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                                $listMediaUrl['url_'.$field][$indexMedia] = $fileManager->getFileLocation($idMedia);
                            }
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
        $blockData = $block->getData();
        $em = $this->getDoctrine()->getEntityManager();
        if (isset($blockData['root']) && count($blockData['root'])>0 ) {
            foreach($blockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                        if ($file != null) {
                            $file->setStatus(File::STATUS_VALID);
                            $em->persist($file);
                            $em->flush();
                        }
                    }
                }
            }
        }
    }

    public function valueMedia($value)
    {
        if (!is_array($value)) {
            return array($value);
        }  else {
            return $value;
        }
    }

}