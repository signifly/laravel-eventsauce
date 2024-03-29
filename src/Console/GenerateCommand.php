<?php

namespace Signifly\LaravelEventSauce\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Signifly\EventSourceGenerator\CodeDumper;
use Signifly\EventSourceGenerator\FilePerNamespaceWriter;
use Signifly\EventSourceGenerator\FileWriter;
use Signifly\EventSourceGenerator\YamlDefinitionLoader;

class GenerateCommand extends Command
{
    protected $signature = 'eventsauce:generate {--d|dump : Triggers composer dump-autoload}';

    protected $description = 'Generate EventSauce code.';

    public function handle(\Illuminate\Support\Composer $composer)
    {
        $this->info('Start generating code...');

        $this->generateCode(
            config('eventsource-generator.definitions', []),
            $this->resolveWriter()
        );

        if ($this->option('dump')) {
            $this->line('');
            $this->line('Composer dump started...');
            $composer->dumpOptimized();
            $this->line('Composer dump finished');
            $this->line('');
        }

        $this->info('All done!');
    }

    protected function resolveWriter(): FileWriter
    {
        $fileWriterClassName = config('eventsource-generator.writer', FilePerNamespaceWriter::class);
        $fileWriterArguments = config('eventsource-generator.writer_arguments', [
            'fileName' => 'events_and_commands.php',
        ]);

        if (! is_subclass_of($fileWriterClassName, FileWriter::class)) {
            throw new InvalidArgumentException(
                sprintf('%s must be implement the %s interface.', $fileWriterClassName, FileWriter::class)
            );
        }

        if (! is_array($fileWriterArguments)) {
            throw new InvalidArgumentException(
                sprintf('%s must be an array of arguments.', $fileWriterArguments)
            );
        }

        return app()->makeWith($fileWriterClassName, $fileWriterArguments);
    }

    protected function generateCode(array $inputFiles, FileWriter $writer)
    {
        $loader = new YamlDefinitionLoader(config('eventsource-generator.missing_examples', 'warn'));
        $dumper = new CodeDumper(config('eventsource-generator.loader_logging', false));

        $groups = $loader->loadFiles($inputFiles);

        $messages = $writer->writeCode(collect($dumper->dumpAll($groups)));

        foreach ($messages as $message) {
            $this->line($message);
        }
    }
}
