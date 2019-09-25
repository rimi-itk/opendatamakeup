<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Transformer;

use App\Annotation\Transform;
use App\Annotation\Transform\Option;
use App\Data\DataSet;

/**
 * @Transform(
 *     id="change_type",
 *     name="Change type",
 *     description="Change type of a column",
 *     options={
 *         "column": @Option(type="string"),
 *         "type": @Option(type="type")
 *     }
 * )
 */
class ChangeTypeTransformer extends AbstractTransformer
{
    public function transform(DataSet $input): DataSet
    {
        // @TODO Check that new type is different from current type.
        // @TODO Check that type change makes sense without data loss.
        foreach ($this->names as $name) {
        }
    }
}
