<?php

namespace Signifly\LaravelEventSauce;

use Carbon\Carbon;
use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Signifly\LaravelEventSauce\Concerns\HandlesDomainEvents;

abstract class EventConsumer implements Consumer
{
    use HandlesDomainEvents;

    protected function timeFrom(Message $message): Carbon
    {
        return Carbon::parse($message->header(Header::TIME_OF_RECORDING));
    }
}
