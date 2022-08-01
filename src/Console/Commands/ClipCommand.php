<?php

namespace Rowles\Console\Commands;

use Rowles\Console\OutputFormatter;
use Illuminate\Console\Command;
use Rowles\Console\OutputHandler;
use Rowles\Console\Processors\ClipProcessor;

class ClipCommand extends Command
{
    /** @var string  */
    protected string $identifier = 'clips';

    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'clip {name?}
        {--bulk : Clip multiple videos}
        {--from= : Starting point for clip}
        {--seconds= : Number of seconds to clip}';

    /**
     * The console command description.
     *
     * @var mixed
     */
    protected $description = 'This command clips videos.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $processor = new ClipProcessor($this->output);

        $processor->setFrom($this->option('from'));
        $processor->setSeconds($this->option('seconds'));
        $process = $processor->run($this->argument('name'), $this->option('bulk'));

        OutputHandler::handle($process, $this->output, $this->identifier);
    }
}
