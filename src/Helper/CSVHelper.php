<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Helper;

class CSVHelper
{
    public const TYPE_INT = 0b0001;
    public const TYPE_FLOAT = 0b0010;
    public const TYPE_DATETIME = 0b0100;
    public const TYPE_STRING = 0b1000;

    public function csv2array(string $csv, $keysInFirstRow = true, array $keys = [])
    {
        $lines = array_map('trim', explode(PHP_EOL, $csv));
        $rows = array_map('str_getcsv', $lines);

        if ($keysInFirstRow) {
            $keys = array_shift($rows);
        }

        // @TODO Guess types of values in each column and convert if possible.
//        $types = [];
//        foreach ($rows as $row) {
//
//        }

        return array_map(function ($values) use ($keys) {
            return array_combine($keys, array_map([$this, 'getValue'], $values));
        }, $rows);
    }

    private function getValue($value)
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
     * @param $value
     *
     * @return int
     */
    private function guessType($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return self::TYPE_INT;
        }
        if (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return self::TYPE_FLOAT;
        }
        try {
            new \DateTime($value);

            return self::TYPE_DATETIME;
        } catch (\Exception $exception) {
        }

        return self::TYPE_STRING;
    }
}
