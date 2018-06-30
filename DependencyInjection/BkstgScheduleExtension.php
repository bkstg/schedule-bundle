<?php

namespace Bkstg\ScheduleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BkstgScheduleExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param array            $configs   The configuration array.
     * @param ContainerBuilder $container The container.
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // If the timeline bundle is active register notification listener.
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['BkstgTimelineBundle'])) {
            $loader->load('services.timeline.yml');
        }
    }
}
