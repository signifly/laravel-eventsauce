<?php

namespace Signifly\LaravelEventSauce;

use Signifly\LaravelEventSauce\Contracts\ShouldReset;

abstract class Projector extends EventConsumer implements ShouldReset
{
    public function resetState(): void
    {
    }
}
