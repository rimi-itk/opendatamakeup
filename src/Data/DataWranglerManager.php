<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data;

use App\Data\DataSource\DataSourceManager;
use App\Entity\DataWrangler;
use App\Transformer\TransformerManager;

class DataWranglerManager
{
    /** @var DataSourceManager */
    protected $dataSourceManager;

    /** @var DataSetManager */
    protected $dataSetManager;

    /** @var TransformerManager */
    protected $transformerManager;

    public function __construct(DataSourceManager $dataSourceManager, DataSetManager $dataSetManager, TransformerManager $transformerManager)
    {
        $this->dataSourceManager = $dataSourceManager;
        $this->dataSetManager = $dataSetManager;
        $this->transformerManager = $transformerManager;
    }

    /**
     * @return DataSetManager
     */
    public function getDataSourceManager(): DataSourceManager
    {
        return $this->dataSourceManager;
    }

    /**
     * @param DataWrangler $dataWrangler
     * @param array        $options
     *
     * @return DataSet[]
     */
    public function run(DataWrangler $dataWrangler, array $options = [])
    {
        if ($dataWrangler->getDataSources()->count() > 1) {
            throw new \RuntimeException('More than one data source not yet supported.');
        }

        $dataSets = $this->getDataSets($dataWrangler);
        $dataSet = reset($dataSets);

        $results = [$dataSet];
        $steps = $options['steps'] ?? PHP_INT_MAX;
        foreach ($dataWrangler->getTransforms() as $index => $transform) {
            if ($index >= $steps - 1) {
                break;
            }
            $transformer = $this->transformerManager->getTransformer(
                $transform->getTransformer(),
                $transform->getTransformerArguments()
            );
            $dataSet = $transformer->transform($dataSet)->setTransform($transform);
            $results[] = $dataSet;
        }

        return $results;
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

        foreach ($dataWrangler->getDataSources() as $index => $dataSource) {
            $data = $this->dataSourceManager->getData($dataSource);
            $dataSet = $this->dataSetManager->createDataSetFromData($dataWrangler->getId().'_'.$index, $data);
            $dataSets[$dataSource->getId()] = $dataSet;
        }

        return $dataSets;
    }
}
