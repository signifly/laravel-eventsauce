<?php

namespace Signifly\LaravelEventSauce\Tests;

use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\InMemoryMessageRepository;
use Signifly\LaravelEventSauce\AggregateRootRepositoryFactory;
use Signifly\LaravelEventSauce\Contracts\AggregateRootRepositoryFactory as AggregateRootRepositoryFactoryContract;
use Signifly\LaravelEventSauce\Tests\Fixtures\Identifier;
use Signifly\LaravelEventSauce\Tests\Fixtures\Model;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestEvent;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestFactory;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestMessageDecorator;

class AggregateRootRepositoryFactoryTest extends TestCase
{
    /** @test */
    public function it_resolves_from_the_container()
    {
        $factory = $this->app->make(AggregateRootRepositoryFactoryContract::class);

        $this->assertInstanceOf(AggregateRootRepositoryFactory::class, $factory);
    }

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

    /** @test */
    public function it_resolves_custom_decorators()
    {
        config(['eventsauce.custom_decorators' => [
            TestMessageDecorator::class,
        ]]);

        $memoryRepository = new InMemoryMessageRepository();
        $factory = new TestFactory($memoryRepository);

        $customDecorators = $factory->getResolvedCustomDecorators();

        $this->assertInstanceOf(TestMessageDecorator::class, $customDecorators[0]);
    }

    /** @test */
    public function it_fails_to_resolve_custom_decorators_without_valid_interface()
    {
        $this->expectException(\InvalidArgumentException::class);

        config(['eventsauce.custom_decorators' => [
            TestEvent::class,
        ]]);

        $memoryRepository = new InMemoryMessageRepository();
        $factory = new TestFactory($memoryRepository);

        $customDecorators = $factory->getResolvedCustomDecorators();

        $this->fail('Should have thrown an InvalidArgumentException');
    }
}
