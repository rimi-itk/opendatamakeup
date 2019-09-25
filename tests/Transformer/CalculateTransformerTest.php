<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\CalculateTransformer;

class CalculateTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = CalculateTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'names' => ['first name'],
                    'name' => 'sum of a and b',
                    'expression' => 'a + b',
                ],
                $this->buildFromCSV(
                    self::class,
                    <<<'CSV'
a,b
1,2
3,4
5,6
7,8
CSV
                ),
                $this->buildFromCSV(
                    self::class.'_000',
                    <<<'CSV'
a,b,sum of a and b
1,2,3
3,4,7
5,6,11
7,8,15
CSV
                ),
            ],
        ];
    }
}
