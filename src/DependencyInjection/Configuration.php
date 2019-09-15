<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DependencyInjection;

use App\Helper\Helper;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('app');
        $treeBuilder->getRootNode()
            ->children()
            ->variableNode('transformers')
            ->isRequired()
            ->validate()
            ->ifTrue(static function ($v) {
                return !Helper::isMap($v);
            })->thenInvalid('The transformers option must be an associative array.')->end()
            ->end();

        return $treeBuilder;
    }
}
