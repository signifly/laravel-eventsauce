<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Signifly\LaravelEventSauce\Console\GenerateCommand;
use Signifly\LaravelEventSauce\Console\ReplayCommand;
use Signifly\LaravelEventSauce\Contracts\AggregateRootRepositoryFactory as AggregateRootRepositoryFactoryContract;
use Signifly\LaravelEventSauce\Contracts\StateRepository;

class EventSauceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            ReplayCommand::class,
            GenerateCommand::class,
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'eventsauce');

        $this->app->bind(MessageRepository::class, function (Container $container) {
            $repository = new DatabaseMessageRepository(
                $container->make('db'),
                config('eventsauce.domain_messages_table', 'domain_messages'),
                $container->make(MessageSerializer::class)
            );

            $repository->setConnection(config('eventsauce.message_database_connection'));

            return $repository;
        });

        $this->app->bind(StateRepository::class, function (Container $container) {
            $repository = new DatabaseStateRepository(
                $container->make('db'),
                config('eventsauce.state_messages_table', 'state_messages')
            );

            $repository->setConnection(config('eventsauce.state_database_connection'));

            return $repository;
        });

        $this->app->bind(MessageSerializer::class, ConstructingMessageSerializer::class);
        $this->app->bind(AggregateRootRepositoryFactoryContract::class, AggregateRootRepositoryFactory::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishMigrations();
        }
    }

    public function provides()
    {
        return [
            ReplayCommand::class,
            GenerateCommand::class,
        ];
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('eventsauce.php'),
        ], 'config');
    }

    protected function publishMigrations(): void
    {
        $this->publishDomainMessagesMigration();
        $this->publishDomainStatesMigration();
    }

    protected function publishDomainMessagesMigration(): void
    {
        if (class_exists('CreateDomainMessagesTable')) {
            return;
        }

        $tableName = config('eventsauce.domain_messages_table', 'domain_messages');
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../stubs/create_domain_messages_table.php.stub' => database_path("/migrations/{$timestamp}_create_{$tableName}_table.php"),
        ], 'migrations');
    }

    protected function publishDomainStatesMigration(): void
    {
        if (class_exists('CreateStateMessagesTable')) {
            return;
        }

        $tableName = config('eventsauce.domain_states_table', 'domain_states');
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../stubs/create_domain_states_table.php.stub' => database_path("/migrations/{$timestamp}_create_{$tableName}_table.php"),
        ], 'migrations');
    }
}
