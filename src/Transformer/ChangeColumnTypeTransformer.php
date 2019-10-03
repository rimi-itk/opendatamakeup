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
use App\Data\Exception\InvalidColumnException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

/**
 * @Transform(
 *     alias="change_type",
 *     name="Change type",
 *     description="Change type of columns",
 *     options={
 *         "columns": @Option(type="columns"),
 *         "type": @Option(type="type")
 *     }
 * )
 */
class ChangeColumnTypeTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $columns;

    /**
     * @var string
     */
    private $type;

    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        // @TODO Check that new type is different from current type.
        // @TODO Check that type change makes sense without data loss.
        $newColumns = clone $columns;

        $type = $this->getType($this->type);
        foreach ($this->columns as $column) {
            if (!isset($newColumns[$column])) {
                throw new InvalidColumnException($column);
            }
            $newColumns[$column] = new Column($column, $type);
        }

        $output = $input->copy($newColumns->toArray())
            ->createTable();

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s;',
            $output->getQuotedTableName(),
            implode(', ', $output->getQuotedColumnNames()),
            implode(', ', $input->getQuotedColumnNames()),
            $input->getQuotedTableName()
        );

        return $output->buildFromSQL($sql);
    }

    public function transformColumns(array $columns): array
    {
        // TODO: Implement transformColumns() method.
    }
}
