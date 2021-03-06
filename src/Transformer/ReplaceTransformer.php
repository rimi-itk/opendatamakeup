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
 *     alias="replace",
 *     name="Replace",
 *     description="Replace values (only for string columns)",
 *     options={
 *         "columns": @Option(type="columns"),
 *         "search": @Option(type="string"),
 *         "replace": @Option(type="string"),
 *         "partial": @Option(type="bool", required=false, default=false),
 *         "regexp": @Option(type="bool", required=false, default=false)
 *     }
 * )
 */
class ReplaceTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $columns;

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
    private $partial;

    /**
     * @var bool
     */
    private $regexp;

    public function transform(DataSet $input): DataSet
    {
        foreach ($this->columns as $column) {
            // @TODO Check that type of column is string.
        }

        $output = $input->copy()->createTable();

        foreach ($input->rows() as $row) {
            foreach ($this->columns as $column) {
                $value = $this->getValue($row, $column);
                if ($this->regexp) {
                    $value = preg_replace($this->search, $this->replace, $value);
                } else {
                    $value = str_replace($this->search, $this->replace, $value);
                }
                $row[$column] = $value;
            }

            $output->insertRow($row);
        }

        return $output;
    }

    public function transformColumns(array $columns): array
    {
        // TODO: Implement transformColumns() method.
    }
}
