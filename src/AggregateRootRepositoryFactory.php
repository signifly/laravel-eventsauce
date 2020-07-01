<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;
use Signifly\LaravelEventSauce\Contracts\AggregateRootRepositoryFactory as Contract;

class AggregateRootRepositoryFactory implements Contract
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
                ...$this->resolveDefaultDecorators($aggregateRootClassName),
                ...$this->resolveCustomDecorators()
            )
        );
    }

    protected function resolveDefaultDecorators(string $aggregateRootClassName): array
    {
        return [
            new DefaultHeadersDecorator(),
            new AggregateRootTypeHeaderDecorator($aggregateRootClassName),
        ];
    }

    protected function resolveCustomDecorators(): array
    {
        $customDecorators = config('eventsauce.custom_decorators', []);

        if (! is_array($customDecorators)) {
            throw new \InvalidArgumentException('Custom decorators must be an array.');
        }

        return array_map(function ($customDecorator) {
            if (! is_a($customDecorator, MessageDecorator::class, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid decorator provided. Must be an instance of %s', MessageDecorator::class)
                );
            }

            return app($customDecorator);
        }, $customDecorators);
    }
}
