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
                                    ->useAttributeAsKey('template_slug')
                                        ->prototype('array')
                                        ->children()
                                            ->scalarNode('class')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('name')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('twig')
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()


//                                    ->addDefaultsIfNotSet()
//                                    ->children()
//                                        ->arrayNode('standard')
//                                            ->addDefaultsIfNotSet()
//                                            ->children()
//                                                ->scalarNode('class')
//                                                    ->defaultValue('\Kitpages\CmsBundle\Form\BlockTemplateEditStandardType')
//                                                    ->cannotBeEmpty()
//                                                ->end()
//                                                ->scalarNode('name')
//                                                    ->defaultValue('Standard')
//                                                    ->cannotBeEmpty()
//                                                ->end()
//                                                ->scalarNode('twig')
//                                                    ->defaultValue('KitpagesCmsBundle:Block:edit.html.twig')
//                                                    ->cannotBeEmpty()
//                                                ->end()
//                                            ->end()
//                                        ->end()
//                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('renderer')
                            ->useAttributeAsKey('template_slug')
                                ->prototype('array')


                                    ->useAttributeAsKey('renderer_slug')
                                        ->prototype('array')
                                        ->children()
                                            ->scalarNode('type')
                                                ->cannotBeEmpty()
                                            ->end()
                                            ->scalarNode('twig')
                                            ->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()
//                            ->addDefaultsIfNotSet()
//                            ->children()
//                                ->arrayNode('standard')
//                                    ->addDefaultsIfNotSet()
//                                    ->children()
//                                        ->arrayNode('default')
//                                            ->addDefaultsIfNotSet()
//                                            ->children()
//                                                ->scalarNode('type')
//                                                    ->defaultValue('twig')
//                                                    ->cannotBeEmpty()
//                                                ->end()
//                                                ->scalarNode('twig')
//                                                    ->defaultValue('KitpagesCmsBundle:Block:block-render-standard-default.html.twig')
//                                                ->end()
//                                            ->end()
//                                        ->end()
//                                    ->end()
//                                ->end()
//                            ->end()
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
                        ->scalarNode('renderer_twig_main')
                            ->defaultValue('KitpagesCmsBundle:Page:layout.html.twig')
                        ->end()
                        ->arrayNode('data_inheritance_list')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('data_inheritance_form_class')
                        ->end()
                        ->scalarNode('data_inheritance_form_twig')
                        ->end()
                        ->arrayNode('layout_list')
                            ->useAttributeAsKey('layout')
                            ->prototype('array')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('renderer_twig')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('data_form_class')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('data_form_twig')
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('zone_list')
                                        ->useAttributeAsKey('location_in_page')
                                            ->prototype('array')
                                            ->children()
                                                ->scalarNode('renderer')
                                                    ->cannotBeEmpty()
                                                ->end()
                                                ->arrayNode('authorized_block_template_list')
                                                    ->isRequired()
                                                    ->requiresAtLeastOneElement()
                                                    ->beforeNormalization()
                                                        ->ifTrue(function($v){ return !is_array($v); })
                                                        ->then(function($v){ return array($v); })
                                                    ->end()
                                                    ->prototype('scalar')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->scalarNode('default_twig')
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