<?php

// src/AppBundle/EventSubscriber/TokenSubscriber.php
namespace AppBundle\EventSubscriber;



use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class GeoSubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {

        $is_bot = preg_match(
            "~(Google|Yahoo|Rambler|Bot|Yandex|Spider|Snoopy|Crawler|Finder|Mail|curl)~i",
            $_SERVER['HTTP_USER_AGENT']
        );

        if(!$is_bot){

            $ip = $event->getRequest()->getClientIp();
            if ($ip == 'unknown') {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            if ($ip == '127.0.0.1') $ip = '94.41.250.18';

            $sessId = $event->getRequest()->getSession()->getId();

            if ($event->getRequest()->getSession()->has('ip')) {
                $sessIP = $event->getRequest()->getSession()->get('ip');
            } else {
                $event->getRequest()->getSession()->set('ip', $ip);
                $event->getRequest()->getSession()->set('sessId', $sessId);
                $geo = json_decode(file_get_contents('http://ip-api.com/json/'.$ip.'?lang=ru'), true);
                $event->getRequest()->getSession()->set('geo', $geo);
                $event->getRequest()->getSession()->getFlashBag()->add('notice', 'Your city:'.$geo['city']);
            }
        }

        //dump($event->getRequest());
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

}