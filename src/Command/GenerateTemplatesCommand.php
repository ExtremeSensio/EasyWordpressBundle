<?php

namespace EasyWordpressBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTemplatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('easywordpress:generate:templates')
            ->setDescription('Generate template files in theme directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('wordpress.helper');
        $fs = $this->getContainer()->get('filesystem');

        $themeDirectory = $this->getContainer()->getParameter('easy_wordpress.theme_directory');
        $fs->remove($themeDirectory . 'page-templates');

        $output->writeln("Parsing {$themeDirectory}");

        foreach ($helper->getTemplatesList() as $filename => $detail) {
            $output->writeln("Generating {$filename}");
            if (substr($filename, 0, 4) == 'page') {
                $filepath = sprintf(
                    '%s/page-templates/%s',
                    $themeDirectory, $filename
                );
                $fileContent = <<<PHP
<?php
/**
  * Template name: {$detail['name']}
  */
PHP;
            } else {
                $filepath = sprintf(
                    '%s/%s',
                    $themeDirectory, $filename
                );
                $fileContent = '';
            }
            $fs->dumpFile($filepath, $fileContent);
        }
    }
}
