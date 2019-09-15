<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\ExpandKeyTransformer;

class ExpandKeyTransformerTest extends AbstractTransformerTest
{
    protected static $class = ExpandKeyTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'key' => 'person',
                    'map' => ['name' => 'name'],
                ],
                [
                    [
                        'person' => [
                            'name' => 'Mikkel',
                            'birthday' => '1975-05-23',
                        ],
                    ],
                ],
                [
                    ['name' => 'Mikkel'],
                ],
            ],

            [
                [
                    'key' => 'person',
                    'map' => [
                        'name' => 'first name',
                        'birthday' => 'birthday',
                    ],
                ],
                [
                    [
                        'person' => [
                            'first name' => 'Mikkel',
                            'birthday' => '1975-05-23',
                        ],
                    ],
                ],
                [
                    [
                        'name' => 'Mikkel',
                        'birthday' => '1975-05-23',
                    ],
                ],
            ],

            [
                [
                    'key' => 'person',
                    'map' => [
                        'name' => 'name.first',
                        'birthday' => 'birthday',
                    ],
                ],
                [
                    [
                        'person' => [
                            'name' => ['first' => 'Mikkel'],
                            'birthday' => '1975-05-23',
                        ],
                    ],
                ],
                [
                    [
                        'name' => 'Mikkel',
                        'birthday' => '1975-05-23',
                    ],
                ],
            ],
        ];
    }
}
