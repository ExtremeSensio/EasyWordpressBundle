<?php

namespace EasyWordpressBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateFilesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('easywordpress:generate:files')
            ->setDescription('Get wordpress with wp-cli');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $rootDir = $container->get('kernel')->getRootDir();
        $webDir = $rootDir.'/../web';
        $fs = new Filesystem;

        $fs->copy(__DIR__.'/../../doc/.env.example', $webDir.'/../.env');
        $fs->copy(__DIR__.'/../../doc/boot.php.example', $webDir.'/boot.php');
        $fs->copy(__DIR__.'/../../doc/index.php.example', $webDir.'/index.php');
        $fs->copy(__DIR__.'/../../doc/wp-config.php.example', $webDir.'/wp-config.php');
        $fs->copy(__DIR__.'/../../doc/parameters.php.example', $rootDir.'/config/parameters.php');

        $output->writeln("<info>Copied files from doc folder.</info>");
    }
}
