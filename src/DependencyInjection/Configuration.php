<?php
/**
 * Created by PhpStorm.
 * User: antony
 * Date: 26/06/2016
 * Time: 02:13
 */

namespace EasyWordpressBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('easy_wordpress');

        $rootNode
            ->children()
            ->scalarNode('wordpress_directory')->defaultNull()->end()
            ->scalarNode('theme_directory')->isRequired()->end()
            ->scalarNode('controllers_namespace')->isRequired()->end()
            ->scalarNode('yoast_title_override')->isRequired()->end();

        return $treeBuilder;
    }

}
