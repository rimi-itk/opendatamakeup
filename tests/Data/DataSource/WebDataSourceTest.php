<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data\DataSource;

use App\Data\DataSource\WebDataSource;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WebDataSourceTest extends KernelTestCase
{
    public function testGetData()
    {
        $path = __DIR__.'/fixtures/data.json';
        $url = 'file://'.$path;
        $format = 'json';
        $dataSource = new WebDataSource($url, $format);
        $expected = json_decode(file_get_contents($path), true);
        $actual = $dataSource->getData();

        $this->assertSame($actual, $expected);
    }
}
