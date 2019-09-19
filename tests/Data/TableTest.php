<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data;

use App\Data\Exception\InvalidNameException;
use App\Data\DataSet;
use App\Transformer\Exception\AbstractTransformerException;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
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

        $actual = DataSet::buildFromCSV($csv);

        $this->assertSame($expected['columns'], $actual->getColumns());
        $this->assertSame($expected['items'], $actual->getItems());
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
    public function testJoinTables(DataSet $first, string $name, DataSet $second, $expected)
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
                implode(PHP_EOL, [
                    'id,name',
                    '1,Mikkel',
                    '2,James',
                ]),
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
                implode(PHP_EOL, [
                    'number',
                    '1',
                    '3.14',
                ]),
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
                implode(PHP_EOL, [
                    'keys',
                    '"a, b, c"',
                ]),
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
