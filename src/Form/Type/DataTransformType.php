<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form\Type;

use App\Entity\DataTransform;
use App\Transformer\TransformerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class DataTransformType extends AbstractType
{
    /** @var TransformerManager */
    private $transformerManager;

    public function __construct(TransformerManager $transformerManager, RouterInterface $router)
    {
        $this->transformerManager = $transformerManager;
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformerChoices = array_flip(array_map(function ($transformer) {
            return $transformer['name'];
        }, $this->transformerManager->getTransformers()));
        $transformerChoices = array_merge(['' => ''], $transformerChoices);

        $transformerId = uniqid('transformer', false);
        $builder
            ->add('name', TextType::class)
//            ->add('description', TextareaType::class)
            ->add('transformer', ChoiceType::class, [
                'choices' => $transformerChoices,
                'attr' => [
                    'data-transformer-id' => $transformerId,
                ],
            ])
            ->add('transformerArguments', TransformerArgumentsType::class, [
                'required' => true,
                'attr' => [
                    'data-transformer-id' => $transformerId,
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data instanceof DataTransform) {
                $form->add('edit', ButtonType::class, [
                    'attr' => [
                        'data-edit-url' => $this->router->generate('data_transform_edit', [
                            'id' => $data->getId(),
                            'referer' => '',
                        ]),
                    ],
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DataTransform::class,
        ]);
    }
}
