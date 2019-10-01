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
                    'name' => 'sum of a and b',
                    'expression' => 'a + b',
                    'type' => 'int',
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
a,b
1,2
3,4
5,6
7,8
CSV
                ),
                $this->buildFromCSV(
                    $this->getTableName('_expected'),
                    <<<'CSV'
a,b,sum of a and b
1,2,3
3,4,7
5,6,11
7,8,15
CSV
                ),
            ],

            [
                [
                    'name' => 'a divided by b',
                    'expression' => 'a / b',
                    'type' => 'float',
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
a,b
1,2
3,4
5,6
7,8
CSV
                ),
                $this->buildFromCSV(
                    $this->getTableName('expected'),
                    <<<'CSV'
a,b,a divided by b
1,2,0.5
3,4,.75
5,6,0.833333333
7,8,0.875
CSV
                ),
            ],
        ];
    }
}
