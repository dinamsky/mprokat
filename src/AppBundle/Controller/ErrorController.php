<?php

namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ErrorController extends Controller
{
    private $env;

    public function __construct()
    {
        //var_dump($debug);
    }

    public function doMailShow(\Swift_Mailer $mailer){
        $msg = (new \Swift_Message('Ошибка в системе'))
                ->setFrom('mail@multiprokat.com')
                ->setTo('wqs-info@mail.ru')
                ->setBody('Ошибка','text/html');
        $mailer->send($msg);
        //return new Response();
    }
}