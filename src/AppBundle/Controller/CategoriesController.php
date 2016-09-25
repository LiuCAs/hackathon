<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class CategoriesController extends FOSRestController
{
    public function getCategoryAction($id)
    {
        $defaultCords = [
            '1' => [
                'lat' => '52.406374',
                'lng' => '16.9251681'
            ],
            '2' => [
                'lat' => '52.406374',
                'lng' => '16.9251681'
            ],
            '3' => [
                'lat' => '51.2464536',
                'lng' => '22.5684463'
            ],
        ];
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT p
               FROM AppBundle:Point p
               WHERE p.category = :id
                AND (p.lat IS NOT NULL AND p.lng IS NOT NULL)
                AND (p.lat != :lat AND p.lng != :lng)'
        )
            ->setParameter('id', $id)
            ->setParameter('lat', $defaultCords[$id]['lat'])
            ->setParameter('lng', $defaultCords[$id]['lng']);

        $points = $query->getResult();


        return $this->view($points, Response::HTTP_OK);
    }
}
