<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class IncrementEvent implements SerializablePayload
{
    public function toPayload(): array
    {
        return [];
    }

    public static function fromPayload(array $payload): SerializablePayload
    {
        return new self;
    }
}
