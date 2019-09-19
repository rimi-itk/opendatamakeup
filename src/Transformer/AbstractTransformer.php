<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Annotation\Configuration;
use App\Annotation\Transform;
use App\Data\DataSet;
use App\Transformer\Exception\InvalidConfigurationException;
use App\Transformer\Exception\InvalidArgumentException;
use App\Transformer\Exception\InvalidKeyException;

abstract class AbstractTransformer
{
    /**
     * @var Transform
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $options;

    public function __construct(array $metadata = null)
    {
        $this->setMetadata($metadata);
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    abstract public function transform(DataSet $input): DataSet;

    public function setOptions(array $options): self
    {
        $this->options = $options;
        $this->validateAndApplyOptions();

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->metadata->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->metadata->description;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getOptions()
    {
        return $this->metadata->getOptions();
    }

    protected function validateAndApplyOptions()
    {
        foreach ($this->metadata['options'] as $name => $option) {
            if ($option['required']) {
                $this->requireOption($name);
            }
            $this->checkType($name, $option['type']);
            $configurationName = $option['name'] ?? $name;
            if (!property_exists($this, $name)) {
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist on %s.', $name, self::class));
            }
            $property = new \ReflectionProperty($this, $name);
            $property->setAccessible(true);
            if (\array_key_exists($configurationName, $this->options)) {
                $property->setValue($this, $this->options[$configurationName]);
            } elseif (isset($option['default'])) {
                $property->setValue($this, $option['default']);
            }
        }
    }

    /**
     * Map table items.
     *
     * @param DataSet  $table
     * @param callable $callback
     *
     * @return DataSet
     */
    protected function map(DataSet $table, callable $callback): DataSet
    {
        return $table->map($callback);
    }

    protected function filter(DataSet $table, callable $callback): DataSet
    {
        return $table->filter($callback);
    }

    /**
     * @param array $value
     *
     * @return bool
     *
     * @see https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     */
    protected function isAssoc($value): bool
    {
        if (!\is_array($value) || [] === $value) {
            return false;
        }

        return array_keys($value) !== range(0, \count($value) - 1);
    }

    protected function isMap($value): bool
    {
        return !empty($value) && $this->isAssoc($value);
    }

    protected function isArray($value): bool
    {
        return \is_array($value) && !$this->isAssoc($value);
    }

    protected function isString($value): bool
    {
        return \is_string($value);
    }

    protected function isBoolean($value): bool
    {
        return \is_bool($value);
    }

    protected function isReadable($objectOrArray, $propertyPath): bool
    {
        $propertyPath = $this->fixPropertyPath($propertyPath);

        return $this->getAccessor()->isReadable($objectOrArray, $propertyPath);
    }

    /**
     * Note: PropertyAccessor should/could be used, but apparently it does not really check existence of array values.
     *
     * @param array $value
     * @param $propertyPath
     *
     * @return array|mixed
     */
    protected function getValue(array $value, $propertyPath)
    {
        $keys = explode('.', $propertyPath);
        foreach ($keys as $key) {
            if (!\array_key_exists($key, $value)) {
                throw (new InvalidKeyException($key))
                    ->setKey($key)
                    ->setValue($value);
            }
            $value = $value[$key];
        }

        return $value;
    }

    protected function requireOption(string $option): void
    {
        if (!\array_key_exists($option, $this->options)) {
            throw new InvalidConfigurationException('missing option: '.$option);
        }
    }

    protected function requireArray(string $key): void
    {
        $this->requireOption($key);

        if (!$this->isArray($this->options[$key])) {
            throw new InvalidConfigurationException('must be an array: '.$key);
        }
    }

    protected function requireMap(string $key): void
    {
        $this->requireOption($key);

        if (!$this->isMap($this->options[$key])) {
            throw new InvalidConfigurationException('must be an map (associative array): '.$key);
        }
    }

    protected function requireString(string $key): void
    {
        $this->requireOption($key);

        if (!$this->isString($this->options[$key])) {
            throw new InvalidConfigurationException('must be a string: '.$key);
        }
    }

    protected function checkType(string $key, string $typeName): void
    {
        if (\array_key_exists($key, $this->options)) {
            $value = $this->options[$key];
            switch ($typeName) {
                case 'array':
                    if (!$this->isArray($value)) {
                        throw new InvalidConfigurationException('Must be an array: '.$key);
                    }
                    break;
                case 'map':
                    if (!$this->isMap($value)) {
                        throw new InvalidConfigurationException('Must be a map: '.$key);
                    }
                    break;
                case 'string':
                    if (!$this->isString($value)) {
                        throw new InvalidConfigurationException('Must be a string: '.$key);
                    }
                    break;
                case 'bool':
                case 'boolean':
                    if (!$this->isBoolean($value)) {
                        throw new InvalidConfigurationException('Must be a boolean: '.$key);
                    }
                    break;
                default:
                    throw new InvalidConfigurationException('Unknown type: '.$typeName);
            }
        }
    }

    /**
     * Check that configuration value is a boolean if set. Otherwise, set a default value.
     *
     * @param string $key
     * @param bool   $default
     */
    protected function checkBoolean(string $key, bool $default): void
    {
        if (\array_key_exists($key, $this->options)) {
            if (!$this->isBoolean($this->options[$key])) {
                throw new InvalidConfigurationException('must be a boolean: '.$key);
            }
        } else {
            $this->options[$key] = $default;
        }
    }
}
