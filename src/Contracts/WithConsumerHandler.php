<?php

namespace Signifly\LaravelEventSauce\Contracts;

interface WithConsumerHandler
{
    public static function getConsumerHandler(): string;
}
