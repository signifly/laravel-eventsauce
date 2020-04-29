<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Signifly\LaravelEventSauce\Contracts\WithConsumerHandler;

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
            $jobClass = is_a($consumer, WithConsumerHandler::class, true)
                ? $consumer::getConsumerHandler()
                : HandleConsumer::class;

            $job = new $jobClass($consumer, ...$messages);
            if (is_a($consumer, ShouldQueue::class, true)) {
                dispatch($job);
            } else {
                dispatch_now($job);
            }
        }
    }
}
