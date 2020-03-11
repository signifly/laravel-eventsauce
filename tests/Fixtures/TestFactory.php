<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

use Signifly\LaravelEventSauce\AggregateRootRepositoryFactory;

class TestFactory extends AggregateRootRepositoryFactory
{
    public function getResolvedCustomDecorators()
    {
        return $this->resolveCustomDecorators();
    }
}
