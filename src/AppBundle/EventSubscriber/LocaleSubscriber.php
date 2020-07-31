<?php

// src/AppBundle/EventSubscriber/LocaleSubscriber.php
namespace AppBundle\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;
    private $env;

    public function __construct($defaultLocale = 'en', KernelInterface $kernel)
    {
        $this->defaultLocale = $defaultLocale;
        $this->env = $kernel->getEnvironment();
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $ip = $event->getRequest()->getClientIp();

        if($this->env == 'dev' and $ip == '127.0.0.1'){
            $_SERVER['LANG'] = 'ru';
        }

        if(!isset($_SERVER['LANG'])) $_SERVER['LANG'] = 'ru';

        $request->setLocale($_SERVER['LANG']);


            $translator = new Translator('en');

            $translator->addLoader('pofile', new PoFileLoader());
            $translator->addResource('pofile', 'messages.en.po', 'en');
            $translator->addResource('pofile', 'messages.ru.po', 'ru');

    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}