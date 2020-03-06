<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

class AggregateRootTypeHeaderDecorator implements MessageDecorator
{
    private string $aggregateRootType;

    public function __construct(string $aggregateRootClassName, ClassNameInflector $classNameInflector = null)
    {
        $this->aggregateRootType = ($classNameInflector ?: new DotSeparatedSnakeCaseInflector())
            ->classNameToType($aggregateRootClassName);
    }

    public function decorate(Message $message): Message
    {
        return $message->withHeaders([
            '__aggregate_root_type' => $this->aggregateRootType,
        ]);
    }
}
