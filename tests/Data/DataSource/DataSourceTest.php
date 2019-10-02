<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data\DataSource;

use App\Entity\DataSource;
use App\Tests\ContainerTestCase;

class DataSourceTest extends ContainerTestCase
{
    public function testGetData()
    {
        $this->dataSourceManager()->setClient(new DataSourceMockHttpClient());

        $path = 'data.json';
        $url = 'http://test/'.$path;
        $type = 'json';
        $dataSource = (new DataSource())
            ->setUrl($url)
            ->setType($type);

        $path = __DIR__.'/fixtures/'.$path;

        $expected = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $actual = $this->dataSourceManager()->getData($dataSource);

        $this->assertSame($actual, $expected);
    }
}
