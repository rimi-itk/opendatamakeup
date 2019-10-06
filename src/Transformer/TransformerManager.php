<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Data\Exception\InvalidTypeException;
use App\Transformer\Exception\InvalidArgumentException;
use App\Transformer\Exception\ValidationException;
use DateTime;

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
        $this->aliases = array_combine(array_column($transformers, 'alias'), array_keys($transformers));
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

    public function normalizeArguments(string $transformer, array $arguments)
    {
        $transformer = $this->getTransformer($transformer);
        if (null !== $transformer) {
            $metadata = $transformer->getMetadata();
            $arguments = array_filter($arguments, static function ($name) use ($metadata) {
                return \array_key_exists($name, $metadata['options']);
            }, ARRAY_FILTER_USE_KEY);

            foreach ($metadata['options'] as $name => $info) {
                if (!\array_key_exists($name, $arguments)) {
                    if ($info['required']) {
                        throw new ValidationException(sprintf('Argument %s must be defined.', $name));
                    }
                    if ($info['default']) {
                        $arguments[$name] = $info['default'];
                    }
                }
                $arguments[$name] = static::convertToType($name, $info['type'], $arguments);
            }
        }

        return $arguments;
    }

    private static function convertToType($name, $typeName, array $values)
    {
        if (isset($values[$name])) {
            $value = $values[$name];
            switch ($typeName) {
                case 'bool':
                    return (bool) $value;
                case 'columns':
                    return $value;
                case 'date':
                    return $value instanceof DateTime ? $value : new DateTime($value);
                case 'int':
                    return (int) $value;
                case 'string':
                    return (string) $value;
                default:
                    throw new InvalidTypeException(sprintf('Unknown type: %s', $typeName));
            }

            return $value;
        }

        return null;
    }
}
