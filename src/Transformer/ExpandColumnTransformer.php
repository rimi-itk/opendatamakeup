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
use App\Data\DataSet;

/**
 * @Transform(
 *     id="expand_name",
 *     name="Expand column",
 *     description="Expand a key into multiple keys",
 *     options={
 *         "column": @Option(type="column"),
 *         "map": @Option(type="map")
 *     }
 * )
 */
class ExpandColumnTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $column;

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
    public function transform(DataSet $input): DataSet
    {
        // @TODO: Chech that type of column is JSON.

        return $this->map($input, function ($item) {
            $value = $this->getValue($item, $this->column);
            unset($item[$this->column]);

            foreach ($this->map as $name => $propertyPath) {
                $item[$name] = $this->getValue($value, $propertyPath);
            }

            return $item;
        });
    }
}
