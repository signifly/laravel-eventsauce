<?php

namespace Signifly\LaravelEventSauce\Tests;

use Signifly\LaravelEventSauce\Contracts\StateRepository;
use Signifly\LaravelEventSauce\DatabaseStateRepository;
use Signifly\LaravelEventSauce\ProcessId;
use Signifly\LaravelEventSauce\State;

class DatabaseStateRepositoryTest extends TestCase
{
    protected DatabaseStateRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(StateRepository::class);
    }

    /** @test */
    public function it_returns_null_when_no_state_matches()
    {
        $state = $this->repository->find(ProcessId::create(), 'test');

        $this->assertNull($state);
    }

    /** @test */
    public function it_saves_a_state_to_the_database()
    {
        $this->assertDatabaseMissing('domain_states', ['process_type' => 'test']);

        $state = new State(ProcessId::create(), 'test', 1, ['hello' => 'world']);

        $this->repository->save($state);

        $this->assertDatabaseHas('domain_states', ['process_type' => 'test']);
    }

    /** @test */
    public function it_saves_multiple_states_to_the_database()
    {
        $this->assertDatabaseMissing('domain_states', ['process_type' => 'test']);

        $state1 = new State($p1 = ProcessId::create(), 'test', 1, ['hello' => 'world']);
        $state2 = new State($p2 = ProcessId::create(), 'test', 1, ['world' => 'hello']);

        $this->repository->save($state1);
        $this->repository->save($state2);

        $this->assertDatabaseHas('domain_states', [
            'process_type' => 'test',
            'process_id' => $p1,
        ]);
        $this->assertDatabaseHas('domain_states', [
            'process_type' => 'test',
            'process_id' => $p2,
        ]);
    }

    /** @test */
    public function it_restores_a_state()
    {
        $create = new State($p1 = ProcessId::create(), 'test', 0, ['hello' => 'world']);
        $this->repository->save($create);

        $restored = $this->repository->find($p1, 'test');
        $this->assertEquals($create->toArray(), $restored->toArray());
        $this->assertEquals(1, $restored->version());
    }

    /** @test */
    public function it_restores_the_latest_version_of_a_state()
    {
        $this->assertDatabaseMissing('domain_states', ['process_type' => 'test']);

        $state = new State($p1 = ProcessId::create(), 'test', 0, ['hello' => 'world']);
        $this->repository->save($state);
        $state = $this->repository->find($p1, 'test');
        $state->hello = 'world2';
        $this->repository->save($state);
        $state = $this->repository->find($p1, 'test');
        $state->hello = 'world3';
        $this->repository->save($state);

        $restored = $this->repository->find($p1, 'test');

        $this->assertEquals($state->hello, $restored->hello);
        $this->assertEquals(3, $restored->version());
    }
}
