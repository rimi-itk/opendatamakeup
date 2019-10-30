<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\DataTarget;

use App\Traits\OptionsTrait;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

abstract class AbstractDataTarget
{
    use LoggerTrait;
    use LoggerAwareTrait;
    use OptionsTrait;

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    abstract public function publish(array $rows, Collection $columns, array &$data);
}
