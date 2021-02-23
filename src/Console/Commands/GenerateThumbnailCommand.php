<?php

namespace Rowles\Console\Commands;

use Rowles\Console\OutputFormatter;
use Illuminate\Console\Command;
use Rowles\Console\OutputHandler;
use Rowles\Console\Processors\ThumbnailProcessor;

class GenerateThumbnailCommand extends Command
{
    /** @var string  */
    protected string $identifier = 'thumbnails';

    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'generate-thumbnail {name?}
        {--gif : render thumbnail(s) in gif format}
        {--bulk-mode : Generate thumbnails in bulk mode}
        {--start= : Starting point for thumbnail(s) (default: 10)}
        {--seconds= : Number of seconds to capture for gif thumbnail(s) (default: 10)}';

    /**
     * The console command description.
     *
     * @var mixed
     */
    protected $description = 'This command generates thumbnails for videos.';

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
        $processor = new ThumbnailProcessor($this->output);

        if ($this->option('start')) {
            $processor->setStart($this->option('start'));
        }

        if ($this->option('seconds')) {
            $processor->setSeconds($this->option('seconds'));
        }

        $process = $processor->thumbnail(
            $this->argument('name'),
            $this->option('gif'),
            $this->option('bulk-mode')
        );

        OutputHandler::handle($process, $this->output, $this->identifier);
    }
}
