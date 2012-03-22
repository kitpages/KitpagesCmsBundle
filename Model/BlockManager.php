<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Block;
use Kitpages\CmsBundle\Entity\BlockPublish;
use Kitpages\CmsBundle\Event\BlockEvent;
use Kitpages\CmsBundle\KitpagesCmsEvents;

use Kitpages\CmsBundle\Controller\Context;
use Kitpages\CmsBundle\Renderer\TwigRenderer;
use Kitpages\UtilBundle\Service\Util;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Validator\Constraint;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class BlockManager
{

    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $templating = null;
    protected $cmsFileManager = null;
    protected $util = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcher $dispatcher,
        $templating,
        CmsFileManager $cmsFileManager,
        Util $util
    ){
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->templating = $templating;
        $this->cmsFileManager = $cmsFileManager;
        $this->util = $util;
    }

    /**
     * @return EventDispatcher $dispatcher
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }

    /**
     * @return $templating
     */
    public function getTemplating() {
        return $this->templating;
    }

    /**
     * @return Registry $doctrine
     */
    public function getDoctrine() {
        return $this->doctrine;
    }

    /**
     * @return $cmsFileManager
     */
    //TDO: depreceated
    public function getFileManager() {
        return $this->cmsFileManager;
    }

    public function getCmsFileManager() {
        return $this->cmsFileManager;
    }

    /**
     * @return Util
     */
    public function getUtil()
    {
        return $this->util;
    }
    ////
    // action function
    ////
    /**
     *
     * @param Block $block
     */
    public function delete(Block $block)
    {
        // throw on event
        $cmsFileManager = $this->cmsFileManager;
        $event = new BlockEvent($block);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockDelete, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $cmsFileManager->deleteInBlockData($block->getData());
            $em->remove($block);
            $em->flush();
        }
        // throw after event
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockDelete, $event);
    }


    public function deletePublished(BlockPublish $blockPublish)
    {
        $data = $blockPublish->getData();
        $this->unpublishFileList($data['media']);
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($blockPublish);
    }

    public function unpublishFileList($mediaList)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repositoryFileBundle = $em->getRepository('KitpagesFileBundle:File');
        $fileManager = $this->getCmsFileManager()->getFileManager();
        foreach($mediaList as $media) {
            foreach($media as $mediaVersionList) {
                foreach($mediaVersionList as $mediaVersion) {
                    $file = $repositoryFileBundle->find($mediaVersion['id']);

                    if (!($file instanceof FileInterface)) {
                        $fileManager->unpublish($mediaVersion['absolutePath']);
                        if(isset($mediaVersion['fileList']) && count($mediaVersion['fileList'])>0){
                            foreach($mediaVersion['fileList'] as $fileListInfo) {
                                $fileManager->unpublish($mediaVersion['absolutePath']);
                            }
                        }
                    }
                }
            }
        }

    }

    public function deletePendingDelete($file)
    {
        $cmsFileManager = $this->cmsFileManager;
        if($file instanceof FileInterface) {
            $fileStatus = $file->getStatus();
            if ($fileStatus == FileInterface::STATUS_PENDING_DELETE) {
                $cmsFileManager->getFileManager()->delete($file);
            }
        }
    }

    /**
     *
     * @param type $templateTwig
     * @param array $blockData
     * @param boolean $viewMode
     * @param array|null $listMediaUrl
     * @return type
     */
    public function render($renderer, $block, $viewMode = Context::VIEW_MODE_PROD, $listMediaUrl = null) {
        $blockData = $block->getData();
        $blockData['block']['slug'] = $block->getSlug();
        $blockData['block']['blockId'] = $block->getId();
        if (is_bool($viewMode)) {
            throw new Exception("boolean viewMode, strange");
        }
        $publish = false;
        if ($viewMode === Context::VIEW_MODE_PROD) {
            $publish = true;
        }
        $cmsFileManager = $this->cmsFileManager;
        if (is_null($listMediaUrl)) {
            $listMediaUrl = $cmsFileManager->mediaListInBlockData($blockData, $publish);
        }
        if (!isset($blockData['root'])) {
            $blockData['root'] = array();
        }

        $blockData['media'] = $listMediaUrl['media'];

        if ($renderer['type'] == 'twig') {
            $instance = new TwigRenderer();
            $instance->setTwig($this->getTemplating());
            $instance->setTemplateName($renderer['twig']);
            return $instance->render($blockData, $viewMode);
        }
        return null;
//        return $this->getTemplating()->render(
//            $templateTwig,
//            array('data' => $blockData)
//        );
    }

    public function publish(Block $block, array $listRenderer)
    {
        $event = new BlockEvent($block, $listRenderer);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::onBlockPublish, $event);
        $cmsFileManager = $this->cmsFileManager;
        if (!$event->isDefaultPrevented()) {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery("
                SELECT bp FROM KitpagesCmsBundle:BlockPublish bp
                WHERE bp.block = :block
            ")->setParameter('block', $block);
            $blockPublishList = $query->getResult();

            foreach($blockPublishList as $blockPublish){
                $this->deletePublished($blockPublish);
            }

            // Vraimetn necessaire ???
            $em->persist($block);
            $em->flush();
            $em->refresh($block);
            if ($block->getBlockType() == Block::BLOCK_TYPE_EDITO) {
                $blockData = $block->getData();
                if (!is_null($blockData) && isset($blockData['root'])) {
                    foreach($listRenderer as $nameRenderer => $renderer) {

                        $cmsFileManager->publishInBlockData($blockData);

                        $listMediaUrl = $cmsFileManager->mediaListInBlockData($blockData, true);

                        $blockData['media'] = $listMediaUrl['media'];

                        $resultingHtml = $this->render($renderer, $block, Context::VIEW_MODE_PROD);

                        $blockPublish = new BlockPublish();
                        $blockPublish->initByBlock($block);

                        $blockPublish->setData(array("html"=>$resultingHtml, "media" => $listMediaUrl['media']));
                        $blockPublish->setRenderer($nameRenderer);
                        $em->persist($blockPublish);
                        $event->set("blockPublish", $blockPublish);
                        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockPublishRenderer, $event);
                    }
                }
            }
            $block->setIsPublished(true);
            $em->persist($block);
            $em->flush();
        }
        $event->set("blockPublish", null);
        $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockPublish, $event);
    }

    public function afterModify($block, $oldBlockData)
    {
        if ($oldBlockData != $block->getData()) {
            $block->setRealUpdatedAt(new \DateTime());
            $block->setIsPublished(false);
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();
            $event = new BlockEvent($block);
            $event->setData('oldBlockData', $oldBlockData);
            $this->getDispatcher()->dispatch(KitpagesCmsEvents::afterBlockModify, $event);
        }
    }

    ////
    //  Validator
    ////
    public function stripTagText($text)
    {
        return $this->getUtil()->stripTags(
            array(
                'allowTags' => array("span","div","li","ul","ol","u","i","em", "strong", "strike","b","p","br","hr", "a"),
                'allowAttribs' => array("class", "href", "target", "style")
            ),
            $text
        );
    }

}
