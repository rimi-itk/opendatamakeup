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
use App\Data\Exception\InvalidColumnException;
use App\Data\DataSet;
use App\Transformer\Exception\InvalidKeyException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Column;

/**
 * @Transform(
 *     alias="rename",
 *     name="Rename",
 *     description="Renames a column",
 *     options={
 *         "from": @Option(type="column"),
 *         "to": @Option(type="string")
 *     }
 * )
 */
class RenameColumnTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @param DataSet $input
     *
     * @return DataSet
     *
     * @throws InvalidColumnException
     */
    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        if (!$columns->containsKey($this->from)) {
            throw new InvalidKeyException(sprintf('Column "%s" does not exist', $this->from));
        }
        if ($columns->containsKey($this->to)) {
            throw new InvalidKeyException(sprintf('Column "%s" already exists', $this->to));
        }

        $newColumns = new ArrayCollection();
        foreach ($input->getColumns() as $name => $column) {
            if ($name === $this->from) {
                $name = $this->to;
                $options = $column->toArray();
                unset($options['name']);
                $column = new Column($name, $column->getType(), $options);
            }
            $newColumns[$name] = $column;
        }

        $output = $input->copy($newColumns->toArray())
            ->createTable();

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s;',
            $output->getQuotedTableName(),
            implode(',', $output->getQuotedColumnNames()),
            implode(',', $input->getQuotedColumnNames()),
            $input->getQuotedTableName()
        );

        return $output->buildFromSQL($sql);
    }

    public function transformColumns(array $columns): array
    {
        // TODO: Implement transformColumns() method.
    }
}
