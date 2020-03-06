<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class TestEvent implements SerializablePayload
{
    public int $number;

    public function __construct(int $number) {
        $this->number = $number;
    }

    public function amount(): int
    {
        return $this->number;
    }

    public static function fromPayload(array $payload): SerializablePayload
    {
        return new self(
            (int) $payload['amount']
        );
    }

    public function toPayload(): array
    {
        return [
            'amount' => (int) $this->number,
        ];
    }
}
