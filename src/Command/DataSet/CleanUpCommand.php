<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\DataSet;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpCommand extends Command
{
    protected static $defaultName = 'app:dataset:clean-up';

    protected function configure()
    {
        $this->setDescription('Clean up, i.e. delete or truncate, data set tables from database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException(__METHOD__.' not yet implemented.');
    }
}
