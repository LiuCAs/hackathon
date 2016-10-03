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

        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('AppBundle:Category')->find(1);

        $query = $em->createQuery(
            'SELECT p
               FROM AppBundle:Point p
               WHERE p.category = :id
                AND (p.lat IS NOT NULL AND p.lng IS NOT NULL)
                AND (p.lat != :lat AND p.lng != :lng)'
        )
            ->setParameter('id', 1)
            ->setParameter('lat', $category->getLat())
            ->setParameter('lng', $category->getLng());

        $points = $query->getResult();

        return $this->view(
            $points,
            Response::HTTP_OK,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
