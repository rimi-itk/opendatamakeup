<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Transformer;

use App\Data\DataSet;
use App\Transformer\Manager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class TransformTest extends KernelTestCase
{
    public function test()
    {
        self::bootKernel();

        // @see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;
        $manager = $container->get(Manager::class);

        $dir = __DIR__.'/Tests';
        $filenames = glob($dir.'/test*.yaml');

        foreach ($filenames as $filename) {
            $content = file_get_contents($filename);
            $data = Yaml::parse($content);
            $inputFilename = \dirname($filename).'/'.$data['input'];
            $expectedFilename = \dirname($filename).'/'.$data['expected'];
            $input = DataSet::buildFromCSV(file_get_contents($inputFilename));
            $expected = DataSet::buildFromCSV(file_get_contents($expectedFilename));

            $actual = $manager->runTransformers($input, $data['transforms']);

            $this->assertSame($expected->getColumns(), $actual->getColumns(), 'columns '.$filename);
            $this->assertSame($expected->getItems(), $actual->getItems(), 'items '.$filename);
        }
    }
}
