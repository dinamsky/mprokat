<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;



class ExceptionListener
{
    var $mailer;
    protected $container;


    protected $templating;

    public function __construct(\Swift_Mailer $mailer,ContainerInterface $container, EngineInterface $templating)
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

            }

            if ($exception->getStatusCode() == 500){

//                $msg = (new \Swift_Message('Ошибка 500 в системе'))
//                    ->setFrom('mail@multiprokat.com')
//                    ->setTo('wqs-info@mail.ru')
//                    ->setBody($message,'text/html');
//                $this->mailer->send($msg);

                $templating = $this->container->get('templating');

                $city = $event->getRequest()->getSession()->get('city');
                $response = new Response($templating->render('TwigBundle:Exception:error500.html.twig', array(
                    'city' => $city,
                    'lang' => 'ru'
                )));
            }

        } else {
            $templating = $this->container->get('templating');

            $city = $event->getRequest()->getSession()->get('city');
            $response = new Response($templating->render('TwigBundle:Exception:error500.html.twig', array(
                'city' => $city,
                'lang' => 'ru'
            )));

        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}