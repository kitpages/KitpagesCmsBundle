<?php

namespace Kitpages\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration processer.
 * Parses/validates the extension configuration and sets default values.
 *
 * @author Philippe Le Van (@plv)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kitpages_cms');

        $this->addBlockSection($rootNode);
        $this->addPageSection($rootNode);
        $this->addOtherSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Parses the kitpages_cms.block config section
     * Example for yaml driver:
     * kitpages_cms:
     *     block:
     *         template:
     *             template_list: {standard: \Kitpages\CmsBundle\Form\TemplateStandardType}
     *
     * @param ArrayNodeDefinition $node
     * @return void
     */
    private function addBlockSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('block')
                    ->addDefaultsIfNotSet()
                    ->children()
            
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('template_list')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('standard')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('class')
                                                    ->defaultValue('\Kitpages\CmsBundle\Form\BlockTemplateEditStandardType')
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->scalarNode('name')
                                                    ->defaultValue('Standard')
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->scalarNode('twig')
                                                    ->defaultValue('KitpagesCmsBundle:Block:edit.html.twig')
                                                    ->cannotBeEmpty()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
            
                        ->arrayNode('renderer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('standard')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('default')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('type')
                                                    ->defaultValue('twig')
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->scalarNode('twig')
                                                    ->defaultValue('KitpagesCmsBundle:Block:block-render-standard-default.html.twig')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
            
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Parses the kitpages_cms.block config section
     * Example for yaml driver:
     * kitpages_cms:
     *     block:
     *         template:
     *             template_list: {standard: \Kitpages\CmsBundle\Form\TemplateStandardType}
     *
     * @param ArrayNodeDefinition $node
     * @return void
     */
    private function addPageSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('page')
                    ->addDefaultsIfNotSet()
                    ->children()
            
                        ->scalarNode('default_layout')
                            ->defaultValue('::base.html.twig')
                            ->cannotBeEmpty()
                        ->end()
            
                    ->end()
                ->end()
            ->end();
    }
    
    /**
     * Parses the kitpages_cms others sections
     * Example for yaml driver:
     * kitpages_cms:
     *     target_parameter: 'cms_target'
     *
     * @param ArrayNodeDefinition $node
     * @return void
     */
    private function addOtherSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('target_parameter')
                    ->defaultValue(null)
                ->end()
            ->end();
    }
}