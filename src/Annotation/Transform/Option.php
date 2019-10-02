<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Annotation\Transform;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-annotations/en/1.7/custom.html
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class Option implements \JsonSerializable
{
    /**
     * @Required
     *
     * @Enum({"column", "columns", "string", "map", "bool", "int", "type"})
     *
     * @var string
     */
    public $type;

    /**
     * Name to use in stead of property name.
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $required = true;

    /**
     * Default value.
     *
     * @var mixed
     */
    public $default;

    public function jsonSerialize()
    {
        return $this->asArray();
    }

    public function asArray()
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            'default' => $this->default,
        ];
    }
}
