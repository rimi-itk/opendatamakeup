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
 *     id="filter",
 *     name="Filter",
 *     description="Filter",
 *     options={
 *         "column": @Option(type="column"),
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
    private $column;

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

    public function transform(DataSet $input): DataSet
    {
        $output = $input->copy()->createTable();

        foreach ($input->rows() as $row) {
            $value = $this->getValue($row, $this->column);
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

            if ($isMatch && $this->include) {
                $output->insertRow($row);
            }
        }

        return $output;
    }
}
