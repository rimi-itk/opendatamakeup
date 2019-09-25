<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\ReplaceTransformer;
use Doctrine\DBAL\Types\Type;

class ReplaceTransformerTest extends AbstractTransformerTest
{
    protected static $transformer = ReplaceTransformer::class;

    public function dataProvider(): array
    {
        return [
            [
                [
                    'names' => ['birthday'],
                    'search' => '75',
                    'replace' => '1975',
                ],
                $this->buildFromCSV(
                    static::class,
                    <<<'CSV'
birthday
23-05-75
03-08-63
CSV
                ),
                $this->buildFromCSV(
                    static::class.'_000',
                    <<<'CSV'
birthday
23-05-1975
03-08-63
CSV
                ),
            ],

            [
                [
                    'names' => ['birthday'],
                    'search' => '/([0-9]{2})-([0-9]{2})-([0-9]{2})/',
                    'replace' => '19\3-\2-\1',
                    'regexp' => true,
                ],
                $this->buildFromCSV(
                    static::class,
                    <<<'CSV'
birthday
23-05-75
03-08-63
CSV
                ),
                $this->buildFromData(static::class.'_000', [
                    ['birthday' => '1975-05-23'],
                    ['birthday' => '1963-08-03'],
                ], [
                    [
                        'name' => 'birthday',
                        'type' => Type::STRING,
                    ],
                ]),
            ],
        ];
    }
}
