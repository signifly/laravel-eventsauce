<?php

namespace Signifly\LaravelEventSauce;

use Illuminate\Support\Collection;
use Signifly\LaravelEventSauce\Contracts\ProcessId as ProcessIdContract;
use Signifly\LaravelEventSauce\Contracts\State as StateContract;
use Signifly\LaravelEventSauce\Contracts\StateRepository;

class InMemoryStateRepository implements StateRepository
{
    /** @var Collection */
    protected $states;

    public function __construct()
    {
        $this->states = collect();
    }

    public function find(ProcessIdContract $processId, string $type): ?State
    {
        return $this->states->get($this->getKeyFor($processId, $type));
    }

    public function save(StateContract $state)
    {
        return $this->states->put($this->getKeyFor($state->processId(), $state->type()), $state);
    }

    public function state(): Collection
    {
        return $this->states;
    }

    private function getKeyFor(ProcessIdContract $processId, string $type): string
    {
        return str_replace('.', '-', $type).'.'.$processId->toString();
    }
}
