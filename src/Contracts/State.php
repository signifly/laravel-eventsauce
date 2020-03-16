<?php

namespace Signifly\LaravelEventSauce\Contracts;

interface State
{
    public function attributes(): array;

    public function processId(): ProcessId;

    public function type(): string;

    public function version(): int;
}
