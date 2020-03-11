<?php

namespace Signifly\LaravelEventSauce\Tests\Fixtures;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

class TestMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        return $message->withHeader('__test', 'test');
    }
}
