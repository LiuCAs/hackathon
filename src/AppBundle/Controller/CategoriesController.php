<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class CategoriesController extends FOSRestController
{
    public function getCategoriesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Point')->findAll();

        return $this->view($entities, Response::HTTP_OK);
    }

    public function getCategoryAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Point')->findBy(['category' => $id]);

        return $this->view($entities, Response::HTTP_OK);
    }
}
