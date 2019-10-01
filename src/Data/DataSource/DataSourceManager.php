<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data\DataSource;

use App\Data\DataSet;
use App\Data\DataSetManager;

class DataSourceManager
{
    /** @var DataSetManager */
    private $dataSetManager;

    public function __construct(DataSetManager $dataSetManager)
    {
        $this->dataSetManager = $dataSetManager;
    }

    public function createDataSet(string $name, AbstractDataSource $dataSource): DataSet
    {
        $data = $dataSource->getData();

        return $this->dataSetManager->createDataSetFromData($name, $data);
    }
}
