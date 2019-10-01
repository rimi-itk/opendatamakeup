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
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

/**
 * @Transform(
 *     id="change_type",
 *     name="Change type",
 *     description="Change type of a column",
 *     options={
 *         "names": @Option(type="array"),
 *         "type": @Option(type="type")
 *     }
 * )
 */
class ChangeTypeTransformer extends AbstractTransformer
{
    /**
     * @var array
     */
    private $names;

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

        $type = Type::getType($this->type);
        foreach ($this->names as $name) {
            $newColumns[$name] = new Column($name, $type);
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
}
