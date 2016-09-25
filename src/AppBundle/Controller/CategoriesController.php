<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class CategoriesController extends FOSRestController
{
    public function getCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('AppBundle:Category')->find($id);

        $query = $em->createQuery(
            'SELECT p
               FROM AppBundle:Point p
               WHERE p.category = :id
                AND (p.lat IS NOT NULL AND p.lng IS NOT NULL)
                AND (p.lat != :lat AND p.lng != :lng)'
        )
            ->setParameter('id', $id)
            ->setParameter('lat', $category->getLat())
            ->setParameter('lng', $category->getLng());

        $points = $query->getResult();


        return $this->view($points, Response::HTTP_OK);
    }
}
