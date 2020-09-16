<?php

namespace EasyWordpressBundle\EventListener;

use EasyWordpressBundle\Service\WordpressHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerArgumentsSubscriber implements EventSubscriberInterface
{
    private $helper;

    public function __construct(WordpressHelper $helper)
    {
        $this->helper = $helper;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->helper->boot();

        $globals = ['wp_query', 'wp', 'wpdb'];
        foreach ($globals as $global) {
            $event->getRequest()->attributes->set($global, $GLOBALS[$global]);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}