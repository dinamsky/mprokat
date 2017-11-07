<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\City;
use Doctrine\ORM\EntityManagerInterface as em;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Cookie;

class GeoSubscriber implements EventSubscriberInterface
{

    public function __construct(em $em)
    {
        $this->em = $em;
    }

    public function onKernelController(FilterControllerEvent $event)
    {


        $userAgent = isset($_SERVER['HTTP_USER_AGENT'])
            ? strtolower($_SERVER['HTTP_USER_AGENT'])
            : '';

        $is_bot = preg_match(
            "~(google|yahoo|rambler|bot|yandex|spider|snoopy|crawler|finder|mail|curl)~i",
            $userAgent
        );

        $default = true;

        if (!$is_bot) {
            if(!$event->getRequest()->getSession()->has('city')) {
                $ip = $event->getRequest()->getClientIp();
                if ($ip == 'unknown') {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                if ($ip != '127.0.0.1') {
                    $get = file_get_contents('http://ip-api.com/json/' . $ip . '?lang=ru');
                    if ($get) {
                        $geo = json_decode($get, true);
                        if(isset($geo['city'])) {
                            $city = $this->em->getRepository("AppBundle:City")->createQueryBuilder('c')
                                ->where('c.header LIKE :geoname')
                                ->andWhere('c.parentId IS NOT NULL')
                                ->setParameter('geoname', '%' . $geo['city'] . '%')
                                ->getQuery()
                                ->getResult();
                            if ($city) {
                                $event->getRequest()->getSession()->set('city', $city[0]);
                                $default = false;
                            }
                        }
                    }
                }
            } else $default = false;
        }

        if($default){
            $city = $this->em
                ->getRepository(City::class)
                ->find(77);
            $event->getRequest()->getSession()->set('city', $city);
        }

        if (isset($city)){
            if(is_array($city)) $cookie_data = $city[0]->getId();
            else $cookie_data = $city->getId();
        }
        else $cookie_data = $event->getRequest()->getSession()->get('city')->getId();
            $event->getRequest()->attributes->set('cookie_data', $cookie_data);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = new Response();
        $request = $event->getRequest();



            $cookie_data = $request->attributes->get('cookie_data');
            $cookie = new Cookie('multiprokat_geo', $cookie_data, strtotime('now +1 year'));
            //dump($cookie);
            $response->headers->setCookie($cookie);
            $response->send();
            //dump($response->headers);
        //return $response;

    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'onKernelResponse'
        );
    }

}