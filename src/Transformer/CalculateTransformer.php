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
 *     alias="calculate",
 *     name="Calculate",
 *     description="",
 *     options={
 *         "name": @Option(type="string", description="The column to put the expression result into"),
 *         "expression": @Option(type="string"),
 *         "type": @Option(type="type", description="The type of the expression result"),
 *     },
 *     example="transformer: calculate
arguments:
  name: sum of a and b
  expression: a + b
  type: float
 "
 * )
 */
class CalculateTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expression;

    /**
     * @var string
     */
    private $type;

    /**
     * {@inheritdoc}
     */
    public function transform(DataSet $input): DataSet
    {
        $columns = $input->getColumns();
        // @TODO: Confirm that this creates a fresh clone without any references to the original.
        $newColumns = clone $columns;

        $type = $this->getType($this->type);
        $newColumns[$this->name] = new Column($this->name, $type);

        [$names, $quotedExpression] = $this->getQuoteNamesInExpression($this->expression, $input);
        $invalidNames = array_diff($names, $columns->getKeys());
        if (!empty($invalidNames)) {
            throw new InvalidColumnException('Invalid names: '.implode(', ', $invalidNames));
        }

        $output = $input->copy($newColumns->toArray())
            ->createTable();

        $expressions = $input->getQuotedColumnNames();
        $expressions[$this->name] = $quotedExpression;

        $sql = sprintf(
            'INSERT INTO %s(%s) SELECT %s FROM %s;',
            $output->getQuotedTableName(),
            implode(', ', $output->getQuotedColumnNames()),
            implode(', ', $expressions),
            $input->getQuotedTableName()
        );

        return $output->buildFromSQL($sql);
    }

    public function transformColumns(array $columns): array
    {
        // TODO: Implement transformColumns() method.
    }

    /**
     * Quote names in expression and return the quoted expression along with a list of unquoted names.
     *
     * @param string $expression
     *
     * @return array
     *
     * @throws InvalidColumnException
     */
    private function getQuoteNamesInExpression(string $expression, DataSet $dataSet): array
    {
        // Remove string literals.
        $expression = preg_replace('/"([\\\\"]|[^"])*"/', '', $expression);
        // Replace escaped strings.

        $names = [];
        if (false !== preg_match_all('/(?P<name>(?:[a-z][a-z0-9_]*|`[^`]+`))/i', $expression, $matches)) {
            foreach ($matches['name'] as $name) {
                $names[$name] = trim($name, '`');
            }
        }

        // Order names by length.
        uksort($names, static function ($a, $b) {
            return \strlen($b) - \strlen($a);
        });

        foreach ($names as $key => $name) {
            $expression = str_replace($key, $dataSet->getQuotedColumnName($name), $expression);
        }

        return [$names, $expression];
    }
}
