<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

use EventSauce\EventSourcing\AggregateRootId;

class Identifier implements AggregateRootId
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return (string) $this->id;
    }

    /**
     * @inheritDoc
     */
    public static function fromString(string $aggregateRootId): AggregateRootId
    {
        return new static((int) $aggregateRootId);
    }
}
