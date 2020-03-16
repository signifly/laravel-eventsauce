<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

class NoopTestProcessManager extends TestProcessManager
{
    public function handleNoopEvent($event)
    {
    }

    public function handleIncrementEvent(IncrementEvent $event)
    {
        $this->state['counter'] = ($this->state['counter'] ?? 0) + 1;
    }
}
