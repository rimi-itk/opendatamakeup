<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\DataSetManager;
use App\Transformer\TransformerManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class TransformTest extends KernelTestCase
{
    public function test(): void
    {
        self::bootKernel();

        // @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;
        $manager = $container->get(TransformerManager::class);
        $dataSetManager = $container->get(DataSetManager::class);

        $dir = __DIR__.'/Tests';
        $filenames = glob($dir.'/test*.yaml');

        foreach ($filenames as $filename) {
            $content = file_get_contents($filename);
            $data = Yaml::parse($content);
            $inputFilename = \dirname($filename).'/'.$data['input'];
            $expectedFilename = \dirname($filename).'/'.$data['expected'];
            $input = $dataSetManager->createDataSetFromCSV(static::class, file_get_contents($inputFilename));
            $expected = $dataSetManager->createDataSetFromCSV(static::class.'_expected', file_get_contents($expectedFilename));

            $actual = $manager->runTransformers($input, $data['transforms']);

            $this->assertEquals($expected->getColumns(), $actual->getColumns(), 'columns '.$filename);
            $this->assertEquals($expected->rows(), $actual->rows(), 'items '.$filename);
        }
    }
}
