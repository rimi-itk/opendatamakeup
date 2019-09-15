<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Helper;

class Helper
{
    /**
     * @param array $value
     *
     * @return bool
     *
     * @see https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     */
    public static function isAssoc($value): bool
    {
        if (!\is_array($value) || [] === $value) {
            return false;
        }

        return array_keys($value) !== range(0, \count($value) - 1);
    }

    public static function isMap($value): bool
    {
        return !empty($value) && static::isAssoc($value);
    }
}
