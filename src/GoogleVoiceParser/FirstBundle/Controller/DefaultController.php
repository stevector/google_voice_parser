<?php

namespace GoogleVoiceParser\FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('GoogleVoiceParserFirstBundle:Default:index.html.twig', array('name' => $name));
    }
}
