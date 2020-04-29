<?php

namespace Signifly\LaravelEventSauce\Contracts;

use EventSauce\EventSourcing\Message;
use Illuminate\Contracts\Container\Container;

interface ConsumerHandler
{
    /**
     * Create a new Consumer instance.
     *
     * @param mixed|string $consumer
     * @param Message[] $messages
     */
    public function __construct(string $consumer, Message ...$messages);

    public function handle(Container $container): void;
}
