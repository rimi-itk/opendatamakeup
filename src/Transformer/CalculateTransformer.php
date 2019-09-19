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
use App\Transformer\Exception\InvalidCalculationException;

/**
 * @Transform(
 *     id="calculate",
 *     name="Calculate",
 *     description="Calculates (simple) stuff",
 *     options={
 *         "name": @Option(type="string"),
 *         "left": @Option(type="string"),
 *         "operator": @Option(type="string"),
 *         "right": @Option(type="string"),
 *     }
 * )
 */
class CalculateTransformer extends AbstractTransformer
{
    public const OPERATOR_ADD = '+';
    public const OPERATOR_SUBTRACT = '-';
    public const OPERATOR_MULTIPLY = '*';
    public const OPERATOR_DIVIDE = '/';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $left;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $right;

    /**
     * @param DataSet $input
     *
     * @return DataSet
     */
    public function transform(DataSet $input): DataSet
    {
        return $this->map($input, function ($item) {
            // @TODO: Check types of operands.
            $left = is_numeric($this->left) ? $this->left : $this->getValue($item, $this->left);
            $right = is_numeric($this->right) ? $this->right : $this->getValue($item, $this->right);
            $result = $this->calculate($left, $right);

            $item[$this->name] = $result;

            return $item;
        });
    }

    private function calculate($left, $right)
    {
        switch ($this->operator) {
            case static::OPERATOR_ADD:
                return $left + $right;
            case static::OPERATOR_DIVIDE:
                return $left / $right;
            case static::OPERATOR_MULTIPLY:
                return $left * $right;
            case static::OPERATOR_SUBTRACT:
                return $left - $right;
            default:
                throw new InvalidCalculationException('Invalid operator: '.$this->operator);
        }
    }
}
