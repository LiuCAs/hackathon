<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class ConfigController extends FOSRestController
{

    public function getConfigAction()
    {
        $response = [
            [
                'city' => '3064',
                'cityName' => 'Poznan',
                'categoryId' => '1',
                'categoryName' => 'Interpelacje'
            ],
            [
                'city' => '3064',
                'cityName' => 'Poznan',
                'categoryId' => '2',
                'categoryName' => 'Oferty Pracy'
            ],
            [
                'city' => '0663',
                'cityName' => 'Lublin',
                'categoryId' => '3',
                'categoryName' => 'Interpelacje'
            ],
        ];

        return $this->view($response, Response::HTTP_OK);
    }
}
