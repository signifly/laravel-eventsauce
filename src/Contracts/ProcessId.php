<?php

namespace Signifly\LaravelEventSauce\Contracts;

interface ProcessId
{
    public function toString(): string;

    /**
     * @return static
     */
    public static function fromString(string $processId): self;

    public function __toString();
}
