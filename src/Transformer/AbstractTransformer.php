<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Transformer\Exception\InvalidConfigurationException;
use App\Transformer\Exception\InvalidKeyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractTransformer
{
    /**
     * @var array
     */
    protected $configuration;

    abstract public function transform(array $input): array;

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;
        $this->validateConfiguration();

        return $this;
    }

    /**
     * Validate that the configuration is valid.
     *
     * @throws InvalidConfigurationException mixed
     */
    abstract public function validateConfiguration(): void;

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
                throw new InvalidKeyException($key, $value);
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

    protected function setConfigurationKey(string $key, $value)
    {
        $this->configuration[$key] = $value;
    }

    protected function setDefault($key, $value)
    {
        if (!\array_key_exists($key, $this->configuration)) {
            $this->configuration[$key] = $value;
        }
    }
}
