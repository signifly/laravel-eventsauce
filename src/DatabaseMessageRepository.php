<?php

namespace Signifly\LaravelEventSauce;

use Carbon\Carbon;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Ramsey\Uuid\Uuid;

class DatabaseMessageRepository implements MessageRepository
{
    protected DatabaseManager $database;

    protected string $tableName;

    protected MessageSerializer $serializer;

    protected ?string $connection = null;

    protected const AGGREGATE_ROOT_TYPE = '__aggregate_root_type';

    public function __construct(DatabaseManager $database, string $tableName, MessageSerializer $serializer)
    {
        $this->database = $database;
        $this->tableName = $tableName;
        $this->serializer = $serializer;
    }

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $payload = $this->serializer->serializeMessage($message);
            $headers = $payload['headers'];

            $this->connection()
                ->table($this->tableName)
                ->insert([
                    'event_id' => $headers[Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                    'event_type' => $headers[Header::EVENT_TYPE],
                    'aggregate_root_id' => $headers[Header::AGGREGATE_ROOT_ID],
                    'aggregate_root_id_type' => $headers[Header::AGGREGATE_ROOT_ID_TYPE],
                    'aggregate_root_version' => $headers[Header::AGGREGATE_ROOT_VERSION] ?? 0,
                    'payload' => json_encode($payload),
                    'recorded_at' => $headers[Header::TIME_OF_RECORDING],
                ]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->baseQuery($id)
            ->select('payload')
            ->cursor();

        return $this->yieldMessagesForResult($payloads);
    }

    public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator
    {
        $payloads = $this->baseQuery($id)
            ->where('aggregate_root_version', '>', $aggregateRootVersion)
            ->select('payload')
            ->cursor();

        return $this->yieldMessagesForResult($payloads);
    }

    public function retrieveAllForReplayingAfterDate(Carbon $time = null): Generator
    {
        $payloads = $this->connection()
            ->table($this->tableName)
            ->when($time !== null, fn ($query) => $query->where('recorded_at', '>', $time))
            ->orderBy('recorded_at')
            ->select('payload')
            ->cursor();

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }

    protected function yieldMessagesForResult(LazyCollection $payloads)
    {
        foreach ($payloads as $payload) {
            $messages = $this->serializer->unserializePayload(json_decode($payload->payload, true));

            /* @var Message $message */
            foreach ($messages as $message) {
                yield $message;
            }
        }

        return (isset($message) && $message instanceof Message)
            ? $message->aggregateVersion()
            : 0;
    }

    protected function connection(): ConnectionInterface
    {
        return $this->database->connection($this->connection);
    }

    public function setConnection(?string $connection): void
    {
        $this->connection = $connection;
    }

    public function setTable(string $table): void
    {
        $this->tableName = $table;
    }

    protected function baseQuery(AggregateRootId $id): Builder
    {
        $aggregateRootIdType = (new DotSeparatedSnakeCaseInflector())->instanceToType($id);

        return $this->connection()
            ->table($this->tableName)
            ->where('aggregate_root_id', $id->toString())
            ->where('aggregate_root_id_type', $aggregateRootIdType)
            ->orderBy('aggregate_root_version');
    }
}
