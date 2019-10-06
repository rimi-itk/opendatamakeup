<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Data\DataWranglerManager;
use App\Data\FormHelper;
use App\Entity\DataTransform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DataTransformController.
 *
 * @Route("/data_transform", name="data_transform_")
 */
class DataTransformController extends AbstractController
{
    /** @var DataWranglerManager */
    private $dataWranglerManager;

    /** @var FormHelper */
    private $formHelper;

    public function __construct(DataWranglerManager $dataWranglerManager, FormHelper $formHelper)
    {
        $this->dataWranglerManager = $dataWranglerManager;
        $this->formHelper = $formHelper;
    }

    /**
     * @Route("/{id}/edit", name="edit")
     */
    public function edit(Request $request, DataTransform $transform, EntityManagerInterface $entityManager)
    {
        $results = $this->dataWranglerManager->run($transform->getDataWrangler(), ['steps' => $transform->getPosition() + 2]);
        $dataSet = $results[\count($results) - 2];
        $columns = $dataSet->getColumns();

        $builder = $this->createFormBuilder($transform);

        $this->formHelper->buildDataTransformForm($builder, $columns);

        $builder
            ->add('preview', SubmitType::class, ['label' => 'Preview'])
            ->add('save', SubmitType::class, ['label' => 'Save']);

        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('preview')->isClicked()) {
                $results = $this->dataWranglerManager->run($transform->getDataWrangler(), ['steps' => $transform->getPosition() + 2]);
            } else {
                $entityManager->persist($transform);
                $entityManager->flush();
                $this->addFlash('success', 'Transform saved.');

                return $this->redirectToRoute(
                    'data_wrangler_preview',
                    ['id' => $transform->getDataWrangler()->getId()]
                );
            }
        }

        return $this->render('data/transform/edit.html.twig', [
            'transform' => $transform,
            'results' => $results,
            'form' => $form->createView(),
        ]);
    }
}
