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
    protected static $transformer = FilterTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'column' => 'name',
                    'match' => 'M',
                    'partial' => true,
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
name
Mikkel
James
CSV
                ),
                $this->buildFromCSV(
                    $this->getTableName('_expected'),
                    <<<'CSV'
name
Mikkel
CSV
                ),
            ],

            [
                [
                    'column' => 'name',
                    'match' => 'M',
                    'partial' => true,
                    'ignoreCase' => true,
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
name
Mikkel
James
CSV
                ),
                $this->buildFromCSV(
                    $this->getTableName('_expected'),
                    <<<'CSV'
name
Mikkel
James
CSV
                ),
            ],
        ];
    }
}
