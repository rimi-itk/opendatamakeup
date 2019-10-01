<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\Converter;

use App\Data\DataSet;
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

    public function toTable($data, string $key = null): DataSet
    {
        if (\is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        if (null !== $key) {
            $data = $data[$key];
        }

        $csv = $this->serializer->serialize($data, 'csv');

        return DataSet::buildFromCSV($csv);
    }

    public function toArray(DataSet $table)
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
    }
}
