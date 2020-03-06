<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as AggregateRootRepositoryContract;

abstract class AggregateRootRepository implements AggregateRootRepositoryContract
{
    /** @var string */
    protected $aggregateRoot = null;

    /** @var string[] */
    protected array $consumers = [];

    protected AggregateRootRepositoryContract $aggregateRootRepository;

    protected AggregateRootRepositoryFactory $aggregateRootRepositoryFactory;

    public function __construct(AggregateRootRepositoryFactory $aggregateRootRepositoryFactory)
    {
        $this->aggregateRootRepositoryFactory = $aggregateRootRepositoryFactory;

        if (! is_a($this->aggregateRootClass(), AggregateRoot::class, true)) {
            throw new \Exception('Not a valid aggregateRoot');
        }
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->aggregateRootRepository()->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot)
    {
        $this->aggregateRootRepository()->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->aggregateRootRepository()->persistEvents($aggregateRootId, $aggregateRootVersion, $events);
    }

    protected function aggregateRootRepository(): AggregateRootRepositoryContract
    {
        if (! isset($this->aggregateRootRepository)) {
            $this->aggregateRootRepository = $this->aggregateRootRepositoryFactory->build(
                $this->aggregateRootClass(),
                $this->consumers
            );
        }

        return $this->aggregateRootRepository;
    }

    protected function aggregateRootClass(): string
    {
        return $this->aggregateRoot;
    }
}
