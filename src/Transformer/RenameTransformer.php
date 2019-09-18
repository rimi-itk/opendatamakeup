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
use App\Data\Exception\InvalidNameException;
use App\Data\DataSource;
use App\Transformer\Exception\InvalidKeyException;

/**
 * @Transform(
 *     id="rename",
 *     name="Rename",
 *     description="Renames a column",
 *     options={
 *         "from": @Option(type="string"),
 *         "to": @Option(type="string")
 *     }
 * )
 */
class RenameTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @param DataSource $input
     *
     * @return DataSource
     *
     * @throws InvalidNameException
     */
    public function transform(DataSource $input): DataSource
    {
        $columns = $input->getColumns();
        if (\array_key_exists($this->to, $columns)) {
            throw new InvalidKeyException(sprintf('Name "%s" already exists', $this->to));
        }

        return $this->map($input, function ($item) {
            $value = $this->getValue($item, $this->from);
            unset($item[$this->from]);
            $item[$this->to] = $value;

            return $item;
        });
    }
}