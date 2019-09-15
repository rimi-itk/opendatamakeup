<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Annotation\Transform;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-annotations/en/1.7/custom.html
 *
 * @Annotation
 */
class Option
{
    /**
     * @Required
     *
     * @Enum({"string", "array", "map", "bool", "int"})
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
}
