<?php

namespace Rowles\Console\Commands;

use Rowles\Console\OutputFormatter;
use Illuminate\Console\Command;
use Rowles\Console\OutputHandler;
use Rowles\Console\Processors\CaptureProcessor;

class CaptureCommand extends Command
{
    /** @var string  */
    protected string $identifier = 'captures';

    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'capture {name?}
        {--gif : capture video in gif format}
        {--bulk : Capture from multiple videos}
        {--from= : Point of capture}
        {--seconds= : Number of seconds to capture if it is a gif}';

    /**
     * The console command description.
     *
     * @var mixed
     */
    protected $description = 'This command generates captures for videos.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $processor = new CaptureProcessor($this->output);
        $processor->setFrom($this->option('from'));
        $processor->setSeconds($this->option('seconds'));

        $process = $processor->run(
            $this->argument('name'),
            $this->option('gif'),
            $this->option('bulk')
        );

        OutputHandler::handle($process, $this->output, $this->identifier);
    }
}
