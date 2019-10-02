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
use App\Transformer\SelectColumnsTransformer;

class SelectColumnsTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = SelectColumnsTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'columns' => ['first name'],
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
name
Mikkel
CSV
                ),
                new InvalidKeyException('invalid keys: first name'),
            ],

            [
                [
                    'columns' => ['name'],
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
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
                    'columns' => ['name'],
                    'include' => false,
                ],
                $this->buildFromCSV(
                    $this->getTableName(),
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
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
        ];
    }
}
