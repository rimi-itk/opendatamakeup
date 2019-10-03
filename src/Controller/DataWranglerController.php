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
use App\Entity\DataWrangler;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DataWranglerController.
 *
 * @Route("/data_wrangler", name="data_wrangler_")
 */
class DataWranglerController extends AdminController
{
    /** @var DataWranglerManager */
    private $dataWranglerManager;

    public function __construct(DataWranglerManager $dataWranglerManager)
    {
        $this->dataWranglerManager = $dataWranglerManager;
    }

    public function previewAction()
    {
        $id = $this->request->get('id');

        return $this->redirectToRoute('data_wrangler_preview', ['id' => $id]);
    }

    /**
     * @param DataWrangler $dataWrangler
     *
     * @Route("/show/{id}", name="show")
     */
    public function show(DataWrangler $dataWrangler)
    {
        return $this->render('data_wrangler/show.html.twig', [
            'data_wrangler' => $dataWrangler,
        ]);
    }

    /**
     * @param Request      $request
     * @param DataWrangler $dataWrangler
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/preview/{id}", name="preview")
     */
    public function preview(Request $request, DataWrangler $dataWrangler)
    {
        $dataSets = $this->dataWranglerManager->run($dataWrangler, [
            'steps' => $request->query->getInt('steps', PHP_INT_MAX),
        ]);

        return $this->render('data_wrangler/preview.html.twig', [
            'data_sets' => $dataSets,
            'data_wrangler' => $dataWrangler,
        ]);
    }
}
