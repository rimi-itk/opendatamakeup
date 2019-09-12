<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Transformer\Exception\AbstractTransformerException;
use App\Transformer\Exception\InvalidInputException;
use App\Transformer\RemoveKeysTransformer;

class RemoveKeysTransformerTest extends AbstractTransformerTest
{
    /**
     * @dataProvider dataProvider
     *
     * @param array                              $configuration
     * @param array                              $input
     * @param array|AbstractTransformerException $expected
     */
    public function testTransform(array $configuration, array $input, $expected)
    {
        $transformer = new RemoveKeysTransformer();
        $transformer->setConfiguration($configuration);

        if ($expected instanceof AbstractTransformerException) {
            $this->expectExceptionObject($expected);
        }

        $actual = $transformer->transform($input);

        $this->assertEquals($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            [
                [
                    'keys' => ['birthday'],
                ],
                [
                    [
                        'name' => 'Mikkel',
                    ],
                ],
                new InvalidInputException('invalid key: birthday'),
            ],
            [
                [
                    'keys' => ['birthday'],
                ],
                [
                    [
                        'name' => 'Mikkel',
                        'birthday' => '1975-05-23',
                    ],
                ],
                [
                    ['name' => 'Mikkel'],
                ],
            ],
        ];
    }
}
