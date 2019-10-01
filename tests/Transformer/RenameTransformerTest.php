<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

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
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
birthdate
1975-05-23
CSV
                ),
                $this->buildFromCSV(
                    $this->getTableName('_expected'),
                    <<<'CSV'
birthday
1975-05-23
CSV
                ),
            ],

            //            [
            //                [
            //                    'from' => 'a',
            //                    'to' => 'A',
            //                ],
            //                $this->buildFromCSV(<<<'CSV'
            //a,A
            //1,2
            //CSV
            //                ),
            //                new InvalidKeyException('Name "A" already exists'),
            //            ],
        ];
    }
}
