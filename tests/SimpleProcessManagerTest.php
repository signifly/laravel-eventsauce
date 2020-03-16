<?php

namespace Signifly\LaravelEventSauce\Tests;

use Signifly\LaravelEventSauce\Contracts\ProcessManager;
use Signifly\LaravelEventSauce\Contracts\StateRepository;
use Signifly\LaravelEventSauce\ProcessId;
use Signifly\LaravelEventSauce\State;
use Signifly\LaravelEventSauce\Tests\Fixtures\IncrementEvent;
use Signifly\LaravelEventSauce\Tests\Fixtures\NoopEvent;
use Signifly\LaravelEventSauce\Tests\Fixtures\NoopTestProcessManager;
use Signifly\LaravelEventSauce\Tests\Fixtures\UnhandledNoopEvent;

class SimpleProcessManagerTest extends ProcessManagerTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $stateMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->newAggregateRootId();
        $this->stateMock = $this->mock(StateRepository::class);
    }

    /** @test */
    public function it_does_not_call_find_if_no_handle_method_exists_for_the_triggered_event()
    {
        $this->stateMock->shouldReceive('find')
            ->never();

        $this->dispatch(new UnhandledNoopEvent());
    }

    /** @test */
    public function it_retrieves_the_state_from_repository()
    {
        $this->stateMock->shouldReceive('find')
            ->withArgs(function ($id, $type) {
                return $id == ProcessId::aggregateRootId($this->aggregateRootId())
                    && $type === 'signifly.laravel_event_sauce.tests.fixtures.noop_test_process_manager';
            })
            ->once();
        $this->stateMock->shouldReceive('save')
            ->withAnyArgs()->once();

        $this->dispatch(new NoopEvent());
    }

    /** @test */
    public function it_saves_an_empty_state_to_repository()
    {
        $this->stateMock->shouldReceive('find')->once();
        $this->stateMock->shouldReceive('save')
            ->withArgs(fn (State $state) => $state->toArray() == [])
            ->once();

        $this->dispatch(new NoopEvent());
    }

    /** @test */
    public function it_saves_state_change_to_repository()
    {
        $this->stateMock->shouldReceive('find')->once();
        $this->stateMock->shouldReceive('save')
            ->withArgs(fn (State $state) => $state->toArray() == ['counter' => 1])
            ->once();

        $this->dispatch(new IncrementEvent());
    }

    protected function manager(): ProcessManager
    {
        return app(NoopTestProcessManager::class);
    }
}
