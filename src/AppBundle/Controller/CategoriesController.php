<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class CategoriesController extends FOSRestController
{
    public function getCategoriesAction()
    {
        $data = [
            "praca", "zarządzenia prezydenta", "zarządzenia rady miasta", "przetargi", "pozwolenia na budowe"
        ];
        return $this->view($data, Response::HTTP_OK);
    }
}
