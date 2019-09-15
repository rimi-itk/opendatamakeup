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
 *     name="Replace",
 *     description="Replace values",
 *     options={
 *         "keys": @Option(type="array"),
 *         "search": @Option(type="string"),
 *         "replace": @Option(type="string"),
 *         "regexp": @Option(type="bool", required=false, default=false)
 *     }
 * )
 */
class ReplaceTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $keys;

    /**
     * @var string
     */
    private $search;

    /**
     * @var string
     */
    private $replace;

    /**
     * @var bool
     */
    private $regexp;

    public function transform(Table $input): Table
    {
        $items = array_map(function ($item) {
            foreach ($this->keys as $key) {
                $value = $this->getValue($item, $key);
                if ($this->regexp) {
                    $value = preg_replace($this->search, $this->replace, $value);
                } else {
                    $value = str_replace($this->search, $this->replace, $value);
                }
                $item[$key] = $value;
            }

            return $item;
        }, $input->getItems());

        return new Table($items);
    }
}
