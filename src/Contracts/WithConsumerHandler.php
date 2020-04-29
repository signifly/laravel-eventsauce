<?php

namespace Signifly\LaravelEventSauce\Contracts;

use EventSauce\EventSourcing\Message;

interface WithConsumerHandler
{
    public static function getConsumerHandler(Message ...$messages): ConsumerHandler;
}
