<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Data\Table;
use App\Transformer\Exception\InvalidArgumentException;

class Manager
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

    public function runTransformers(Table $input, array $transformers)
    {
        $result = $input;
        foreach ($transformers as $transformer) {
            if (!$transformer instanceof AbstractTransformer) {
                $transformer = $this->getTransformer($transformer['name'])
                    ->setOptions($transformer['configuration']);
            }
            $result = $transformer->transform($result);
        }

        return $result;
    }
}
