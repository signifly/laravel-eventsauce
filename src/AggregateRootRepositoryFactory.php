<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;

class AggregateRootRepositoryFactory
{
    private MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function build(string $aggregateRootClassName, array $consumers): AggregateRootRepository
    {
        return new ConstructingAggregateRootRepository(
            $aggregateRootClassName,
            $this->messageRepository,
            new MessageDispatcherChain(
                new LaravelMessageDispatcher(...$consumers)
            ),
            new MessageDecoratorChain(
                new DefaultHeadersDecorator(),
                new AggregateRootTypeHeaderDecorator($aggregateRootClassName)
            )
        );
    }
}
