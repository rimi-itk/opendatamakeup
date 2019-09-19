<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\DataSet;
use App\Transformer\FilterTransformer;

class FilterTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = FilterTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'key' => 'name',
                    'match' => 'M',
                    'partial' => true,
                ],
                DataSet::buildFromCSV(
                    <<<'CSV'
name
Mikkel
James
CSV
                ),
                DataSet::buildFromCSV(
                    <<<'CSV'
name
Mikkel
CSV
                ),
            ],
            //
            //            [
            //                [
            //                    'key' => 'name',
            //                    'match' => 'M',
            //                    'partial' => true,
            //                    'ignore_case' => true,
            //                ],
            //                Table::createFromCSV(<<<'CSV'
            //name
            //Mikkel
            //James
            //CSV
            //                ),
            //                Table::createFromCSV(<<<'CSV'
            //name
            //Mikkel
            //James
            //CSV
            //                ),
            //            ],
        ];
    }
}
