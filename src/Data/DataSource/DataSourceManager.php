<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\DataSource;

use App\Entity\DataSource;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DataSourceManager
{
    /** @var HttpClientInterface */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function setClient(HttpClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getData(DataSource $dataSource)
    {
        [$url, $type] = [$dataSource->getUrl(), $dataSource->getFormat()];
        $content = null;
        $response = $this->client->request('GET', $url);
        $content = $response->getContent();

        switch ($type) {
            case DataSource::FORMAT_CSV:
                $lines = explode(PHP_EOL, $content);
                // Ignore empty lines.
                $lines = array_filter(array_map('trim', $lines));
                $rows = array_map('str_getcsv', $lines);
                if (\count($rows) < 2) {
                    return [];
                }
                $headers = array_shift($rows);

                return array_map(static function ($values) use ($headers) {
                    return array_combine($headers, $values);
                }, $rows);
                break;
            case DataSource::FORMAT_JSON:
                return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            default:
                throw new \InvalidArgumentException(sprintf('Invalid data source type: %s', $type));
        }
    }
}
