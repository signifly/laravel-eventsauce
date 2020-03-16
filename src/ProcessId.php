<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use Illuminate\Support\Str;
use Signifly\LaravelEventSauce\Contracts\ProcessId as Contract;

class ProcessId implements Contract
{
    private string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function aggregateRootId(AggregateRootId $aggregateRootId): self
    {
        return self::fromString($aggregateRootId->toString());
    }

    public function toString(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromString(string $processId): self
    {
        return new self($processId);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public static function create()
    {
        return new self(Str::random(6));
    }
}
