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
use Kitpages\FileBundle\Entity\FileInterface;

class CmsFileManager {

    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $fileManager = null;
    protected $itemClassBlock = null;
    
    public function __construct(Registry $doctrine, EventDispatcher $dispatcher, FileManager $fileManager) {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->fileManager = $fileManager;
        $this->itemClassBlock = 'KitpagesCmsBundle:Block';
    }

    /**
     * @return $fileManager
     */
    public function getFileManager() {
        return $this->fileManager;
    }    

    /**
     * @return $itemClassBlock
     */
    public function getItemClassBlock() {
        return $this->itemClassBlock;
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



    public function mediaListInBlockData($blockData, $publish) {
        $em = $this->getDoctrine()->getEntityManager();
        $listMediaUrl = array('urlList' => array(), 'media' => array());
        if (isset($blockData['root']) && count($blockData['root'])>0 ) {
            foreach($blockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                        if ($file != null && $file instanceof FileInterface) {
                            $mediaInfo = array();

                            $url = $this->mediaUrl($file, $publish);
                            $mediaInfo['default'] = $this->mediaInfo($file, $url);

                            if(method_exists($file,'getParent')){
                                    $fileOriginal = $file->getParent();
                                    if ($fileOriginal instanceof FileInterface) {
                                        $urlOriginal = $this->mediaUrl($fileOriginal, $publish);
                                        $mediaInfo['original'] = $this->mediaInfo(
                                            $fileOriginal,
                                            $urlOriginal,
                                            $file->getPublishParent()
                                        );
                                    } else {
                                        $mediaInfo['original'] = $mediaInfo['default'];
                                    }
                            }

                            $listMediaUrl['media'][substr($field, '6')][$indexMedia] = $mediaInfo;
                        }
                    }
                }
            }
        }
        return $listMediaUrl;
    }


    public function mediaUrl($file, $publish){
        $fileManager = $this->getFileManager();
        if ($publish) {
            $url = $fileManager->getFilePublicLocation($file)."/".$file->getFileName();
        } else {
            $url = $fileManager->getFileLocation($file->getId());
        }
        return $url;
    }

    public function mediaInfo($file, $url, $isPublished = true) {
        $fileInfo = array(
            'id' => $file->getId(),
            'type' => '',
            'mime' => '',
            'url' => $url,
            'html' => '',
            'isPublished' => $isPublished,
            'absolutePath' => $this->getFileManager()->getFilePublicAbsolute($file),
            'info' => array()
        );

        if(method_exists($file,'getHtml')){
            $fileInfo['html'] = $file->getHtml();
        }
        if(method_exists($file,'getType')){
            $fileInfo['type'] = $file->getType();
        }
        if(method_exists($file,'getMimeType')){
            $fileInfo['mime'] = $file->getMimeType();
        }
        $fileData = $file->getData();
        if(isset($fileData['info'])){
            $fileInfo['info'] = $fileData['info'];
        }
        return $fileInfo;
    }

    public function fileValidate($file, $id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $file->setStatus(FileInterface::STATUS_VALID);
        $file->setItemClass($this->itemClassBlock);
        $file->setItemId($id);
        $em->persist($file);
        $em->flush();
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
                            $mediaIdList[$idMedia] = 1;
                            $this->fileValidate($file, $block->getId());

                            $fileParent = $file->getParent();
                            if($fileParent instanceof FileInterface) {
                                $mediaIdList[$fileParent->getId()] = 1;
                                $this->fileValidate($fileParent, $block->getId());
                            }
                        }
                    }
                }
            }
        }
        //delete old file with status = Valid
        $oldBlockData = $event->getData('oldBlockData');
        if (isset($oldBlockData['root']) && count($oldBlockData['root'])>0 ) {
            foreach($oldBlockData['root'] as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        if (!isset($mediaIdList[$idMedia])) {
                            $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                            if ($file != null) {
                                $fileParent = $file->getParent();
                                if($fileParent instanceof FileInterface) {
                                    if (!isset($mediaIdList[$fileParent->getId()])) {
                                        $this->fileManager->delete($fileParent);
                                    }
                                }
                                $this->fileManager->delete($file);
                            }
                        }
                    }
                }
            }
        }
        $this->fileManager->deleteTemp($this->itemClassBlock, $block->getId());


    }

    public function valueMedia($value)
    {
        if (!is_array($value)) {
            if ($value == null)
            {
                return array();
            }
            return array($value);
        }  else {
            return $value;
        }
    }

}