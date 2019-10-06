<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Data;

use App\Entity\DataTransform;
use App\Transformer\TransformerManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FormHelper
{
    /** @var TransformerManager */
    private $transformerManager;

    public function __construct(TransformerManager $transformerManager)
    {
        $this->transformerManager = $transformerManager;
    }

    public function buildDataTransformForm(FormBuilderInterface $builder, ArrayCollection $columns): FormBuilderInterface
    {
        /** @var DataTransform $transform */
        $transform = $builder->getData();
        $builder
            ->add('name', TextType::class);

        $argumentsBuilder = $builder->create('transformerArguments', FormType::class, [
            //            'mapped' => false,
        ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($transform) {
                $data = $event->getData();
                $form = $event->getForm();
                $form->setData($this->transformerManager->normalizeArguments($transform->getTransformer(), $data));
//            header('content-type: text/plain'); echo var_export($data, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
            })
        ;
//        $builder->add('transformerArguments', FormType::class);
//        header('content-type: text/plain'); echo var_export($transform->getTransformerArguments(), true); die(__FILE__.':'.__LINE__.':'.__METHOD__);

        $metadata = $this->transformerManager->getTransformer($transform->getTransformer());
        foreach ($metadata->getOptions() as $name => $info) {
            $type = TextType::class;
            $options = [
                'mapped' => false,
                'required' => $info['required'],
                'help' => $info['description'] ?? null,
                'data' => $transform->getTransformerArguments()[$name] ?? $info['default'] ?? null,
            ];
            switch ($info['type']) {
                case 'bool':
                    $type = CheckboxType::class;
                    break;
                case 'date':
                    $type = DateType::class;
                    break;
                case 'datetime':
                    $type = DateTimeType::class;
                    break;
                case 'int':
                    $type = IntegerType::class;
                    break;
                case 'float':
                    break;
                case 'column':
                    $type = ChoiceType::class;
                    $options['choices'] = $columns->map(static function (Column $column) {
                        return $column->getName();
                    })->toArray();
                    break;
                case 'columns':
                    $type = ChoiceType::class;
                    $options['choices'] = $columns->map(static function (Column $column) {
                        return $column->getName();
                    })->toArray();
                    $options['multiple'] = true;
                    $options['expanded'] = true;
                    break;
                case 'type':
                    $type = ChoiceType::class;
                    $options['choices'] = DataSet::$columnTypes;
            }
            $argumentsBuilder->add($name, $type, $options);
        }

        $builder->add($argumentsBuilder);

        return $builder;
    }
}
