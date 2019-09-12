<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

class ReplaceTransformer extends AbstractTransformer
{
    public function transform(array $input): array
    {
        $keys = $this->configuration['keys'];
        $search = $this->configuration['search'];
        $replace = $this->configuration['replace'];
        $regexp = $this->configuration['regexp'];

        return array_map(function ($item) use ($keys, $search, $replace, $regexp) {
            foreach ($keys as $key) {
                $value = $this->getValue($item, $key);
                if ($regexp) {
                    $value = preg_replace($search, $replace, $value);
                } else {
                    $value = str_replace($search, $replace, $value);
                }
                $item[$key] = $value;
            }

            return $item;
        }, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(): void
    {
        $this->requireArray('keys');
        $this->requireString('search');
        $this->requireString('replace');
        $this->checkBoolean('regexp', false);
    }
}
