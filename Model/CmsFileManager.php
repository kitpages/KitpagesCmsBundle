<?php

namespace Kitpages\CmsBundle\Model;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Bundle\DoctrineBundle\Registry;
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
    
    public function __construct(Registry $doctrine, EventDispatcherInterface $dispatcher, FileManager $fileManager) {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->fileManager = $fileManager;
        $this->itemClassBlock = 'KitpagesCmsBundle:Block';
        $this->itemClassPage = 'KitpagesCmsBundle:Page';
    }

    /**
     * @return $fileManager
     */
    public function getFileManager() {
        return $this->fileManager;
    }    

    /**
     * @return $itemClassPage
     */
    public function getItemClassPage() {
        return $this->itemClassPage;
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

    public function publishDataMediaList($data) {
        $fileManager = $this->getFileManager();
        $em = $this->getDoctrine()->getManager();
        if (count($data)>0 ) {
            foreach($data as $field => $value) {
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

    public function delete($data) {
        $fileManager = $this->getFileManager();        
        $em = $this->getDoctrine()->getManager();
        if (count($data)>0 ) {
            foreach($data as $field => $value) {
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

    public function unpublishFileList($mediaList)
    {
        $em = $this->getDoctrine()->getManager();
        $repositoryFileBundle = $em->getRepository('KitpagesFileBundle:File');
        $fileManager = $this->getFileManager();
        foreach($mediaList as $media) {
            foreach($media as $mediaVersionList) {
                foreach($mediaVersionList as $mediaVersion) {
                    $file = $repositoryFileBundle->find($mediaVersion['id']);
                    // delete file published only if file has been deleted in the database
                    if (!($file instanceof FileInterface)) {
                        $fileManager->unpublish($mediaVersion['filePath'], false);
                        if(isset($mediaVersion['fileList']) && count($mediaVersion['fileList'])>0){
                            foreach($mediaVersion['fileList'] as $fileListInfo) {
                                $fileManager->unpublish($fileListInfo['filePath'], false);
                            }
                        }
                    }
                }
            }
        }
    }

    public function mediaList($data, $publish) {
        $em = $this->getDoctrine()->getManager();
        $listMedia = array();
        if (count($data)>0 ) {
            foreach($data as $field => $value) {
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

                            $listMedia[substr($field, '6')][$indexMedia] = $mediaInfo;
                        }
                    }
                }
            }
        }
        return $listMedia;
    }


    public function mediaUrl($file, $publish){
        $fileManager = $this->getFileManager();
        if ($publish) {
            $url = $fileManager->getFileLocationPublic($file, false);
        } else {
            $url = $fileManager->getFileLocationPrivate($file->getId());
        }
        return $url;
    }

    public function mediaInfo($file, $url, $isPublished = true) {
        $private = !$isPublished;
        $fileInfo = array(
            'id' => $file->getId(),
            'type' => '',
            'mime' => '',
            'url' => $url,
            'fileName' => $file->getFileName(),
            'isPublished' => $isPublished,
            'filePath' => $this->getFileManager()->getFilePath($file, $private),
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

    public function validateFile($file, $itemClass, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $file->setStatus(FileInterface::STATUS_VALID);
        $file->setItemClass($itemClass);
        $file->setItemId($id);
        $em->persist($file);
        $em->flush();
    }

    public function validateFileMediaList($data, $itemClass, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $mediaIdList = array();
        if (count($data)>0 ) {
            foreach($data as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                        if ($file != null) {
                            $mediaIdList[$idMedia] = 1;
                            $this->validateFile($file, $itemClass, $id);
                            $fileParent = $file->getParent();
                            if($fileParent instanceof FileInterface) {
                                $mediaIdList[$fileParent->getId()] = 1;
                                $this->validateFile($fileParent, $itemClass, $id);
                            }
                        }
                    }
                }
            }
        }
        return $mediaIdList;
    }
    public function deleteFileMediaList($data, $idNoDeleteList)
    {
        $em = $this->getDoctrine()->getManager();
        if (count($data)>0 ) {
            foreach($data as $field => $value) {
                if (substr($field, '0', '6') == 'media_') {
                    foreach($this->valueMedia($value) as $indexMedia => $idMedia) {
                        if (!isset($idNoDeleteList[$idMedia])) {
                            $file = $em->getRepository('KitpagesFileBundle:File')->find($idMedia);
                            if ($file != null) {
                                $fileParent = $file->getParent();
                                if($fileParent instanceof FileInterface) {
                                    if (!isset($idNoDeleteList[$fileParent->getId()])) {
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
    }

    public function afterBlockModify(Event $event)
    {
        $block = $event->getBlock();
        $blockData = $block->getData();
        $oldBlockData = $event->getData('oldBlockData');
        $entityId = $block->getId();
        $mediaIdList = array();
        if (isset($blockData['root'])){
            $mediaIdList = $this->validateFileMediaList($blockData['root'], $this->itemClassBlock, $entityId);
        }

        //delete old file with status = Valid
        if (isset($oldBlockData['root'])){
            $this->deleteFileMediaList($oldBlockData['root'], $mediaIdList);
        }
        $this->fileManager->deleteTemp($this->itemClassBlock, $entityId);
    }

    public function afterPageModify(Event $event)
    {
        $page = $event->getPage();
        $pageData = $page->getData();
        $oldPageData = $event->getData('oldPageData');
        $entityId = $page->getId();
        $mediaIdList = array();
        if (isset($pageData['root'])){
            $mediaIdList = $this->validateFileMediaList($pageData['root'], $this->itemClassPage, $entityId);
        }

        //delete old file with status = Valid
        if (isset($oldPageData['root'])){
            $this->deleteFileMediaList($oldPageData['root'], $mediaIdList);
        }
        $this->fileManager->deleteTemp($this->itemClassPage, $entityId);
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