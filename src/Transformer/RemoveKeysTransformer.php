<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Annotation\Transform;
use App\Annotation\Transform\Option;
use App\Data\Table;
use App\Transformer\Exception\InvalidKeyException;

/**
 * Class RemoveKeysTransformer.
 *
 * @Transform(
 *     name="Remove keys",
 *     description="Removes one or more keys from the dataset",
 *     options={
 *         "keys": @Option(type="array"),
 *     }
 * )
 */
class RemoveKeysTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $keys;

    /**
     * Remove named keys.
     *
     * @param array $input
     *
     * @return array
     */
    public function transform(Table $input): Table
    {
        $names = array_keys($input->getColumns());
        $diff = array_diff($this->keys, $names);
        if (!empty($diff)) {
            throw new InvalidKeyException('invalid keys: '.implode(', ', $diff));
        }

        return $this->map($input, function ($item) {
            foreach ($this->keys as $key) {
                unset($item[$key]);
            }

            return $item;
        });
    }
}
