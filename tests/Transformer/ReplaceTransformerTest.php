<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\ReplaceTransformer;

class ReplaceTransformerTest extends AbstractTransformerTest
{
    protected static $class = ReplaceTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'keys' => ['birthday'],
                    'search' => '75',
                    'replace' => '1975',
                ],
                [
                    [
                        'birthday' => '23-05-75',
                    ],
                    [
                        'birthday' => '03-08-63',
                    ],
                ],
                [
                    [
                        'birthday' => '23-05-1975',
                    ],
                    [
                        'birthday' => '03-08-63',
                    ],
                ],
            ],

            [
                [
                    'keys' => ['birthday'],
                    'search' => '/([0-9]{2})-([0-9]{2})-([0-9]{2})/',
                    'replace' => '19\3-\2-\1',
                    'regexp' => true,
                ],
                [
                    [
                        'birthday' => '23-05-75',
                    ],
                    [
                        'birthday' => '03-08-63',
                    ],
                ],
                [
                    [
                        'birthday' => '1975-05-23',
                    ],
                    [
                        'birthday' => '1963-08-03',
                    ],
                ],
            ],
        ];
    }
}
