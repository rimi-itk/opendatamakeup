<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data;

use Doctrine\ORM\EntityManagerInterface;

class DataSetManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createDataSet(string $name, array $columns, array $items = []): DataSet
    {
        $dataSet = new DataSet($name, $columns);
        $dataSet->setEntityManager($this->entityManager);
        if (null !== $items) {
            $dataSet->createTable()->loadData($items);
        }

        return $dataSet;
    }

    public function createDataSetFromCSV(string $name, $csv, array $headers = null)
    {
        $dataSet = new DataSet($name);
        $dataSet->setEntityManager($this->entityManager);
        $dataSet->buildFromCSV($csv, $headers);

        return $dataSet;
    }

    public function createDataSetFromData(array $items)
    {
    }
}
