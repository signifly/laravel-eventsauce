<?php

namespace Signifly\LaravelEventSauce\Contracts;

use EventSauce\EventSourcing\Message;

interface WithProcessingHooks
{
    public function processing(Message $message);

    public function processed(Message $message);
}
