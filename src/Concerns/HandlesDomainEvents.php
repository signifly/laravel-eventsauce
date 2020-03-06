<?php

namespace Signifly\LaravelEventSauce\Concerns;

use EventSauce\EventSourcing\Message;
use ReflectionClass;

trait HandlesDomainEvents
{
    public function handle(Message $message)
    {
        $event = $message->event();
        $method = sprintf('handle%s', (new ReflectionClass($event))->getShortName());

        if (method_exists($this, $method)) {
            $this->$method($event, $message->aggregateRootId(), $message);
        }
    }
}
