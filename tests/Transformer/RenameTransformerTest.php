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
use App\Transformer\RenameTransformer;

class RenameTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = RenameTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'from' => 'birthdate',
                    'to' => 'birthday',
                ],
                Table::createFromCSV([
                    'birthdate',
                    '1975-05-23',
                ]),
                Table::createFromCSV([
                    'birthday',
                    '1975-05-23',
                ]),
            ],

            [
                [
                    'from' => 'a',
                    'to' => 'A',
                ],
                Table::createFromCSV([
                    'a,A',
                    '1,2',
                ]),
                new InvalidKeyException('Name "A" already exists'),
            ],
        ];
    }
}
