<?php

namespace Signifly\LaravelEventSauce\Contracts;

interface StateRepository
{
    public function find(ProcessId $processId, string $type): ?State;

    public function save(State $state);
}
