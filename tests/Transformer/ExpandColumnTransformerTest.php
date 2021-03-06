<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\ExpandColumnTransformer;

class ExpandColumnTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = ExpandColumnTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'column' => 'person',
                    'map' => ['name' => 'name'],
                ],
                $this->buildFromData(
                    $this->getTableName(),
                    [
                        [
                            'person' => [
                                'name' => 'Mikkel',
                                'birthday' => '1975-05-23',
                            ],
                        ],
                    ]
                ),
                $this->buildFromData(
                    $this->getTableName('_expected'),
                    [
                        ['name' => 'Mikkel'],
                    ]
                ),
            ],

            //            [
            //                [
            //                    'name' => 'person',
            //                    'map' => [
            //                        'name' => 'first name',
            //                        'birthday' => 'birthday',
            //                    ],
            //                ],
            //                [
            //                    [
            //                        'person' => [
            //                            'first name' => 'Mikkel',
            //                            'birthday' => '1975-05-23',
            //                        ],
            //                    ],
            //                ],
            //                [
            //                    [
            //                        'name' => 'Mikkel',
            //                        'birthday' => '1975-05-23',
            //                    ],
            //                ],
            //            ],
            //
            //            [
            //                [
            //                    'name' => 'person',
            //                    'map' => [
            //                        'name' => 'name.first',
            //                        'birthday' => 'birthday',
            //                    ],
            //                ],
            //                [
            //                    [
            //                        'person' => [
            //                            'name' => ['first' => 'Mikkel'],
            //                            'birthday' => '1975-05-23',
            //                        ],
            //                    ],
            //                ],
            //                [
            //                    [
            //                        'name' => 'Mikkel',
            //                        'birthday' => '1975-05-23',
            //                    ],
            //                ],
            //            ],
        ];
    }
}
