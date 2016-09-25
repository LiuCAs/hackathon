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
                'categoryName' => 'Interpelacje',
                'lat' => '52.406374',
                'long' => '16.9251681'
            ],
            [
                'city' => '3064',
                'cityName' => 'Poznan',
                'categoryId' => '2',
                'categoryName' => 'Oferty Pracy',
                'lat' => '52.406374',
                'long' => '16.9251681'
            ],
            [
                'city' => '0663',
                'cityName' => 'Lublin',
                'categoryId' => '3',
                'categoryName' => 'Interpelacje',
                'lat' => '51.2464536',
                'long' => '22.5684463'
            ],
        ];

        return $this->view($response, Response::HTTP_OK);
    }
}
