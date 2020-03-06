<?php

namespace Signifly\LaravelEventSauce\Console;

use Carbon\Carbon;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Signifly\LaravelEventSauce\LaravelMessageDispatcher;
use Signifly\LaravelEventSauce\Projector;
use Signifly\LaravelEventSauce\ProjectorFinder;

class ReplayCommand extends Command
{
    protected $signature = 'eventsauce:replay {projector?*}
                            {--after : Replay events after this date}';

    protected $description = 'Replay stored events';

    protected array $consumerMethods = [];
    protected array $dispatchers = [];

    public function handle(MessageRepository $messageRepository): void
    {
        // Find consumers / projectors
        $consumers = $this->findProjectors($this->argument('projector') ?? []);

        if ($consumers->isEmpty()) {
            $this->error('Found no projectors to replay.');

            return;
        }

        $this->line(sprintf('Now replaying %s consumers...', $consumers->count()));

        // Retrieve messages / events (after date)
        $messages = $messageRepository->retrieveAllForReplayingAfterDate($this->after());

        // Reset state
        $this->resetStateFor($consumers);

        // Apply events to consumers / projectors
        foreach ($messages as $message) {
            $dispatchers = $this->resolveDispatchersFrom($message, $consumers);
            $dispatchers->each->dispatch($message);
        }

        $this->info(sprintf('All done! %s consumers have been replayed.', $consumers->count()));
    }

    private function findProjectors(array $projectors = []): Collection
    {
        if (count($projectors) > 0) {
            return collect($projectors)
                ->filter(fn (string $projector) => is_subclass_of($projector, Projector::class))
                ->values();
        }

        return (new ProjectorFinder())
            ->within([
                base_path('app/Domain'),
                base_path('app/Support'),
            ])
            ->useBasePath(base_path())
            ->ignoringFiles(Composer::getAutoloadedFiles(base_path('composer.json')))
            ->findProjectors();
    }

    private function resetStateFor(Collection $consumers): void
    {
        $consumers
            ->map(fn ($consumer) => app($consumer))
            ->each
            ->resetState();
    }

    private function resolveDispatchersFrom(Message $message, Collection $consumers): Collection
    {
        $event = $message->event();
        $methodName = $this->methodNameFor($event);

        return $consumers
            ->filter(function ($consumer) use ($event, $methodName) {
                $this->consumerMethods[$consumer] ??= (new ReflectionClass($consumer))
                    ->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($this->consumerMethods[$consumer] as $method) {
                    if ($method->getName() === $methodName && $method->getNumberOfParameters() > 0) {
                        return get_class($event) === $method->getParameters()[0]->getType()->getName();
                    }
                }

                return false;
            })
            ->map(function ($consumer) {
                return $this->dispatchers[$consumer] ??= new LaravelMessageDispatcher($consumer);
            });
    }

    private function after(): ?Carbon
    {
        return ($after = $this->option('after')) ? Carbon::parse($after) : null;
    }

    private function methodNameFor(object $event): string
    {
        return sprintf('handle%s', (new ReflectionClass($event))->getShortName());
    }
}
