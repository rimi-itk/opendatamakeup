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
use App\Transformer\Exception\InvalidKeyException;
use App\Transformer\SelectNamesTransformer;

class SelectNamesTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = SelectNamesTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'names' => ['first name'],
                ],
                DataSet::buildFromCSV([
                    'name',
                    'Mikkel',
                ]),
                new InvalidKeyException('invalid keys: first name'),
            ],

            [
                [
                    'names' => ['name'],
                ],
                DataSet::buildFromCSV(
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                DataSet::buildFromCSV(
                    <<<'CSV'
name
Mikkel
CSV
                ),
            ],

            [
                [
                    'names' => ['name'],
                    'include' => false,
                ],
                DataSet::buildFromCSV(
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                DataSet::buildFromCSV(
                    <<<'CSV'
birthday
1975-05-23
CSV
                ),
            ],
        ];
    }
}
