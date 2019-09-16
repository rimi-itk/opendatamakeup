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
 *     id="select_names",
 *     name="Remove keys",
 *     description="Removes one or more keys from the dataset",
 *     options={
 *         "names": @Option(type="array"),
 *         "include": @Option(type="bool", required=false, default=true)
 *     }
 * )
 */
class SelectNamesTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $names;

    /**
     * @var bool
     */
    private $include;

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
        $diff = array_diff($this->names, $names);
        if (!empty($diff)) {
            throw new InvalidKeyException('invalid keys: '.implode(', ', $diff));
        }

        $namesToRemove = $this->include ? array_diff($names, $this->names) : $this->names;

        return $this->map($input, static function ($item) use ($namesToRemove) {
            foreach ($namesToRemove as $name) {
                unset($item[$name]);
            }

            return $item;
        });
    }
}
