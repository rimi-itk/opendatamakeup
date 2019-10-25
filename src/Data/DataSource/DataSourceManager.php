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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DataSourceManager
{
    /** @var HttpClientInterface */
    private $client;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(HttpClientInterface $client, PropertyAccessorInterface $propertyAccessor)
    {
        $this->client = $client;
        $this->propertyAccessor = $propertyAccessor;
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
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

                $root = $dataSource->getJsonRoot();
                if (null !== $root) {
                    $propertyPath = (static function ($spec) {
                        return '['.implode('][', preg_split('/\./', $spec)).']';
                    })($root);
                    $data = $this->propertyAccessor->getValue($data, $propertyPath);
                }

                return $data;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid data source type: %s', $type));
        }
    }
}
