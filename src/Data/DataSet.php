<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data;

use App\Data\Exception\InvalidNameException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

class DataSet
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Connection */
    private $connection;

    /** @var AbstractPlatform */
    private $platform;

    /** @var string */
    private $name;

    /**
     * The underlying table holding the data and schema.
     *
     * @var Table
     */
    private $table;

    /**
     * Table constructor.
     *
     * @param array      $items
     * @param array|null $columns
     */
    public function __construct(string $name, array $columns = null)
    {
        if (empty($name)) {
            throw new \RuntimeException('Data set name cannot be empty');
        }
        $this->name = $name;
        if (null !== $columns) {
            $this->table = $this->buildTable($columns);
        }
    }

    public function getNewName()
    {
        $name = $this->name;

        $pattern = '/_(?P<number>\d+)$/';
        $format = '%03d';
        if (preg_match($pattern, $name, $matches)) {
            return preg_replace($pattern, '_'.sprintf($format, ((int) $matches['number']) + 1), $this->name);
        }

        return $this->name.'_'.sprintf($format, 0);
    }

    public function copy(array $columns = null)
    {
        $name = $this->getNewName();

        return (new static($name, $columns ?? $this->getColumns()->toArray()))
            ->setEntityManager($this->entityManager);
    }

    /**
     * Create table from CSV. If no headers are specified, first row of CSV is assumed to be headers.
     *
     * @param string|array $csv
     * @param array|null   $headers
     *
     * @return DataSet
     */
    public function buildFromCSV($csv, array $headers = null): self
    {
        if (\is_string($csv)) {
            $csv = explode(PHP_EOL, $csv);
        }
        // Ignore empty lines.
        $lines = array_filter(array_map('trim', $csv));
        $rows = array_map('str_getcsv', $lines);

        if (null === $headers) {
            $headers = array_shift($rows);
        }

        $items = array_map(static function ($values) use ($headers) {
            return array_combine($headers, $values);
        }, $rows);

        return $this->buildFromData($items);
    }

    public function buildFromData(array $items): self
    {
        if (null === $this->table) {
            $columns = $this->buildColumns($items);
            $this->table = $this->buildTable($columns);
        }
        $this->createTable();

        return $this->loadData($items);
    }

    public function buildFromSQL(string $sql): self
    {
        $statement = $this->prepare($sql);

        $statement->execute();

        return $this;
    }

    public function getTableName()
    {
        return $this->table->getName();
    }

    public function getQuotedTableName(string $name = null)
    {
        return $this->quoteName($name ?? $this->getTableName());
    }

    public function getQuotedColumnName(string $name)
    {
        if (!$this->getColumns()->containsKey($name)) {
            throw new InvalidNameException($name);
        }

        return $this->quoteName($name);
    }

    /**
     * Get table columns indexed by their real name (and not a normalized (e.g. down cased) name).
     *
     * @return ArrayCollection<Column>
     */
    public function getColumns()
    {
        $columns = new ArrayCollection();
        foreach ($this->table->getColumns() as $column) {
            $columns[$column->getName()] = $column;
        }

        return $columns;
    }

    private $rowsStatement;

    public function rows()
    {
        if (null === $this->rowsStatement) {
            $statement = sprintf('SELECT * FROM %s;', $this->getQuotedTableName());
            $this->rowsStatement = $this->prepare($statement);
            $this->rowsStatement->execute();
        }
        $columns = $this->getColumns();
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();

        while ($row = $this->rowsStatement->fetch()) {
            array_walk($row, static function (&$value, $name) use ($columns, $platform) {
                $value = $columns[$name]->getType()->convertToPHPValue($value, $platform);
            });
            yield $row;
        }
        $this->rowsStatement = null;
    }

    public function insertRow(array $row)
    {
        $sql = sprintf(
            'INSERT INTO %s(%s) VALUES (%s);',
            $this->getQuotedTableName(),
            implode(',', $this->getColumns()->map(function (Column $column) {
                return $this->quoteName($column->getName());
            })->getValues()),
            implode(',', array_fill(0, $this->getColumns()->count(), '?'))
        );
        $statement = $this->prepare($sql);

        $types = $this->getColumns()->map(static function (Column $column) {
            return $column->getType();
        });

        $index = 0;
        foreach ($row as $name => $value) {
            /** @var Type $type */
            $type = $types[$name];
            $statement->bindValue($index + 1, $type->convertToPHPValue($value, $this->platform), $type);
            ++$index;
        }

        return         $statement->execute();
    }

    /**
     * Wrapper around Connection::prepare().
     *
     * @param string $statement
     *
     * @return \Doctrine\DBAL\Driver\Statement
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @see Connection::prepare()
     */
    private function prepare(string $statement): \Doctrine\DBAL\Driver\Statement
    {
        if (null === $this->entityManager) {
            throw new \RuntimeException('No entity manager set on data source');
        }

        return $this->entityManager->getConnection()->prepare($statement);
    }

    /**
     * Join this Table with another Table.
     *
     * @param string  $name
     *                      The name to join on
     * @param DataSet $that
     *                      The other table
     *
     * @return DataSet
     *                 The resulting Table
     */
    public function join(string $name, self $that)
    {
        if (!\array_key_exists($name, $this->columns) || !\array_key_exists($name, $that->columns)) {
            throw new InvalidNameException(sprintf('Column named "%s" does not exist both tables', $name));
        }

        $thoseItems = array_column($that->items, null, $name);

        $items = [];
        foreach ($this->items as $item) {
            $items[] = $item + $thoseItems[$item[$name]];
        }

        return new static($items);
    }

    /**
     * @return bool|string
     */
    public function toCSV()
    {
        $buffer = fopen('php://temp', 'rb+');
        foreach ($this->rows() as $index => $item) {
            if (0 === $index) {
                fputcsv($buffer, array_keys($item));
            }
            fputcsv($buffer, array_map(static function ($value) {
                if ($value instanceof \DateTime) {
                    return $value->format(\DateTime::ATOM);
                }

                return $value;
            }, $item));
        }
        rewind($buffer);
        $csv = stream_get_contents($buffer);
        fclose($buffer);

        return $csv;
    }

    public function setEntityManager(EntityManagerInterface $entityManager): self
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
        $this->platform = $this->connection->getDatabasePlatform();

        return $this;
    }

    private function quoteName($name): string
    {
        return '`'.$name.'`';
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Build database table.
     *
     * @param array       $columns
     * @param string|null $tableName
     *
     * @return Table
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function buildTable(array $columns)
    {
        $tableName = '__data_set_'.$this->getName();
        $table = new Table($tableName);
        foreach ($columns as $column) {
            $name = $column instanceof Column ? $column->getName() : $column['name'];
            $type = $column instanceof Column ? $column->getType()->getName() : $column['type'];
            $table->addColumn($this->quoteName($name), $type);
        }

        return $table;
    }

    /**
     * Create table in database.
     *
     * @return DataSet
     */
    public function createTable(): self
    {
        if (null === $this->entityManager) {
            throw new \RuntimeException('No entity manager set on data source');
        }

        $connection = $this->entityManager->getConnection();
        $schema = $connection->getSchemaManager();
        $schema->dropAndCreateTable($this->table);

        return $this;
    }

    /**
     * Load data into database table.
     *
     * @param array $items
     * @param bool  $truncate
     *
     * @return $this
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadData(array $items, bool $truncate = true): self
    {
        $columns = $this->getColumns();

        $sql = sprintf(
            'INSERT INTO %s(%s) VALUES (%s);',
            $this->getQuotedTableName(),
            implode(',', $columns->map(function (Column $column) {
                return $this->quoteName($column->getName());
            })->getValues()),
            implode(',', array_fill(0, $columns->count(), '?'))
        );
        $statement = $this->prepare($sql);
        $types = $columns->map(static function (Column $column) {
            return $column->getType();
        });

        foreach ($items as $item) {
            $index = 0;
            foreach ($item as $name => $value) {
                /** @var Type $type */
                $type = $types[$name];
                $statement->bindValue($index + 1, $type->convertToPHPValue($value, $this->platform), $type);
                ++$index;
            }
            $statement->execute();
        }

        return $this;
    }

    public function filter(callable $callback): self
    {
        // Filter may remove all columns, so we have to pass the columns on.
        return new static(array_filter($this->items, $callback), $this->columns);
    }

    public function map(callable $callback)
    {
        return new static(array_map($callback, $this->items));
    }

    private function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    private function buildColumns(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $names = array_keys(reset($items));
        $types = $this->guessTypes($items);
        $columns = array_map(static function ($name) use ($types) {
            return [
                'name' => $name,
                'type' => $types[$name],
            ];
        }, $names);

        return array_column($columns, null, 'name');
    }

    private static function getValue($value, Type $type)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return (int) $value;
        }
        if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * Guess type of a column.
     *
     * @param string $name
     *                      The column name
     * @param array  $items
     *
     * @return string
     *                The type
     */
    private function guessTypes(array $items)
    {
        $item = reset($items);

        $types = [];
        foreach ($item as $name => $value) {
            $values = array_column($items, $name);
            $types[$name] = $this->guessType($values);
        }

        return $types;
    }

    private function guessType(array $values)
    {
        $votes = [
            Type::INTEGER => 0,
            Type::FLOAT => 0,
            Type::DATETIME => 0,
            Type::DATE => 0,
            Type::STRING => 0,
        ];
        foreach ($values as $value) {
            if (filter_var($value, FILTER_VALIDATE_INT)) {
                ++$votes[Type::INTEGER];
            }
            if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
                ++$votes[Type::FLOAT];
            }
            if (\is_string($value) && !empty($value)) {
                // @TODO: Distinguish between DATE and DATETIME.
                try {
                    new \DateTime($value);
                    ++$votes[Type::DATETIME];
                } catch (\Exception $exception) {
                }
            }

            // String is the most generic type.
            ++$votes[Type::STRING];
        }

        foreach ($votes as $type => $count) {
            if (\count($values) === $count) {
                return $type;
            }
        }

        return Type::STRING;
    }
}
