<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests;

use App\Data\DataSetManager;
use App\Data\DataSource\DataSourceManager;
use App\Data\DataWranglerManager;
use App\Transformer\TransformerManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerTestCase extends KernelTestCase
{
    /**
     * @see https://stackoverflow.com/a/42161440
     * @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        if (null === static::$container) {
            static::bootKernel();
        }

        return static::$container;
    }

    private $services = [];

    protected function get(string $service)
    {
        if (!isset($this->services[$service])) {
            $this->services[$service] = $this->getContainer()->get($service);
        }

        return $this->services[$service];
    }

    protected function dataWranglerManager(): DataWranglerManager
    {
        return $this->get(DataWranglerManager::class);
    }

    protected function dataSourceManager(): DataSourceManager
    {
        return $this->get(DataSourceManager::class);
    }

    protected function dataSetManager(): DataSetManager
    {
        return $this->get(DataSetManager::class);
    }

    protected function transformerManager(): TransformerManager
    {
        return $this->get(TransformerManager::class);
    }
}
