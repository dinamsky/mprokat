<?php

namespace AppBundle\EventListener;

use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;



class ExceptionListener
{
    var $mailer;
    protected $container;


    protected $templating;

    public function __construct(\Swift_Mailer $mailer,ContainerInterface $container, TwigEngine $templating)
    {
        $this->mailer = $mailer;
        $this->container = $container;
        $this->templating = $templating;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        $message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );

//        $msg = (new \Swift_Message('Ошибка в системе'))
//                ->setFrom('mail@multiprokat.com')
//                ->setTo('wqs-info@mail.ru')
//                ->setBody($message,'text/html');
//        $this->mailer->send($msg);

        // Customize your response object to display the exception details
        $response = new Response();
        $response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());

            if ($exception->getStatusCode() == 404){

                $templating = $this->container->get('templating');

                $city = $event->getRequest()->getSession()->get('city');
                $response = new Response($templating->render('TwigBundle:Exception:error404.html.twig', array(
                    'city' => $city,
                    'lang' => 'ru'
                )));

                //$event->setResponse($response);
            }


        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $msg = (new \Swift_Message('Ошибка 500 в системе'))
                    ->setFrom('mail@multiprokat.com')
                    ->setTo('wqs-info@mail.ru')
                    ->setBody($message,'text/html');
            $this->mailer->send($msg);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}