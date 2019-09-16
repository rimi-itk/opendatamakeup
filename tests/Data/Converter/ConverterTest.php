<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data\Converter;

use App\Data\Converter\Converter;
use App\Data\Table;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConverterTest extends KernelTestCase
{
    /** @var Converter */
    private $converter;

    protected function setUp(): void
    {
        self::bootKernel();

        // @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;
        $this->converter = $container->get(Converter::class);
    }

    /**
     * @dataProvider dataToTable
     *
     * @param $data
     * @param $key
     * @param $expected
     */
    public function testToTable($data, $key, $expected): void
    {
        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $this->converter->toTable($data, $key);
        $this->assertSame($expected->getColumns(), $actual->getColumns(), 'columns');
        $this->assertSame($expected->getItems(), $actual->getItems(), 'columns');
    }

    /**
     * @dataProvider dataToArray
     *
     * @param Table $table
     * @param array $expected
     */
    public function testToArray(Table $table, array $expected): void
    {
        $actual = $this->converter->toArray($table);

        $this->assertSame($expected, $actual);
    }

    public function dataToTable(): array
    {
        return [
            [
                [
                    ['person' => ['name' => 'Mikkel']],
                    ['name' => 'James'],
                ],
                null,
                Table::createFromCSV(
                    <<<'CSV'
person.name,name
Mikkel,
,James
CSV
                ),
            ],

            [
                [
                    'data' => [
                        ['person' => ['name' => 'Mikkel']],
                        ['name' => 'James'],
                    ],
                ],
                'data',
                Table::createFromCSV(
                    <<<'CSV'
person.name,name
Mikkel,
,James
CSV
                ),
            ],
        ];
    }

    public function dataToArray(): array
    {
        return [
            [
                Table::createFromCSV(
                    <<<'CSV'
person.name,name
Mikkel,
,James
CSV
                ),
                [
                    [
                        'person' => ['name' => 'Mikkel'],
                        'name' => '',
                    ],
                    [
                        'person' => ['name' => ''],
                        'name' => 'James',
                    ],
                ],
            ],

            //            [
            //                Table::createFromCSV([
            //                    'person.name,name',
            //                    'Mikkel,',
            //                    ',James',
            //                ]),
            //                [
            //                    'data' => [
            //                        ['person' => ['name' => 'Mikkel']],
            //                        ['name' => 'James'],
            //                    ],
            //                ],
            //            ],
        ];
    }
}
