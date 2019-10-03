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
use App\DataFixtures\DataWranglerFixture;
use App\Entity\DataSource;
use App\Tests\ContainerTestCase;
use App\Tests\Data\DataSource\DataSourceMockHttpClient;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class DataWranglerTest extends ContainerTestCase
{
    /** @var \App\Data\DataWranglerManager */
    private $dataWranglerManager;

    /** @var DataWranglerFixture */
    private $fixture;

    protected function setUp(): void
    {
        $this->dataWranglerManager = $this->dataWranglerManager();
        $this->dataWranglerManager->getDataSourceManager()->setClient(new DataSourceMockHttpClient());

        $this->fixture = $this->get(DataWranglerFixture::class);
        $this->fixture->setReferenceRepository(new ReferenceRepository($this->get(ObjectManager::class)));
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $filename
     */
    public function test($filename): void
    {
        $content = file_get_contents($filename);
        $data = Yaml::parse($content);
        $expected = $this->buildExpected($data);

        $dataWrangler = $this->fixture->buildEntity($data);
        $result = $this->dataWranglerManager->run($dataWrangler);
        /** @var DataSet $actual */
        $actual = end($result);

        $this->assertEquals($expected->getColumns(), $actual->getColumns(), 'columns '.$filename);
//        $this->assertEquals($expected->rows(), $actual->rows(), 'items '.$filename);
    }

    private function buildExpected(array &$data)
    {
        $expectedData = $data['expected'];
        unset($data['expected']);

        $expectedDataSource = $this->fixture->buildEntity($expectedData, DataSource::class);
        $expectedData = $this->dataWranglerManager->getDataSourceManager()->getData($expectedDataSource);

        return $this->dataSetManager()->createDataSetFromData('expected', $expectedData);
    }

    public function dataProvider()
    {
        $dir = __DIR__.'/Tests';
        $filenames = glob($dir.'/test*.yaml');

        return array_map(static function ($filename) { return [$filename]; }, $filenames);
    }
}
