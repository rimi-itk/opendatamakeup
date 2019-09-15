<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer\Exception;

class InvalidKeyException extends AbstractTransformerException
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $value;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return InvalidKeyException
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param array $value
     *
     * @return InvalidKeyException
     */
    public function setValue(array $value): self
    {
        $this->value = $value;

        return $this;
    }
}
