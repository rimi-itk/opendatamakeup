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
 *     id="expand_name",
 *     name="Expand name",
 *     description="Expand a key into multiple keys",
 *     options={
 *         "name": @Option(type="string"),
 *         "map": @Option(type="map")
 *     }
 * )
 */
class ExpandNameTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $name;

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
        return $this->map($input, function ($item) {
            $value = $this->getValue($item, $this->name);
            unset($item[$this->name]);

            foreach ($this->map as $name => $propertyPath) {
                $item[$name] = $this->getValue($value, $propertyPath);
            }

            return $item;
        });
    }
}
