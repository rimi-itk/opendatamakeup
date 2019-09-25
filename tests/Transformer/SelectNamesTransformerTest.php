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
                $this->buildFromCSV(
                    self::class,
                    <<<'CSV'
name
Mikkel
CSV
                ),
                new InvalidKeyException('invalid keys: first name'),
            ],

            [
                [
                    'names' => ['name'],
                ],
                $this->buildFromCSV(
                    self::class,
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                $this->buildFromCSV(
                    self::class.'_000',
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
                $this->buildFromCSV(
                    self::class,
                    <<<'CSV'
name,birthday
Mikkel,1975-05-23
CSV
                ),
                $this->buildFromCSV(
                    self::class.'_000',
                    <<<'CSV'
birthday
1975-05-23
CSV
                ),
            ],
        ];
    }
}
