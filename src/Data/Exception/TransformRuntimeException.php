<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\Exception;

use App\Entity\DataTransform;

class TransformRuntimeException extends \RuntimeException
{
    /** @var */
    private $transform;

    public function getTransform(): ?DataTransform
    {
        return $this->transform;
    }

    public function setTransform(DataTransform $transform)
    {
        $this->transform = $transform;

        return $this;
    }
}
