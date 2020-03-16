<?php

namespace Signifly\LaravelEventSauce\Tests;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Signifly\LaravelEventSauce\Contracts\ProcessManager;
use Signifly\LaravelEventSauce\Tests\Fixtures\Identifier;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestProcessManager;

abstract class ProcessManagerTestCase extends TestCase
{
    protected AggregateRootId $aggregateRootId;

    protected function newAggregateRootId()
    {
        return $this->aggregateRootId = new Identifier(rand());
    }

    protected function aggregateRootId()
    {
        return $this->aggregateRootId ?? $this->newAggregateRootId();
    }

    protected function dispatch(object ...$events)
    {
        $aggregateRootVersion = 1;
        $metadata = [
            Header::AGGREGATE_ROOT_ID => $this->aggregateRootId(),
        ];
        $messages = array_map(function (object $event) use ($metadata, &$aggregateRootVersion) {
            return new Message(
                $event,
                $metadata + [Header::AGGREGATE_ROOT_VERSION => ++$aggregateRootVersion]
            );
        }, $events);

        $manager = $this->manager();
        foreach ($messages as $message) {
            $manager->handle($message);
        }
    }

    protected function manager(): ProcessManager
    {
        return app(TestProcessManager::class);
    }
}
