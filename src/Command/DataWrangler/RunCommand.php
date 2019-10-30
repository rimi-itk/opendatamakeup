<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\DataWrangler;

use App\Data\DataWranglerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    protected static $defaultName = 'app:data-wrangler:run';

    /** @var DataWranglerManager */
    private $manager;

    public function __construct(DataWranglerManager $manager)
    {
        parent::__construct(null);
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->addArgument('wrangler', InputArgument::REQUIRED, 'Id of the data wrangler to run')
            ->addOption('publish', null, InputOption::VALUE_NONE, 'If set, send result to data targets')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $this->manager->setLogger($logger);

        $id = $input->getArgument('wrangler');
        $dataWrangler = $this->manager->getDataWrangler($id);
        if (null === $dataWrangler) {
            throw new InvalidArgumentException(sprintf('Invalid data wrangler: %s', $id));
        }
        $options = [
            'publish' => $input->getOption('publish'),
        ];

        $this->manager->run($dataWrangler, $options);
    }
}
