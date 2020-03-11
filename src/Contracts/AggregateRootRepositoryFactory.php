<?php

namespace Signifly\LaravelEventSauce\Contracts;

use EventSauce\EventSourcing\AggregateRootRepository;

interface AggregateRootRepositoryFactory
{
    public function build(string $aggregateRootClassName, array $consumers): AggregateRootRepository;
}
