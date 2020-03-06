<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class LaravelMessageDispatcher implements MessageDispatcher
{
    /**
     * @var array|string[]
     */
    private array $consumers;

    public function __construct(string ...$consumers)
    {
        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages)
    {
        foreach ($this->consumers as $consumer) {
            if (is_a($consumer, ShouldQueue::class, true)) {
                dispatch(new HandleConsumer($consumer, ...$messages));
            } else {
                dispatch_now(new HandleConsumer($consumer, ...$messages));
            }
        }
    }
}
