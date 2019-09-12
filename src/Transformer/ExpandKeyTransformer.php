<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Transformer\Exception\InvalidInputException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ExpandKeyTransformer extends AbstractTransformer
{
    /**
     * Expand key into new columns.
     *
     * @param array $input
     *
     * @return array
     */
    public function transform(array $input): array
    {
        $key = $this->configuration['key'];
        $map = $this->configuration['map'];
        $accessor = PropertyAccess::createPropertyAccessor();

        return array_map(function ($item) use ($key, $map, $accessor) {
            if (!\array_key_exists($key, $item)) {
                throw new InvalidInputException('invalid key: '.$key);
            }

            $value = $item[$key];
            unset($item[$key]);

            foreach ($map as $name => $propertyPath) {
                $item[$name] = $this->getValue($value, $propertyPath);
            }

            return $item;
        }, $input);
    }

    public function validateConfiguration(): void
    {
        $this->requireString('key');
        $this->requireMap('map');
    }
}
