<?php

namespace EasyWordpressBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallWordpressCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('easywordpress:install-wordpress')
            ->setDescription('Copy wordpress files from web/wp');
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

        $fs->mirror($webDir.'/wp/wp-content', $webDir.'/content');
        $fs->mirror($webDir.'/wp/wp-content/themes', $rootDir.'/Resources/themes');
        $fs->remove($webDir.'/content/themes');
        $fs->symlink($rootDir.'/Resources/themes', $webDir.'/content/themes');

        $output->writeln("<info>Mooved wp files from web/wp</info>");
    }
}
