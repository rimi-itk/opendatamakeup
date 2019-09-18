<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\Converter;

use App\Data\DataSource;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class Converter
{
    /** @var SerializerInterface */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function toTable($data, string $key = null): DataSource
    {
        if (\is_string($data)) {
            $data = json_decode($data, true);
        }

        if (null !== $key) {
            $data = $data[$key];
        }

        $csv = $this->serializer->serialize($data, 'csv');

        return DataSource::createFromCSV($csv);
    }

    public function toArray(DataSource $table)
    {
        return array_map(function ($item) {
            $unFlattened = [];
            $this->unFlatten($item, $unFlattened);

            return $unFlattened;
        }, $table->getItems());
    }

    private function unFlatten(array $item, array &$result, string $keySeparator = '.'): void
    {
        foreach ($item as $name => $value) {
            $steps = explode($keySeparator, $name);
            foreach ($steps as $index => $step) {
                if (0 === $index) {
                    $pointer = &$result;
                }
                if (!\array_key_exists($step, $pointer) || !\is_array($pointer[$step])) {
                    $pointer[$step] = [];
                }
                $pointer = &$pointer[$step];
            }
            $pointer = $value;
        }

        return;

        foreach ($item as $name => $value) {
            $steps = explode($keySeparator, $name);
            $key = array_shift($steps);
            foreach (array_reverse($steps) as $index => $step) {
                if (0 === $index) {
                    $tmp[$step] = $value;
                } else {
                    $tmp[$step] = $tmp;
                }
            }
            $result[$key];
        }
    }
}
