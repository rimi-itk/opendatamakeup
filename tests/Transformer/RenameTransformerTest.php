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
                DataSet::buildFromCSV([
                    'birthdate',
                    '1975-05-23',
                ]),
                DataSet::buildFromCSV([
                    'birthday',
                    '1975-05-23',
                ]),
            ],

            [
                [
                    'from' => 'a',
                    'to' => 'A',
                ],
                DataSet::buildFromCSV([
                    'a,A',
                    '1,2',
                ]),
                new InvalidKeyException('Name "A" already exists'),
            ],
        ];
    }
}
