<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Tests\Data\DataSource;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DataSourceMockHttpClient extends MockHttpClient
{
    public function __construct($responseFactory = null, string $baseUri = null)
    {
        parent::__construct([$this, 'callback']);
    }

    public function callback($method, $url, $options)
    {
        if ('GET' === $method) {
            return $this->get($url, $options);
        }

        throw new \RuntimeException(sprintf('Invalid request method: %s', $method));
    }

    private function get($url, $options)
    {
        $path = __DIR__.'/fixtures'.parse_url($url, PHP_URL_PATH);

        return new MockResponse(file_get_contents($path));
    }
}
