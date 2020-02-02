<?php

namespace AdminBundle\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use AdminBundle\Controller\AuthenticatedController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    protected $session;

    function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof AuthenticatedController) {
            if ($this->session->get('admin') === null) {
                // throw new AccessDeniedHttpException('This action needs a valid token!');
                $event->setController(function() {
                    return new RedirectResponse('/admin/login');
                });
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
