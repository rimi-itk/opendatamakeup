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
 *     id="filter",
 *     name="Filter",
 *     description="Filter",
 *     options={
 *         "key": @Option(type="string"),
 *         "match": @Option(type="string"),
 *         "partial": @Option(type="bool", required=false, default=false),
 *         "ignoreCase": @Option(type="bool", required=false, default=false, name="ignore_case"),
 *         "regexp": @Option(type="bool", required=false, default=false),
 *         "include": @Option(type="bool", required=false, default=true, description="If not set, items that match will be removed rather that kept"),
 *     }
 * )
 */
class FilterTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $match;

    /**
     * @var bool
     */
    private $partial;

    /**
     * @Configuration(type="bool", required=false, default=false, name="ignore_case")
     */
    private $ignoreCase;

    /**
     * @var bool
     */
    private $regexp;

    /**
     * @var bool
     */
    private $include;

    public function transform(Table $input): Table
    {
        return $this->filter($input, function ($item) {
            $value = $this->getValue($item, $this->key);
            $isMatch = false;
            if ($this->regexp) {
                throw new \RuntimeException(__METHOD__.' not implemented');
            } else {
                if ($this->partial) {
                    $isMatch = false !== ($this->ignoreCase ? stripos($value, $this->match) : strpos($value, $this->match));
                } else {
                    $isMatch = 0 === ($this->ignoreCase ? strcasecmp($value, $this->match) : strcmp($value, $this->match));
                }
            }

            return $isMatch && $this->include;
        });
    }
}
