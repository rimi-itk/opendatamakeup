<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\DataSource;
use App\Transformer\Exception\AbstractTransformerException;
use App\Transformer\Manager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractTransformerTest extends KernelTestCase
{
    protected static $transformer = null;

    /** @var Manager */
    private $manager;

    protected function setUp(): void
    {
        static::bootKernel();

        // @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = static::$container;
        $this->manager = $container->get(Manager::class);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array                              $options
     * @param array                              $input
     * @param array|AbstractTransformerException $expected
     */
    public function testTransform(array $options, $input, $expected)
    {
        $transformer = $this->manager->getTransformer(static::$transformer);
        $transformer->setOptions($options);

        if ($expected instanceof AbstractTransformerException) {
            $this->expectExceptionObject($expected);
        }

        if (!$input instanceof DataSource) {
            $input = new DataSource($input);
        }

        $actual = $transformer->transform($input);

        if (!$expected instanceof DataSource) {
            $expected = new DataSource($expected);
        }

        $this->assertInstanceOf(DataSource::class, $actual);
        $this->assertSame($expected->getColumns(), $actual->getColumns(), 'columns');
        $this->assertSame($expected->getItems(), $actual->getItems(), 'items');
    }
}
