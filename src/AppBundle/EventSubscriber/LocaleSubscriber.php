<?php

// src/AppBundle/EventSubscriber/LocaleSubscriber.php
namespace AppBundle\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

            $request->setLocale('en');

        if(!isset($_SERVER['LANG'])) $_SERVER['LANG'] = 'en';

        $translator = new Translator('ru', new MessageSelector());

        $translator->addLoader('pofile', new PoFileLoader());
        $translator->addResource('pofile', 'messages.en.po', 'en');

    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}