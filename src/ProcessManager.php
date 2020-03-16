<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Message;
use Illuminate\Support\Collection;
use Signifly\LaravelEventSauce\Contracts\ProcessManager as Contract;
use Signifly\LaravelEventSauce\Contracts\StateRepository;
use Signifly\LaravelEventSauce\Contracts\WithProcessingHooks;

abstract class ProcessManager extends EventConsumer implements Contract
{
    protected ProcessId $processId;
    protected State $state;
    protected StateRepository $repository;
    protected string $stateClass = State::class;

    public function __construct(StateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(Message $message): void
    {
        $event = $message->event();
        $method = $this->getQualifiedMethodFor($event);

        if (! method_exists($this, $method)) {
            return;
        }

        $this->processMessage($message, $message->aggregateRootId());
    }

    protected function processMessage(Message $message, AggregateRootId $aggregateRootId): void
    {
        $type = (new DotSeparatedSnakeCaseInflector())->instanceToType($this);
        $processIds = $this->resolveProcessId($aggregateRootId, $message->event());

        $processIds->each(function (ProcessId $processId) use ($type, $message) {
            $this->processId = $processId;
            $this->state = $this->repository->find($this->processId, $type) ?? $this->newState($this->processId, $type);

            // Trigger processing hook and return if the return value is false
            if ($this instanceof WithProcessingHooks && $this->processing($message) === false) {
                return;
            }

            parent::handle($message);

            $this->repository->save($this->state);

            // Trigger processed hook
            if ($this instanceof WithProcessingHooks) {
                $this->processed($message);
            }
        });
    }

    protected function resolveProcessId(AggregateRootId $aggregateRootId, object $event): Collection
    {
        return collect([ProcessId::aggregateRootId($aggregateRootId)]);
    }

    protected function newState(ProcessId $processId, string $type): State
    {
        return new $this->stateClass($processId, $type);
    }
}
