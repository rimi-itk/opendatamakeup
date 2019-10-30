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
use App\Data\DataTarget\DataTargetManager;
use App\Data\Exception\TransformRuntimeException;
use App\Entity\DataWrangler;
use App\Repository\DataWranglerRepository;
use App\Traits\LogTrait;
use App\Transformer\Exception\AbstractTransformerException;
use App\Transformer\TransformerManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class DataWranglerManager
{
    use LogTrait;

    /** @var DataSourceManager */
    protected $dataSourceManager;

    /** @var DataSetManager */
    protected $dataSetManager;

    /** @var TransformerManager */
    protected $transformerManager;

    /** @var DataTargetManager */
    protected $dataTargetManager;

    /** @var DataWranglerRepository */
    protected $repository;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(DataSourceManager $dataSourceManager, DataSetManager $dataSetManager, TransformerManager $transformerManager, DataTargetManager $dataTargetManager, DataWranglerRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->dataSourceManager = $dataSourceManager;
        $this->dataSetManager = $dataSetManager;
        $this->transformerManager = $transformerManager;
        $this->dataTargetManager = $dataTargetManager;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function getDataWrangler(string $id): ?DataWrangler
    {
        return $this->repository->find($id);
    }

    /**
     * @return DataSourceManager
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
            try {
                $transformer = $this->transformerManager->getTransformer(
                    $transform->getTransformer(),
                    $transform->getTransformerArguments()
                );
                $dataSet = $transformer->transform($dataSet)->setTransform($transform);
                $results[] = $dataSet;
            } catch (AbstractTransformerException $exception) {
                if ($options['return_exceptions'] ?? false) {
                    $results[] = (new TransformRuntimeException('Data wrangler run failed', $exception->getCode(), $exception))
                        ->setTransform($transform);
                    break;
                } else {
                    throw $exception;
                }
            }
        }

        // Publish result only if running all transforms.
        if ((!isset($options['steps']) || $dataWrangler->getTransforms()->count() === $options['steps'])
            && $options['publish']) {
            $result = end($results);
            $this->publish($result, $dataWrangler->getDataTargets());
        }

        return $results;
    }

    private function publish(DataSet $result, Collection $dataTargets)
    {
        $rows = $result->getRows();
        $this->dataTargetManager->setLogger($this->logger);
        foreach ($dataTargets as $dataTarget) {
            $this->debug(sprintf('publish: %s', $dataTarget));
            $target = $this->dataTargetManager->getDataTarget($dataTarget->getDataTarget(), $dataTarget->getDataTargetOptions());
            $target->setLogger($this->logger);
            $data = $dataTarget->getData() ?? [];
            $target->publish($rows, $result->getColumns(), $data);
            $dataTarget->setData($data);
            $this->entityManager->persist($dataTarget);
            $this->entityManager->flush();
        }
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
