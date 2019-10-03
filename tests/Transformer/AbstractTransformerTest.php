<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Tests\ContainerTestCase;
use App\Transformer\Exception\AbstractTransformerException;

abstract class AbstractTransformerTest extends ContainerTestCase
{
    protected static $transformer = null;

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
        $this->assertEquals(iterator_to_array($expected->rows()), iterator_to_array($actual->rows()), 'items');
    }

    abstract public function dataProvider();

    private $tableCounter = 0;

    protected function getTableName(string $suffix = null)
    {
        $name = preg_replace('@^([a-z]+\\\\)+@i', '', static::class);
        $name .= sprintf('%03d', $this->tableCounter);
        ++$this->tableCounter;

        return $name;
    }

    protected function buildFromCSV(string $name, string $csv, array $columns = null)
    {
        return $this->dataSetManager()->createDataSetFromCSV($name, $csv, $columns);
    }

    protected function buildFromData(string $name, array $items, array $columns = null)
    {
        return $this->dataSetManager()->createDataSetFromData($name, $items, $columns);
    }
}
