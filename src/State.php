<?php

namespace Signifly\LaravelEventSauce;

use Illuminate\Support\Fluent;
use Signifly\LaravelEventSauce\Contracts\ProcessId;
use Signifly\LaravelEventSauce\Contracts\State as Contract;

class State extends Fluent implements Contract
{
    private ProcessId $processId;
    private string $type;
    private int $version;

    public function __construct(ProcessId $processId, string $type, int $version = 0, $attributes = [])
    {
        $this->processId = $processId;
        $this->type = $type;
        $this->version = $version;

        parent::__construct($attributes);
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function processId(): ProcessId
    {
        return $this->processId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function version(): int
    {
        return $this->version;
    }
}
