<?php


namespace AppBundle\EventSubscriber;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class GeoSubscriber implements EventSubscriberInterface
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT'])
            ? strtolower($_SERVER['HTTP_USER_AGENT'])
            : '';

        $is_bot = preg_match(
            "~(google|yahoo|rambler|bot|yandex|spider|snoopy|crawler|finder|mail|curl)~i",
            $userAgent
        );

        if(!$is_bot){

            $ip = $event->getRequest()->getClientIp();
            if ($ip == 'unknown') {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            if ($ip == '127.0.0.1') {
                $geo = ['city' => 'Москва','lat'=>'55.753410','lon'=>'37.620285'];
                $event->getRequest()->getSession()->set('geo', $geo);

            } else {

                $sessId = $event->getRequest()->getSession()->getId();

                if ($event->getRequest()->getSession()->has('ip')) {
                    $sessIP = $event->getRequest()->getSession()->get('ip');
                } else {
                    $event->getRequest()->getSession()->set('ip', $ip);
                    $event->getRequest()->getSession()->set('sessId', $sessId);
                    $get = file_get_contents('http://ip-api.com/json/' . $ip . '?lang=ru');
                    if ($get) {
                        $geo = json_decode($get, true);
                    } else {
                        $geo = ['city' => 'Москва','lat'=>'55.753410','lon'=>'37.620285'];
                    }
                    $event->getRequest()->getSession()->set('geo', $geo);
                    $event->getRequest()->getSession()->getFlashBag()->add('notice', 'Ваш город был определен как ' . $geo['city']);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }

}