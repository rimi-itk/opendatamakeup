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
    protected static $class = SelectNamesTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'names' => ['birthday'],
                ],
                Table::createFromCSV([
                    'name',
                    'Mikkel',
                ]),
                new InvalidKeyException('invalid keys: birthday'),
            ],

            [
                [
                    'names' => ['birthday'],
                ],
                Table::createFromCSV([
                    'name,birthday',
                    'Mikkel,1975-05-23',
                ]),
                Table::createFromCSV([
                    'name',
                    'Mikkel',
                ]),
            ],

            [
                [
                    'names' => ['birthday'],
                    'include' => false,
                ],
                Table::createFromCSV([
                    'name,birthday',
                    'Mikkel,1975-05-23',
                ]),
                Table::createFromCSV([
                    'birthday',
                    '1975-05-23',
                ]),
            ],
        ];
    }
}
