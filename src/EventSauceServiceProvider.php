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
            return new DatabaseMessageRepository(
                $container->make('db'),
                config('eventsauce.domain_messages_table', 'domain_messages'),
                $container->make(MessageSerializer::class)
            );
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
            $this->publishMigration();
        }
    }

    public function provides()
    {
        return [
            ReplayCommand::class,
            GenerateCommand::class,
        ];
    }

    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('eventsauce.php'),
        ], 'config');
    }

    private function publishMigration(): void
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
}
