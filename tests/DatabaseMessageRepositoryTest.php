<?php

namespace Signifly\LaravelEventSauce\Tests;

use DateTimeImmutable;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\PointInTime;
use Illuminate\Support\Facades\DB;
use Signifly\LaravelEventSauce\DatabaseMessageRepository;
use Signifly\LaravelEventSauce\Tests\Fixtures\Identifier;
use Signifly\LaravelEventSauce\Tests\Fixtures\TestEvent;

class DatabaseMessageRepositoryTest extends TestCase
{
    private MessageRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(MessageRepository::class);
    }

    /** @test */
    public function it_resolves_from_container()
    {
        $this->assertInstanceOf(DatabaseMessageRepository::class, $this->repository);
    }

    /** @test **/
    public function it_can_store_a_message()
    {
        $testEvent = new TestEvent(1);

        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => get_class($testEvent),
            Header::AGGREGATE_ROOT_ID => 'aggregate-root-id',
            Header::AGGREGATE_ROOT_ID_TYPE => 'aggregate-root-id-type',
            Header::TIME_OF_RECORDING => PointInTime::fromDateTime(new DateTimeImmutable())->toString(),
        ];

        $message = new Message($testEvent, $headers);

        $this->repository->persist($message);

        $storedDomainMessage = DB::table('domain_messages')->first();

        $this->assertEquals($headers[Header::EVENT_ID], $storedDomainMessage->event_id);
        $this->assertEquals($headers[Header::EVENT_TYPE], get_class($testEvent));
        $this->assertEquals($headers[Header::AGGREGATE_ROOT_ID], $storedDomainMessage->aggregate_root_id);
        $this->assertEquals($headers[Header::AGGREGATE_ROOT_ID_TYPE], $storedDomainMessage->aggregate_root_id_type);
        $this->assertEquals($headers[Header::TIME_OF_RECORDING], $storedDomainMessage->recorded_at);

        $payload = json_decode($storedDomainMessage->payload, true);

        $this->assertCount(5, $payload['headers']);
        $this->assertEquals(1, $payload['payload']['amount']);
    }

    /** @test */
    public function it_can_retrieve_messages()
    {
        $testEvent = new TestEvent(1);
        $identifier = new Identifier(1);

        $headers = [
            Header::EVENT_ID => 1,
            Header::EVENT_TYPE => get_class($testEvent),
            Header::AGGREGATE_ROOT_ID => $identifier->toString(),
            Header::AGGREGATE_ROOT_ID_TYPE => (new DotSeparatedSnakeCaseInflector)->instanceToType($identifier),
            Header::AGGREGATE_ROOT_VERSION => 1,
            Header::TIME_OF_RECORDING => PointInTime::fromDateTime(new DateTimeImmutable())->toString(),
        ];

        $message = new Message($testEvent, $headers);

        $this->repository->persist($message);

        $messages = $this->repository->retrieveAll($identifier);

        $messageArray = [];

        foreach ($messages as $message) {
            $messageArray[] = $message;
        }

        $this->assertCount(1, $messageArray);
        $this->assertInstanceOf(Message::class, $messageArray[0]);
        $this->assertInstanceOf(TestEvent::class, $messageArray[0]->event());
    }
}
