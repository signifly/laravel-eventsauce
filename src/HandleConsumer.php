<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleConsumer implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var array|Message[]
     */
    protected array $messages;

    /**
     * @var mixed|string
     */
    protected $consumer;

    /**
     * Create a new Consumer instance.
     *
     * @param mixed|string $consumer
     * @param Message[] $messages
     */
    public function __construct(string $consumer, Message ...$messages)
    {
        $this->consumer = $consumer;
        $this->messages = $messages;
    }

    public function handle(Container $container)
    {
        /** @var Consumer $consumer */
        $consumer = $container->make($this->consumer);

        foreach ($this->messages as $message) {
            $consumer->handle($message);
        }
    }
}
