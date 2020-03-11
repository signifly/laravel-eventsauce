<?php

namespace Signifly\LaravelEventSauce\Tests;

use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\InMemoryMessageRepository;
use Signifly\LaravelEventSauce\AggregateRootRepositoryFactory;
use Signifly\LaravelEventSauce\Tests\Fixtures\Identifier;
use Signifly\LaravelEventSauce\Tests\Fixtures\Model;

class AggregateRootRepositoryFactoryTest extends TestCase
{
    /** @test **/
    public function it_builds_an_aggregate_root_repository()
    {
        $memoryRepository = new InMemoryMessageRepository();
        $factory = new AggregateRootRepositoryFactory($memoryRepository);

        $aggregateRootRepository = $factory->build(Model::class, []);
        $model = $aggregateRootRepository->retrieve(new Identifier(1));

        $this->assertInstanceOf(ConstructingAggregateRootRepository::class, $aggregateRootRepository);
        $this->assertInstanceOf(Model::class, $model);
    }
}
