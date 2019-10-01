<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data;

use App\Entity\DataSource;
use App\Entity\DataWrangler;

class DataWranglerManager
{
    /** @var DataSetManager */
    protected $dataSetManager;

    public function __construct(DataSetManager $dataSetManager)
    {
        $this->dataSetManager = $dataSetManager;
    }

    public function run(DataWrangler $dataWrangler)
    {
        $dataSets = $this->getDataSets($dataWrangler);
    }

    /**
     * Get dataSets indexed by id.
     *
     * @param DataWrangler $dataWrangler
     *
     * @return array
     */
    protected function getDataSets(DataWrangler $dataWrangler)
    {
        $dataSets = [];

        foreach ($dataWrangler->getDataSources() as $dataSource) {
            $data = $this->getData($dataSource);
            $dataSet = $this->dataSetManager->createDataSetFromData($data);
            $dataSets[$dataSource->getId()] = $dataSet;
        }

        return $dataSets;
    }

    protected function getData(DataSource $dataSource)
    {
        [$url, $type] = [$dataSource->getUrl(), $dataSource->getType()];
        $content = null;
        if (preg_match('@^file://(?P<path>.+)@', $url, $matches)) {
            $path = $matches['path'];
            if (!file_exists($path)) {
                return null;
            }
            $content = file_get_contents($path);
        } else {
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET', $url);

            if (200 !== $response->getStatusCode()) {
                return null;
            }
            try {
                $content = $response->getContent();
            } catch (ExceptionInterface $e) {
                return null;
            }
        }

        switch ($type) {
            case DataSource::TYPE_CSV:
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
            case DataSource::TYPE_JSON:
                return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            default:
                throw new \InvalidArgumentException(sprintf('Invalid data source type: %s', $type));
        }
    }
}
