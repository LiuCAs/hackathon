<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;

class MarkersController extends FOSRestController
{
    public function getMarkersAction(Request $request)
    {
        $content = $request->getContent();

        if (!empty($content)) {
            $params = json_decode($content, true); // 2nd param to get as array
            var_dump($params);die;
        }

        return $this->view([], Response::HTTP_OK);
    }
}
