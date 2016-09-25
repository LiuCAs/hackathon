<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class MarkersController extends FOSRestController
{
    public function getMarkersAction(Request $request)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        $em = $this->getDoctrine();
        $entities = $em->getRepository('AppBundle:Point')->findBy(['city' => '3064', 'category' => 1]);

        return $this->view($entities, Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}
