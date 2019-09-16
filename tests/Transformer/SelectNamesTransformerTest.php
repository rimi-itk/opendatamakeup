<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\Table;
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
                Table::createFromCSV([
                    'name',
                    'Mikkel',
                ]),
                new InvalidKeyException('invalid keys: first name'),
            ],

            [
                [
                    'names' => ['name'],
                ],
                Table::createFromCSV(
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                Table::createFromCSV(
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
                Table::createFromCSV(
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                Table::createFromCSV(
                    <<<'CSV'
birthday
1975-05-23
CSV
                ),
            ],
        ];
    }
}
