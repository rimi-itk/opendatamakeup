<?php

/*
 * This file is part of opendata/datamakeup.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Transformer\TransformerManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/documentation", name="documentation_")
 */
class DocumentationController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('documentation/index.html.twig', [
            'controller_name' => 'DocumentationController',
        ]);
    }

    /**
     * @Route("/transformers", name="transformers")
     */
    public function transformers(TransformerManager $transformerManager)
    {
        $transformers = $transformerManager->getTransformers();

//        foreach ($transformers as $id => $transformer) {
//            $io->newLine();
//            $io->section($transformer['name']);
//            $io->writeln([
//                $transformer['description'],
//                '',
//                'Id:        '.$id,
//                'Alias:     '.$transformer['id'],
//                'Arguments: ',
//            ]);
//            $io->table([
//                'name',
//                'type',
//                'required',
//                'default',
//                'description',
//            ], array_map(static function ($option) {
//                return [
//                    $option['name'],
//                    $option['type'],
//                    $option['required'] ? 'yes' : 'no',
//                    \is_bool($option['default']) ? var_export($option['default'], true) : $option['default'],
//                    $option['description'],
//                ];
//            }, $transformer['options']));
//        }

        return $this->render('documentation/transformers.html.twig', [
            'transformers' => $transformers,
        ]);
    }
}
