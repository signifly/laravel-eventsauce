<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

class AggregateRootTypeHeaderDecorator implements MessageDecorator
{
    const AGGREGATE_ROOT_TYPE = '__aggregate_root_type';

    protected string $aggregateRootType;

    public function __construct(string $aggregateRootClassName, ClassNameInflector $classNameInflector = null)
    {
        $this->aggregateRootType = ($classNameInflector ?: new DotSeparatedSnakeCaseInflector())
            ->classNameToType($aggregateRootClassName);
    }

    public function decorate(Message $message): Message
    {
        return $message->withHeaders([
            self::AGGREGATE_ROOT_TYPE => $this->aggregateRootType,
        ]);
    }
}
