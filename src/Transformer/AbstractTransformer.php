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
use App\Data\Exception\InvalidTypeException;
use App\Transformer\Exception\InvalidConfigurationException;
use App\Transformer\Exception\InvalidArgumentException;
use App\Transformer\Exception\InvalidKeyException;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

abstract class AbstractTransformer
{
    public static $types = [
        'bool' => BooleanType::class,
        'int' => IntegerType::class,
        'float' => FloatType::class,
        'string' => StringType::class,
        'date' => DateType::class,
        'datetime' => DateTimeType::class,
    ];

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

    /**
     * Transform a data set.
     *
     * @param DataSet $input
     *
     * @return DataSet
     */
    abstract public function transform(DataSet $input): DataSet;

    /**
     * Compute columns after applying transform.
     *
     * @param array $columns
     *
     * @return array
     */
    abstract public function transformColumns(array $columns): array;

    /**
     * Set options on the transformer.
     *
     * @param array $options
     *
     * @return $this
     */
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
        return $this->metadata['options'] ?? [];
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
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist on %s.', $name, static::class));
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

    protected function isType($value): bool
    {
        return $this->isString($value) && \array_key_exists($value, static::$types);
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

    protected function getType(string $name)
    {
        if (!\array_key_exists($name, static::$types)) {
            throw new InvalidTypeException($name);
        }
        if ('int' === $name) {
            $name = 'integer';
        }

        return Type::getType($name);
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
                case 'column':
                    if (!$this->isString($value)) {
                        throw new InvalidConfigurationException('Must be a column: '.$key);
                    }
                    break;
                case 'columns':
                    if (!$this->isArray($value)) {
                        throw new InvalidConfigurationException('Must be a list of columns: '.$key);
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
                case 'type':
                    if (!$this->isType($value)) {
                        throw new InvalidConfigurationException('Must be a type: '.$key);
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
