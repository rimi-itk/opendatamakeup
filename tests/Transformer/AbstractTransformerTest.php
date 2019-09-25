<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\DataSetManager;
use App\Transformer\Exception\AbstractTransformerException;
use App\Transformer\TransformerManager;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractTransformerTest extends KernelTestCase
{
    protected static $transformer = null;

    /** @var TransformerManager */
    private $transformerManager;

    /** @var DataSetManager */
    private $dataSetManager;

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        static::bootKernel();

        // @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = static::$container;
        $this->transformerManager = $container->get(TransformerManager::class);
        $this->dataSetManager = $container->get(DataSetManager::class);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array                              $options
     * @param array                              $input
     * @param array|AbstractTransformerException $expected
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testTransform(array $options, $input, $expected)
    {
        $transformer = $this->transformerManager()
            ->getTransformer(static::$transformer)
            ->setOptions($options);

        if ($expected instanceof AbstractTransformerException) {
            $this->expectExceptionObject($expected);
        }

        $actual = $transformer->transform($input);

        $this->assertEquals($expected->getColumns(), $actual->getColumns(), 'columns');
        $this->assertEquals($expected->rows(), $actual->rows(), 'items');
    }

    abstract public function dataProvider();

    protected function buildFromCSV(string $name, string $csv, array $columns = null)
    {
        return $this->dataSetManager()->createDataSetFromCSV($name, $csv, $columns);
    }

    protected function buildFromData(string $name, array $items, array $columns = null)
    {
        return $this->dataSetManager()->createDataSetFromData($name, $items, $columns);
    }

    /**
     * @see https://stackoverflow.com/a/42161440
     * @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
     *
     * @return TransformerManager
     */
    protected function dataSetManager(): DataSetManager
    {
        if (null === $this->dataSetManager) {
            $this->dataSetManager = $this->getContainer()->get(DataSetManager::class);
        }

        return $this->dataSetManager;
    }

    /**
     * @see https://stackoverflow.com/a/42161440
     * @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
     *
     * @return TransformerManager
     */
    protected function transformerManager(): TransformerManager
    {
        if (null === $this->dataSetManager) {
            $this->transformerManager = $this->getContainer()->get(TransformerManager::class);
        }

        return $this->transformerManager;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        if (null === static::$container) {
            static::bootKernel();
        }

        return static::$container;
    }
}
