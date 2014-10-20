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

class ExportManager
{

    static protected $optionExportDuplicateSimple = array('base', 'language');

    static protected $optionExportDuplicateChildren = array('base', 'language', 'children');

    public function __construct(
        PageManager $pageManager,
        Util $util,
        $exportDir
    )
    {
        $this->pageManager = $pageManager;
        $this->exportDir = $exportDir;
        $this->kitpagesUtil = $util;
    }

    public function exportFilePageSlugForDuplicate($slug, $date)
    {
        $this->exportFile(
            $this->pageManager->getContentPageJsonBySlug($slug, self::$optionExportDuplicateSimple),
            $date,
            $slug
        );
    }

    public function exportFilePageAndChildrenSlugForDuplicate($slug, $date)
    {
        $this->exportFile(
            $this->pageManager->getContentPageAndChildrenJsonBySlug($slug, self::$optionExportDuplicateChildren),
            $date,
            $slug
        );
    }

    public function exportFile($jsonContent, $date, $identifiant)
    {
        $jsonFile = fopen($this->getDirExport($date).'/'.$identifiant.'.json', 'w');
        fwrite($jsonFile, $jsonContent);
        fclose($jsonFile);
    }

    public function getDirExport($date)
    {
        $dirExport = $this->exportDir.'/'.$date;
        $this->kitpagesUtil->mkdirr($dirExport);
        return $dirExport;
    }


}