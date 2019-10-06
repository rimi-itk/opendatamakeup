<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class TransformerArgumentsType extends CodeEditorType implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value ? Yaml::dump($value, PHP_INT_MAX, 4) : '';
    }

    public function reverseTransform($value)
    {
        try {
            return Yaml::parse($value);
        } catch (\Exception $ex) {
            throw new TransformationFailedException($ex->getMessage());
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addViewTransformer($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'transformer_name' => null,
            'language' => 'yaml',
        ]);
    }
}
