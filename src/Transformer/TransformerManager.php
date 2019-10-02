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

class TransformerManager
{
    /**
     * @var array
     */
    private $transformers;

    /**
     * @var array
     */
    private $aliases;

    public function __construct(array $transformers)
    {
        // Add transformer ids (as well as class names).
        $this->transformers = $transformers;
        $this->aliases = array_combine(array_column($transformers, 'id'), array_keys($transformers));
    }

    /**
     * @return AbstractTransformer[]
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * @param string     $name
     * @param array|null $options
     *
     * @return AbstractTransformer
     */
    public function getTransformer(string $name, array $options = null): AbstractTransformer
    {
        $transformers = $this->getTransformers();
        if (\array_key_exists($name, $this->aliases)) {
            $name = $this->aliases[$name];
        }

        if (!\array_key_exists($name, $transformers)) {
            throw new InvalidArgumentException(sprintf('Transformer with name "%s" does not exist', $name));
        }

        /** @var AbstractTransformer $transformer */
        $transformer = new $name($transformers[$name]);
        if (null !== $options) {
            $transformer->setOptions($options);
        }

        return $transformer;
    }
}
