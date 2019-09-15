<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\FilterTransformer;

class FilterTransformerTest extends AbstractTransformerTest
{
    protected static $class = FilterTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'key' => 'name',
                    'match' => 'M',
                    'partial' => true,
                ],
                [
                    ['name' => 'Mikkel'],
                    ['name' => 'James'],
                ],
                [
                    ['name' => 'Mikkel'],
                ],
            ],

            [
                [
                    'key' => 'name',
                    'match' => 'M',
                    'partial' => true,
                    'ignore_case' => true,
                ],
                [
                    ['name' => 'Mikkel'],
                    ['name' => 'James'],
                ],
                [
                    ['name' => 'Mikkel'],
                    ['name' => 'James'],
                ],
            ],
        ];
    }
}
