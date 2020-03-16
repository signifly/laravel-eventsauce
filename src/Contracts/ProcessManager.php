<?php

namespace Signifly\LaravelEventSauce\Contracts;

use EventSauce\EventSourcing\Message;

interface ProcessManager
{
    public function handle(Message $message): void;
}
