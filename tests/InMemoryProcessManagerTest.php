<?php

namespace Signifly\LaravelEventSauce\Tests;

use Illuminate\Support\Collection;
use Signifly\LaravelEventSauce\Contracts\ProcessManager;
use Signifly\LaravelEventSauce\Contracts\StateRepository;
use Signifly\LaravelEventSauce\InMemoryStateRepository;
use Signifly\LaravelEventSauce\Tests\Fixtures\IncrementEvent;
use Signifly\LaravelEventSauce\Tests\Fixtures\NoopEvent;
use Signifly\LaravelEventSauce\Tests\Fixtures\NoopTestProcessManager;
use Signifly\LaravelEventSauce\Tests\Fixtures\UnhandledNoopEvent;

class InMemoryProcessManagerTest extends ProcessManagerTestCase
{
    /** @var StateRepository $state */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->instance(StateRepository::class, new InMemoryStateRepository);
    }

    /** @test */
    public function state_is_untouched_by_unhandled_event()
    {
        $this->assertEmpty($this->repository->state());

        $this->dispatch(new UnhandledNoopEvent());

        $this->assertEmpty($this->repository->state());
    }

    /** @test */
    public function it_saves_an_empty_state_to_repository()
    {
        $this->assertEmpty($this->repository->state());

        $this->dispatch(new NoopEvent());

        $this->assertNotEmpty($this->repository->state());
    }

    /** @test */
    public function it_saves_state_change_to_repository()
    {
        $this->assertEmpty($this->repository->state());

        $this->dispatch(new IncrementEvent());

        $state = $this->repository->state();

        $this->assertEquals(['counter' => 1], $state->first()->toArray());
    }

    /** @test */
    public function it_updates_a_single_state_when_multiple_changes_to_repository()
    {
        $this->assertEmpty($this->repository->state());

        $this->dispatch(new IncrementEvent(), new IncrementEvent(), new IncrementEvent());

        $state = $this->repository->state();

        $this->assertEquals(['counter' => 3], $state->first()->toArray());
    }

    /** @test */
    public function it_can_work_on_two_states_at_the_same_time()
    {
        $this->assertEmpty($this->repository->state());

        $aggr1 = $this->aggregateRootId();
        $this->dispatch(new IncrementEvent());
        $aggr2 = $this->newAggregateRootId();
        $this->dispatch(new IncrementEvent(), new IncrementEvent());
        $aggr3 = $this->newAggregateRootId();
        $this->dispatch(new IncrementEvent(), new IncrementEvent(), new IncrementEvent());
        $this->aggregateRootId = $aggr1;
        $this->dispatch(new IncrementEvent());

        /** @var Collection $state */
        $state = $this->repository->state();
        $actual = $state->mapWithKeys(fn ($v, $k) => [explode('.', $k)[1] => $v]);

        $this->assertEquals([
            $aggr1->toString() => ['counter' => 2],
            $aggr2->toString() => ['counter' => 2],
            $aggr3->toString() => ['counter' => 3],
        ], $actual->toArray());
    }

    protected function manager(): ProcessManager
    {
        return app(NoopTestProcessManager::class);
    }
}
