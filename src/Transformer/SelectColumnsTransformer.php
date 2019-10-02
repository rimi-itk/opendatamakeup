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
use App\Transformer\Exception\InvalidKeyException;
use Doctrine\DBAL\Schema\Column;

/**
 * @Transform(
 *     id="select_names",
 *     name="Select columns",
 *     description="Selects (or excludes) one or more columns",
 *     options={
 *         "columns": @Option(type="columns"),
 *         "include": @Option(type="bool", required=false, default=true)
 *     }
 * )
 */
class SelectColumnsTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $columns;

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
    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        $names = $columns->getKeys();
        $diff = array_diff($this->columns, $names);
        if (!empty($diff)) {
            throw new InvalidKeyException('invalid keys: '.implode(', ', $diff));
        }

        $namesToKeep = $this->include ? $this->columns : array_diff($names, $this->columns);

        $newColumns = $columns->filter(static function ($value, $name) use ($namesToKeep) {
            return \in_array($name, $namesToKeep, true);
        });

        $output = $input->copy($newColumns->toArray())->createTable();

        $sql = sprintf(
            'INSERT INTO %s SELECT %s FROM %s;',
            $output->getQuotedTableName(),
            implode(',', $newColumns->map(static function (Column $column) use ($input) {
                return $input->getQuotedColumnName($column->getName());
            })->getValues()),
            $input->getQuotedTableName()
        );

        return $output->buildFromSQL($sql);
    }
}
