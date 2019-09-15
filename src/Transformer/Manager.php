<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Transformer\Exception\InvalidArgumentException;

class Manager
{
    /**
     * @var array
     */
    private $transformers;

    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @return AbstractTransformer[]
     */
    public function getTransformers()
    {
        return $this->transformers;
    }

    /**
     * @param string $name
     *
     * @return AbstractTransformer
     *
     * @throws InvalidArgumentException
     */
    public function getTransformer(string $name): ?AbstractTransformer
    {
        $transformers = $this->getTransformers();

        if (!\array_key_exists($name, $transformers)) {
            throw new InvalidArgumentException(sprintf('Transformer with name "%s" does not exist', $name));
        }

        return new $transformers[$name]();
    }
}
