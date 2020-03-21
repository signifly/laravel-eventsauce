<?php

namespace Signifly\LaravelEventSauce;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Ramsey\Uuid\Uuid;
use Signifly\LaravelEventSauce\Contracts\ProcessId;
use Signifly\LaravelEventSauce\Contracts\State;
use Signifly\LaravelEventSauce\Contracts\StateRepository;

class DatabaseStateRepository implements StateRepository
{
    private DatabaseManager $database;

    private string $tableName;

    private ClassNameInflector $inflector;

    private ?string $connection = null;

    public function __construct(DatabaseManager $database, string $tableName, ClassNameInflector $inflector = null)
    {
        $this->database = $database;
        $this->tableName = $tableName;
        $this->inflector = $inflector ?? new DotSeparatedSnakeCaseInflector();
    }

    public function find(ProcessId $processId, string $type): ?State
    {
        $result = $this->connection()
            ->table($this->tableName)
            ->where('process_id', $processId)
            ->where('process_type', $type)
            ->orderBy('process_version', 'desc')
            ->first(['state', 'state_type', 'process_id', 'process_type', 'process_version']);

        if (! $result) {
            return null;
        }

        $stateClass = $this->inflector->typeToClassName($result->state_type);

        return new $stateClass(
            \Signifly\LaravelEventSauce\ProcessId::fromString($result->process_id),
            $result->process_type,
            $result->process_version,
            json_decode($result->state, true)
        );
    }

    public function save(State $state)
    {
        $this->connection()
            ->table($this->tableName)
            ->insert([
                'event_id' => Uuid::uuid4()->toString(),
                'state_type' => $this->inflector->instanceToType($state),
                'process_id' => $state->processId(),
                'process_type' => $state->type(),
                'process_version' => $state->version() + 1,
                'state' => json_encode($state),
            ]);
    }

    private function connection(): ConnectionInterface
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
}
