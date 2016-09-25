<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class ConfigController extends FOSRestController
{

    public function getConfigAction()
    {
        $em = $this->getDoctrine();
        $entities = $em->getRepository('AppBundle:Category')->findAll();

        return $this->view($entities, Response::HTTP_OK);
    }
}
