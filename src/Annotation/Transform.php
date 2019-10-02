<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

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
        $options = [];
        foreach ($this->options as $name => $option) {
            $options[$name] = array_merge($option->asArray(), ['name' => $name]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'options' => $options,
        ];
    }
}
