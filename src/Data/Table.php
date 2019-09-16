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

class Table
{
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_STRING = 'string';
    public const TYPE_DATA = 'data';

    /**
     * @var array
     */
    private $columns;

    /**
     * @var array
     */
    private $items;

    /**
     * Table constructor.
     *
     * @param array      $items
     * @param array|null $columns
     */
    public function __construct(array $items, array $columns = null)
    {
        $this->items = $items;
        $this->buildColumns($columns);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Join this Table with another Table.
     *
     * @param string $name
     *                     The name to join on
     * @param table  $that
     *                     The other table
     *
     * @return table
     *               The resulting Table
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
     * Create table from CSV. If no headers are specified, first row of CSV is assumed to be headers.
     *
     * @param string|array $csv
     * @param array|null   $headers
     *
     * @return Table
     */
    public static function createFromCSV($csv, array $headers = null): self
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

        $data = array_map(function ($values) use ($headers) {
            return array_combine($headers, array_map([static::class, 'getValue'], $values));
        }, $rows);

        return new static($data);
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

    private function buildColumns(array $columns = null)
    {
        if (null === $columns) {
            if (empty($this->items)) {
                $this->columns = [];

                return;
            }

            $names = array_keys($this->items[0]);
            $columns = array_map(function ($name) {
                return [
                    'name' => $name,
                    'type' => $this->guessType($name),
                ];
            }, $names);
        }
        $this->columns = array_column($columns, null, 'name');
    }

    private static function getValue($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return (int) $value;
        }
        if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return (float) $value;
        }

        return $value;
    }

    private function guessType($name)
    {
        $votes = [
            static::TYPE_INT => 0,
            static::TYPE_FLOAT => 0,
            static::TYPE_DATETIME => 0,
            static::TYPE_STRING => 0,
        ];
        foreach ($this->items as $item) {
            $value = $item[$name];
            if (filter_var($value, FILTER_VALIDATE_INT)) {
                ++$votes[static::TYPE_INT];
            }
            if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
                ++$votes[static::TYPE_FLOAT];
            }
            if (\is_string($value)) {
                try {
                    new \DateTime($value);
                    ++$votes[static::TYPE_DATETIME];
                } catch (\Exception $exception) {
                }
            }
            ++$votes[static::TYPE_STRING];
        }

        foreach ($votes as $type => $count) {
            if (\count($this->items) === $count) {
                return $type;
            }
        }

        return static::TYPE_STRING;
    }
}
