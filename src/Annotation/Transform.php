<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Annotation;

use App\Annotation\Transform\Option;
use App\Transformer\Exception\InvalidArgumentException;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-annotations/en/1.7/custom.html
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Transform
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;

    /**
     * @Required
     *
     * @var string
     */
    public $description;

    /**
     * @Required
     *
     * @var array
     */
    public $options;

    public function __construct($options = [])
    {
        foreach ($options['options'] as $name => $option) {
            if (!\is_string($name)) {
                throw new InvalidArgumentException(sprintf('Option name "%s" must be a string.', $name));
            }
            if (!$option instanceof Option) {
                throw new InvalidArgumentException(sprintf('Option "%s" must be an %s annotation.', $name, Option::class));
            }
        }
        // @TODO: Validate options.
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist on the %s annotation.', $key, self::class));
            }
            $this->{$key} = $value;
        }
    }

    public function getOptions()
    {
        return json_decode(json_encode($this->options), true);
    }
}
