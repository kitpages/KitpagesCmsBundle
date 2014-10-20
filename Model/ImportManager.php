<?php
namespace Kitpages\CmsBundle\Model;

use Kitpages\CmsBundle\Entity\Page;
use Kitpages\UtilBundle\Service\Util;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Kitpages\CmsBundle\Entity\Block;

class ImportManager
{

    static protected $optionExportDuplicateSimple = array('base');

    public function __construct(
        PageManager $pageManager
    )
    {
        $this->pageManager = $pageManager;
    }

    public function createNewPageByFile($pathFile, $option = array())
    {
        $json = file_get_contents($pathFile);
        return $this->createNewPageByJson($json, $option);
    }

    public function createNewPageByJson($json, $option = array())
    {
        $em = $this->pageManager->getDoctrine()->getManager();

        $treeRepository = $em->getRepository('Kitpages\CmsBundle\Entity\Page');

        $cmsFileManager = $this->pageManager->getCmsFileManager();


        $page = $this->pageManager->getObjectBySerializeFormat($json, 'json', 'Kitpages\CmsBundle\Entity\Page');
        $page->setChildren(new \Doctrine\Common\Collections\ArrayCollection());

        $this->pageManager->updatePageByOption($page, $option);

        $pageZoneList = $page->getPageZoneList();

        foreach($pageZoneList as $pageZone) {

            $pageZone->setPage($page);

            $ZoneBlock = $pageZone->getZone()->getZoneBlockList();
            $zoneBlock = $ZoneBlock->current();

            foreach($pageZone->getZone()->getZoneBlockList() as $zoneBlock) {
                $block = $zoneBlock->getBlock();
                $data = $block->getData();
//                $mediaList = $cmsFileManager->mediaList($data['root'], false);

                foreach($data['root'] as $field => $value) {
                    if (substr($field, '0', '6') == 'media_') {
                        unset($data['root'][$field]);
                    }
                }

                $block->setData($data);
                $em->persist($block);
                $em->flush();
                $zoneBlock->setBlock($block);
            }
            $em->persist($pageZone->getZone());
            $em->flush();
            foreach($pageZone->getZone()->getZoneBlockList() as $zoneBlock) {
                $zoneBlock->setZone($pageZone->getZone());
                $em->persist($zoneBlock);
                $em->flush();
            }
        }

        $em->persist($page);
        $em->flush();

        $jsonDecode = json_decode($json);
        if (isset($jsonDecode->children)) {
            foreach ($jsonDecode->children as $jsonDecodeChildren) {
                $pageChild = $this->createNewPageByJson(json_encode($jsonDecodeChildren), $option);
                $treeRepository->persistAsLastChildOf($pageChild, $page);
                $em->flush();
            }
        }

        return $page;
    }


//
//    public function exportFile($xmlContent, $date, $identifiant)
//    {
//        $xmlFile = fopen($this->getDirExport($date).'/'.$identifiant.'.xml', 'w');
//        fwrite($xmlFile, $xmlContent);
//        fclose($xmlFile);
//    }
//
//    public function getDirExport($date)
//    {
//        $dirExport = $this->exportDir.'/'.$date;
//        $this->kitpagesUtil->mkdirr($dirExport);
//        return $dirExport;
//    }


}