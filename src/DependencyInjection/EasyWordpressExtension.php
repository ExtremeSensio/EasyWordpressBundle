<?php

namespace EasyWordpressBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class EasyWordpressExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $wordpressDirectory = isset($config['wordpress_directory']) ?
            $config['wordpress_directory'] : $container->getParameter('kernel.root_dir') . '/../web';
        $container->setParameter('easy_wordpress.wordpress_directory', $wordpressDirectory);

        $themeDirectory = $config['theme_directory'];
        if (substr($themeDirectory, -1) != '/') {
            $themeDirectory .= '/';
        }
        $container->setParameter('easy_wordpress.theme_directory', $themeDirectory);

        $controllerNamespace = $config['controllers_namespace'];
        if (substr($controllerNamespace, -2) != '\\\\') {
            $controllerNamespace .= '\\\\';
        }
        $container->setParameter('easy_wordpress.controllers_namespace', $controllerNamespace);

        $controllerDir = sprintf('%s/../src/%s', $container->getParameter('kernel.root_dir'), str_replace('\\\\', '/', $controllerNamespace));
        $controllerNamespace = str_replace('\\\\', '\\', $controllerNamespace);
        foreach (glob($controllerDir . '*Controller.php') as $controllerFile) {
            $controllerName = substr(basename($controllerFile), 0, -4);
            $controllerClass = $controllerNamespace . $controllerName;
            $container
                ->register(
                    sprintf('easy_wordpress.controllers.%s', $controllerName),
                    $controllerClass
                )
                ->addMethodCall('setContainer', array(new Reference('service_container')));
        }

        $yoastTitle = $config['yoast_title_override'];
        $container->setParameter('easy_wordpress.yoast_title_override', $yoastTitle);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');

        $this->addClassesToCompile(array(
            'EasyWordpressBundle\\Annotation\\CustomPostType',
            'EasyWordpressBundle\\Annotation\\TemplateName',
            'EasyWordpressBundle\\Controller\\WordpressController',
            'EasyWordpressBundle\\Service\\WordpressDataCollector',
            'EasyWordpressBundle\\Service\\WordpressHelper',
            'EasyWordpressBundle\\Twig\\Extension\\WordpressTwigExtension',
        ));
    }

}
