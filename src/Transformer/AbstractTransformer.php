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
use App\Data\Table;
use App\Transformer\Exception\InvalidConfigurationException;
use App\Transformer\Exception\InvalidArgumentException;
use App\Transformer\Exception\InvalidKeyException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ApcuCache;

abstract class AbstractTransformer
{
    /**
     * @var Transform
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $configuration;

    public function __construct()
    {
        AnnotationRegistry::registerLoader('class_exists');

        $reader = new CachedReader(
            new AnnotationReader(),
            new ApcuCache(),
            $debug = true
        );
        $annotation = $reader->getClassAnnotation(new \ReflectionClass($this), Transform::class);
        if (null === $annotation) {
            throw new InvalidConfigurationException(sprintf('Annotation @%s missing on class %s', Transform::class, self::class));
        }
        $this->metadata = $annotation;
    }

    abstract public function transform(Table $input): Table;

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        $this->validateAndApplyConfiguration();

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

    public function getOptions()
    {
        return $this->metadata->getOptions();
    }

    protected function validateAndApplyConfiguration()
    {
        foreach ($this->metadata->options as $name => $option) {
            if ($option->required) {
                $this->requireKey($name);
            }
            $this->checkType($name, $option->type);
            $configurationName = $option->name ?? $name;
            if (!property_exists($this, $name)) {
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist on %s.', $name, self::class));
            }
            $property = new \ReflectionProperty($this, $name);
            $property->setAccessible(true);
            if (\array_key_exists($configurationName, $this->configuration)) {
                $property->setValue($this, $this->configuration[$configurationName]);
            } elseif (isset($option->default)) {
                $property->setValue($this, $option->default);
            }
        }
    }

    /**
     * Map table items.
     *
     * @param Table    $table
     * @param callable $callback
     *
     * @return Table
     */
    protected function map(Table $table, callable $callback): Table
    {
        return $table->map($callback);
    }

    protected function filter(Table $table, callable $callback): Table
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
                throw (new InvalidKeyException())
                    ->setKey($key)
                    ->setValue($value);
            }
            $value = $value[$key];
        }

        return $value;
    }

    protected function requireKey(string $key): void
    {
        if (!\array_key_exists($key, $this->configuration)) {
            throw new InvalidConfigurationException('missing configuration: '.$key);
        }
    }

    protected function requireArray(string $key): void
    {
        $this->requireKey($key);

        if (!$this->isArray($this->configuration[$key])) {
            throw new InvalidConfigurationException('must be an array: '.$key);
        }
    }

    protected function requireMap(string $key): void
    {
        $this->requireKey($key);

        if (!$this->isMap($this->configuration[$key])) {
            throw new InvalidConfigurationException('must be an map (associative array): '.$key);
        }
    }

    protected function requireString(string $key): void
    {
        $this->requireKey($key);

        if (!$this->isString($this->configuration[$key])) {
            throw new InvalidConfigurationException('must be a string: '.$key);
        }
    }

    protected function checkType(string $key, string $typeName): void
    {
        if (\array_key_exists($key, $this->configuration)) {
            $value = $this->configuration[$key];
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
        if (\array_key_exists($key, $this->configuration)) {
            if (!$this->isBoolean($this->configuration[$key])) {
                throw new InvalidConfigurationException('must be a boolean: '.$key);
            }
        } else {
            $this->configuration[$key] = $default;
        }
    }
}
