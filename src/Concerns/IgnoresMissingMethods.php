<?php

namespace Signifly\LaravelEventSauce\Concerns;

trait IgnoresMissingMethods
{
    public function __call($name, $arguments)
    {
    }
}
