<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\Transformer;

use App\Transformer\TransformerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocCommand extends Command
{
    protected static $defaultName = 'app:transformer:doc';

    /** @var TransformerManager */
    private $transformerManager;

    public function __construct(TransformerManager $transformerManager)
    {
        parent::__construct();
        $this->transformerManager = $transformerManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Transforms');

        $transformers = $this->transformerManager->getTransformers();

        foreach ($transformers as $id => $transformer) {
            $io->newLine();
            $io->section($transformer['name']);
            $io->writeln([
                $transformer['description'],
                '',
                'Id:        '.$id,
                'Alias:     '.$transformer['id'],
                'Arguments: ',
            ]);
            $io->table([
                'name',
                'type',
                'required',
                'default',
                'description',
            ], array_map(static function ($option) {
                return [
                    $option['name'],
                    $option['type'],
                    $option['required'] ? 'yes' : 'no',
                    \is_bool($option['default']) ? var_export($option['default'], true) : $option['default'],
                    $option['description'],
                ];
            }, $transformer['options']));
        }
    }
}
