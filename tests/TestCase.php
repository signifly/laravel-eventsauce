<?php

namespace Signifly\LaravelEventSauce\Tests;

use CreateDomainMessagesTable;
use CreateDomainStatesTable;
use Orchestra\Testbench\TestCase as Orchestra;
use Signifly\LaravelEventSauce\EventSauceServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        if (! class_exists('CreateDomainMessagesTable')) {
            include_once __DIR__.'/../stubs/create_domain_messages_table.php.stub';
        }

        if (! class_exists('CreateDomainStatesTable')) {
            include_once __DIR__.'/../stubs/create_domain_states_table.php.stub';
        }

        (new CreateDomainMessagesTable())->up();
        (new CreateDomainStatesTable())->up();
    }

    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }

    public function getTemporaryDirectory(): TemporaryDirectory
    {
        return (new TemporaryDirectory('tests/temp'))->force()->empty();
    }

    protected function getStubPath(string $path): string
    {
        return __DIR__."/stubs/{$path}";
    }

    protected function markTestPassed()
    {
        $this->assertTrue(true);
    }
}
