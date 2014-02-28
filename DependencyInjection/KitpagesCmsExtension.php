<?php

/*
 * This file is part of the Kitpages CMS Project
 *
 * (c) Philippe Le Van (@plv)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kitpages\CmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

/**
 * KitpagesCmsBundleExtension
 *
 * @author      Philippe Le Van (@plv)
 */
class KitpagesCmsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $this->remapParametersNamespaces($config, $container, array(
            'block'  => 'kitpages_cms.block.%s'
        ));
        $this->remapParametersNamespaces($config['block'], $container, array(
            'template'  => 'kitpages_cms.block.template.%s'
        ));
        $this->remapParametersNamespaces($config['block'], $container, array(
            'renderer'  => 'kitpages_cms.block.renderer.%s'
        ));
        $this->remapParametersNamespaces($config, $container, array(
            'page'  => 'kitpages_cms.page.%s'
        ));
        $this->remapParametersNamespaces($config['page'], $container, array(
            'layout_list'  => 'kitpages_cms.page.layout_list.%s'
        ));        
        $this->remapParameters($config, $container, array(
            'target_parameter'  => 'kitpages_cms.target_parameter'
        ));
        $this->remapParameters($config, $container, array(
            'view_mode_default'  => 'kitpages_cms.view_mode_default'
        ));

        foreach($config['block']['template']['template_list'] as $keyTemplate => $template) {
            if (!isset($template['class']) && !isset($template['service'])) {
                throw new \Exception(sprintf('Error configuration template Block "%s", class or service parameter require', $keyTemplate));
            }
            if (isset($template['class']) && isset($template['service'])) {
                throw new \Exception(sprintf('Error configuration template Block "%s", class or service parameter require, but not both', $keyTemplate));
            }
        }

        foreach($config['page']['layout_list'] as $keyLayout => $layout) {
            if (!isset($layout['data_form_class']) && !isset($layout['data_form_service'])) {
                throw new \Exception(sprintf('Error configuration layout Page "%s", data_form_class or data_form_service parameter require', $keyLayout));
            }
            if (isset($layout['data_form_class']) && isset($layout['data_form_service'])) {
                throw new \Exception(sprintf('Error configuration layout Page "%s", data_form_class or data_form_service parameter require, but not both', $keyLayout));
            }
        }

    }

    public function getAlias()
    {
        return "kitpages_cms";
    }
    /**
     * Dynamically remaps parameters from the config values
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $namespaces
     * @return void
     */
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!isset($config[$ns])) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    if (null !== $value) {
                        $container->setParameter(sprintf($map, $name), $value);
                    }
                }
            }
        }
    }

    /**
     * 
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     * @return void
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (isset($config[$name])) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    /**
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $namespaces
     * @return void
     */
    protected function remapParametersNamespacesRequire(array $config, ContainerBuilder $container, array $namespaces)
    {

    }


}