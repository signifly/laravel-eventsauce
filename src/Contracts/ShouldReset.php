<?php

namespace Signifly\LaravelEventSauce\Contracts;

interface ShouldReset
{
    public function resetState(): void;
}
