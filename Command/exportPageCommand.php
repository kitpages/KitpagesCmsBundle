<?php
namespace Kitpages\CmsBundle\Command;

use Kitpages\CmsBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Kitpages\FileBundle\Entity\FileInterface;

class exportPageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kitCms:exportPage')
            ->addArgument('slug', InputArgument::REQUIRED, 'slug of page')
            ->addOption('d', null, InputOption::VALUE_NONE, 'If set, page for duplicate')
            ->addOption('c', null, InputOption::VALUE_NONE, 'If set, page chidlren')
            ->setHelp(<<<EOT
The <info>kitCms:exportPage</info> command export a page.
EOT
            )
            ->setDescription('command to export a page')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $duplicate = $input->getOption('d');

        $children = $input->getOption('c');

        $slug = $input->getArgument('slug');

        $exportManager = $this->getContainer()->get('kitpages.cms.manager.export');

        $date = date('YmdHis');

        if (!$children) {
            $exportManager->exportFilePageSlugForDuplicate($slug, $date);

            $output->writeln('export in:'.$exportManager->getDirExport($date));

        } else {
            $exportManager->exportFilePageAndChildrenSlugForDuplicate($slug, $date);

            $output->writeln('export in:'.$exportManager->getDirExport($date));

        }





    }


}