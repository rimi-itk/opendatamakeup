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

/**
 * @Transform(
 *     name="Expand keys",
 *     description="Expand a key into multiple keys",
 *     options={
 *         "key": @Option(type="string"),
 *         "map": @Option(type="map")
 *     }
 * )
 */
class ExpandKeyTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $map;

    /**
     * Expand key into new columns.
     *
     * @param array $input
     *
     * @return array
     */
    public function transform(Table $input): Table
    {
        $items = array_map(function ($item) {
            $value = $this->getValue($item, $this->key);
            unset($item[$this->key]);

            foreach ($this->map as $name => $propertyPath) {
                $item[$name] = $this->getValue($value, $propertyPath);
            }

            return $item;
        }, $input->getItems());

        return new Table($items);
    }
}
