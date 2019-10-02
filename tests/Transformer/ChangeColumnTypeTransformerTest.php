<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\ChangeColumnTypeTransformer;

class ChangeColumnTypeTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = ChangeColumnTypeTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'names' => ['a', 'b'],
                    'type' => 'float',
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
                    $this->getTableName('_expected'),
                    <<<'CSV'
a,b
1.0,2.0
3.0,4.0
5.0,6.0
7.0,8.0
CSV
                ),
            ],
        ];
    }
}
