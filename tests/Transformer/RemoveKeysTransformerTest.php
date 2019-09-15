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
use App\Transformer\RemoveKeysTransformer;

class RemoveKeysTransformerTest extends AbstractTransformerTest
{
    protected static $class = RemoveKeysTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'keys' => ['birthday'],
                ],
                Table::createFromCSV([
                    'name',
                    'Mikkel',
                ]),
                new InvalidKeyException('invalid keys: birthday'),
            ],

            [
                [
                    'keys' => ['birthday'],
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
        ];
    }
}
