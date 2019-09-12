<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Transformer\Exception\InvalidInputException;

class RemoveKeysTransformer extends AbstractTransformer
{
    /**
     * Remove named keys.
     *
     * @param array $input
     *
     * @return array
     */
    public function transform(array $input): array
    {
        $keys = $this->configuration['keys'];

        return array_map(static function ($item) use ($keys) {
            foreach ($keys as $key) {
                if (!\array_key_exists($key, $item)) {
                    throw new InvalidInputException('invalid key: '.$key);
                }
                unset($item[$key]);
            }

            return $item;
        }, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(): void
    {
        $this->requireArray('keys');
    }
}
