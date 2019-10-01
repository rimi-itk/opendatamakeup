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
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-annotations/en/1.7/custom.html
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Transform implements \JsonSerializable
{
    /**
     * @Required
     *
     * @var string
     */
    public $id;

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

    public function x__construct($values)
    {
        if (empty($values['sid'])) {
            throw new AnnotationException(sprintf());
        }
        foreach ($values['options'] as $name => $option) {
            if (!\is_string($name)) {
                throw new InvalidArgumentException(sprintf('Option name "%s" must be a string.', $name));
            }
            if (!$option instanceof Option) {
                throw new InvalidArgumentException(sprintf('Option "%s" must be an %s annotation.', $name, Option::class));
            }
        }
        // @TODO: Validate options.
        foreach ($values as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(sprintf('Property "%s" does not exist on the %s annotation.', $key, self::class));
            }
            $this->{$key} = $value;
        }
    }

    public function getOptions()
    {
        return json_decode(json_encode($this->options, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }

    public function asArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'options' => array_map(static function (Option $option) { return $option->asArray(); }, $this->options),
        ];
    }
}
