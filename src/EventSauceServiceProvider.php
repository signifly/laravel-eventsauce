<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Signifly\LaravelEventSauce\Console\GenerateCommand;
use Signifly\LaravelEventSauce\Console\ReplayCommand;

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

        $this->app->bind(MessageRepository::class, function (Container $container) {
            return new DatabaseMessageRepository(
                $container->make('db'),
                'domain_messages',
                $container->make(MessageSerializer::class)
            );
        });

        $this->app->bind(MessageSerializer::class, ConstructingMessageSerializer::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    public function provides()
    {
        return [
            ReplayCommand::class,
            GenerateCommand::class,
        ];
    }
}
