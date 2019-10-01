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
    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        $names = $columns->getKeys();
        $diff = array_diff($this->names, $names);
        if (!empty($diff)) {
            throw new InvalidKeyException('invalid keys: '.implode(', ', $diff));
        }

        $namesToKeep = $this->include ? $this->names : array_diff($names, $this->names);

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
