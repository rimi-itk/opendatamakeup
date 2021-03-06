<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Traits;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

trait LogTrait
{
    use LoggerAwareTrait;
    use LoggerTrait;

    public function log($level, $message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
