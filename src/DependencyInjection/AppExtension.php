<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DependencyInjection;

use App\Transformer\Manager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

//        $services = $container->findTaggedServiceIds('app.transformer');
//header('content-type: text/plain'); echo var_export($services, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);

//        $definition = $container->getDefinition(Manager::class);
//        $definition->replaceArgument('$transformers', $config['transformers']);
    }
}
