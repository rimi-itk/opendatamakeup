<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\Table;
use App\Transformer\Exception\AbstractTransformerException;
use PHPUnit\Framework\TestCase;

abstract class AbstractTransformerTest extends TestCase
{
    protected static $class = null;

    /**
     * @dataProvider dataProvider
     *
     * @param array                              $configuration
     * @param array                              $input
     * @param array|AbstractTransformerException $expected
     */
    public function testTransform(array $configuration, $input, $expected)
    {
        $transformer = new static::$class();
        $transformer->setConfiguration($configuration);

        if ($expected instanceof AbstractTransformerException) {
            $this->expectExceptionObject($expected);
        }

        if (!$input instanceof Table) {
            $input = new Table($input);
        }

        $actual = $transformer->transform($input);

        if (!$expected instanceof Table) {
            $expected = new Table($expected);
        }

        $this->assertInstanceOf(Table::class, $actual);
        $this->assertSame($expected->getColumns(), $actual->getColumns(), 'columns');
        $this->assertSame($expected->getItems(), $actual->getItems(), 'items');
    }
}
