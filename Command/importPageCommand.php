<?php
namespace Kitpages\CmsBundle\Command;

use Kitpages\CmsBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kitpages\FileBundle\Entity\FileInterface;

class importPageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kitCms:importPage')
            ->addArgument('pathfile', InputArgument::REQUIRED, 'path file')
            ->addOption('l', null, InputOption::VALUE_OPTIONAL, "If set, modify language of the page")
            ->addOption('lu', null, InputOption::VALUE_OPTIONAL, "If set, update language of the page")
            ->setHelp(<<<EOT
The <info>kitCms:importPage</info> command import a page.
EOT
            )
            ->setDescription('command to export a page')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $pathfile = $input->getArgument('pathfile');

        $language = $input->getOption('l');

        $importManager = $this->getContainer()->get('kitpages.cms.manager.import');

        $page = $importManager->createNewPageByFile($pathfile, array(
            'page_language' => $language
        ));

        $output->writeln('import in:'.$page->getSlug());

    }


}