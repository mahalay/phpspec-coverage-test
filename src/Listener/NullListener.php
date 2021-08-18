<?php

namespace Mahalay\PhpSpec\CoverageTest\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NullListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [];
    }
}
