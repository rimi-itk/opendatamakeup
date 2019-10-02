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
use App\Data\DataSet;
use App\Tests\ContainerTestCase;

class ConverterTest extends ContainerTestCase
{
    /** @var Converter */
    private $converter;

    private function converter()
    {
        if (null === $this->converter) {
            $this->converter = $this->getContainer()->get(Converter::class);
        }

        return $this->converter;
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

        $actual = $this->converter()->toTable($data, $key);
        $this->assertSame($expected->getColumns(), $actual->getColumns(), 'columns');
        $this->assertSame($expected->getItems(), $actual->getItems(), 'columns');
    }

    /**
     * @dataProvider dataToArray
     *
     * @param DataSet $table
     * @param array   $expected
     */
    public function testToArray(DataSet $table, array $expected): void
    {
        $actual = $this->converter()->toArray($table);

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
                DataSet::buildFromCSV(
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
                DataSet::buildFromCSV(
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
                DataSet::buildFromCSV(
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
