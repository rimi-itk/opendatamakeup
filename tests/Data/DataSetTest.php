<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data;

use App\Data\DataSet;
use App\Data\Exception\InvalidNameException;
use App\Tests\ContainerTestCase;
use App\Transformer\Exception\AbstractTransformerException;
use Doctrine\DBAL\Types\Type;

class DataSetTest extends ContainerTestCase
{
    /**
     * @dataProvider dataProviderCreateFromCsv
     *
     * @param string                             $csv
     * @param array|AbstractTransformerException $expected
     */
    public function testCreateFromCsv(string $csv, $expected): void
    {
        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $this->buildFromCSV(static::class, $csv);

        $this->assertCount(\count($expected['columns']), $actual->getColumns());
        foreach ($actual->getColumns() as $name => $column) {
            $this->assertArrayHasKey($name, $expected['columns']);
            $this->assertEquals($expected['columns'][$name]['type'], $column->getType());
        }
//        $this->assertSame($expected['items'], $actual->getItems());
    }

    protected function buildFromCSV(string $name, string $csv, array $columns = null)
    {
        return $this->dataSetManager()->createDataSetFromCSV($name, $csv, $columns);
    }

    /**
     * @dataProvider dataProviderJoinTables
     *
     * @param DataSet                              $first
     * @param DataSet                              $second
     * @param DataSet|AbstractTransformerException $expected
     *
     * @throws \App\Data\Exception\InvalidNameException
     */
    public function hest_testJoinTables(DataSet $first, string $name, DataSet $second, $expected)
    {
        if ($expected instanceof \Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $first->join($name, $second);

        $this->assertSame($expected->getColumns(), $actual->getColumns());
        $this->assertSame($expected->getItems(), $actual->getItems());
    }

    public function dataProviderCreateFromCsv(): array
    {
        return [
            [
                <<<'CSV'
id,name
1,Mikkel
2,James
CSV
                ,
                [
                    'columns' => [
                        'id' => [
                            'name' => 'id',
                            'type' => Type::INTEGER,
                        ],
                        'name' => [
                            'name' => 'name',
                            'type' => Type::STRING,
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 1,
                            'name' => 'Mikkel',
                        ],
                        [
                            'id' => 2,
                            'name' => 'James',
                        ],
                    ],
                ],
            ],

            [
                <<<'CSV'
number
1
3.14
CSV
                ,
                [
                    'columns' => [
                        'number' => [
                            'name' => 'number',
                            'type' => Type::FLOAT,
                        ],
                    ],
                    'items' => [
                        [
                            'number' => 1,
                        ],
                        [
                            'number' => 3.14,
                        ],
                    ],
                ],
            ],

            [
                <<<'CSV'
keys
"a, b, c"
CSV
                ,
                [
                    'columns' => [
                        'keys' => [
                            'name' => 'keys',
                            'type' => Type::STRING,
                        ],
                    ],
                    'items' => [
                        [
                            'keys' => 'a, b, c',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function dataProviderJoinTables()
    {
        return [
            [
                new DataSet([]),
                'id',
                new DataSet([]),
                new InvalidNameException('Column named "id" does not exist both tables'),
            ],

            [
                DataSet::buildFromCSV(implode(PHP_EOL, [
                    'id,name',
                    '1,Mikkel',
                    '2,James Hetfield',
                ])),
                'id',
                DataSet::buildFromCSV(implode(PHP_EOL, [
                    'id,birthday',
                    '2,1963-08-03',
                    '1,1975-05-23',
                ])),
                DataSet::buildFromCSV(implode(PHP_EOL, [
                    'id,name,birthday',
                    '1,Mikkel,1975-05-23',
                    '2,James Hetfield,1963-08-03',
                ])),
            ],
        ];
    }
}
