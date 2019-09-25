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
use App\Data\Exception\InvalidNameException;
use App\Data\DataSet;
use App\Transformer\Exception\InvalidKeyException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Column;

/**
 * @Transform(
 *     id="rename",
 *     name="Rename",
 *     description="Renames a column",
 *     options={
 *         "from": @Option(type="string"),
 *         "to": @Option(type="string")
 *     }
 * )
 */
class RenameTransformer extends AbstractTransformer
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
     * @throws InvalidNameException
     */
    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        if (\array_key_exists($this->from, $columns)) {
            throw new InvalidKeyException(sprintf('Name "%s" does not exist', $this->from));
        }
        if (\array_key_exists($this->to, $columns)) {
            throw new InvalidKeyException(sprintf('Name "%s" already exists', $this->to));
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
}
