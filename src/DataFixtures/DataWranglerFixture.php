<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\DataFixtures;

use App\Entity\DataWrangler;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class DataWranglerFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected $entityClass = DataWrangler::class;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            DataSourceFixture::class,
        ];
    }
}
