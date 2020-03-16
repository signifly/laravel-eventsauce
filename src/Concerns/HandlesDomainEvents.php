<?php

namespace Signifly\LaravelEventSauce\Concerns;

use EventSauce\EventSourcing\Message;
use ReflectionClass;

trait HandlesDomainEvents
{
    public function handle(Message $message): void
    {
        $event = $message->event();
        $method = $this->getQualifiedMethodFor($event);

        if (method_exists($this, $method)) {
            $this->$method($event, $message->aggregateRootId(), $message);
        }
    }

    /**
     * @param object $event
     * @return string
     * @throws \ReflectionException
     */
    protected function getQualifiedMethodFor(object $event): string
    {
        return sprintf('handle%s', (new ReflectionClass($event))->getShortName());
    }
}
