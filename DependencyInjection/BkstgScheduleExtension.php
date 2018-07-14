<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BkstgScheduleExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param array            $configs   The configuration array.
     * @param ContainerBuilder $container The container.
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        // If the timeline bundle is active register notification listener.
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['BkstgTimelineBundle'])) {
            $loader->load('services.timeline.yml');
        }
        if (isset($bundles['BkstgSearchBundle'])) {
            $loader->load('services.search.yml');
        }
    }
}
