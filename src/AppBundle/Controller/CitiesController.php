<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class CitiesController extends FOSRestController
{
    public function getCitiesAction()
    {
        $data = [
            'Poznan',
            'Warszawa'
        ];
        return $this->view($data, Response::HTTP_OK);
    }
}
