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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DataWranglerController.
 *
 * @Route("/data_wrangler", name="data_wrangler_")
 */
class DataWranglerController extends AbstractController
{
    /** @var DataWranglerManager */
    private $dataWranglerManager;

    public function __construct(DataWranglerManager $dataWranglerManager)
    {
        $this->dataWranglerManager = $dataWranglerManager;
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
     * @param DataWrangler $dataWrangler
     *
     * @Route("/run/{id}", name="show")
     */
    public function run(DataWrangler $dataWrangler)
    {
        $this->dataWranglerManager->run($dataWrangler);
    }
}
