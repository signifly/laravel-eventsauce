<?php

namespace Signifly\LaravelEventSauce\Tests\Concerns;

use Signifly\LaravelEventSauce\Concerns\IgnoresMissingMethods;
use Signifly\LaravelEventSauce\Tests\TestCase;

class IgnoresMissingMethodsTest extends TestCase
{
    /** @test */
    public function it_will_make_objects_ignore_missing_methods()
    {
        $class = new class {
            use IgnoresMissingMethods;
        };

        $class->nonExistingMethod();

        $this->markTestPassed();
    }
}
