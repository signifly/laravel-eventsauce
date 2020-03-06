<?php

namespace Signifly\LaravelEventSauce\Tests;

use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Signifly\LaravelEventSauce\AggregateRootTypeHeaderDecorator;
use Signifly\LaravelEventSauce\Tests\Fixtures\Model;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestEvent;

class AggregateRootTypeHeaderDecoratorTest extends TestCase
{
    /** @test **/
    public function it_adds_aggregate_root_type_header()
    {
        $testEvent = new TestEvent(1);
        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => get_class($testEvent),
        ];
        $message = new Message($testEvent, $headers);
        $decorator = new AggregateRootTypeHeaderDecorator(Model::class);

        $decoratedMessage = $decorator->decorate($message);

        $aggregateRootType = (new DotSeparatedSnakeCaseInflector)->classNameToType(Model::class);
        $this->assertCount(3, $decoratedMessage->headers());
        $this->assertEquals($aggregateRootType, $decoratedMessage->header(AggregateRootTypeHeaderDecorator::AGGREGATE_ROOT_TYPE));
    }
}
