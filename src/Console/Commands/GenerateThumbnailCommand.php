<?php

namespace Rowles\Console\Commands;

use Rowles\Processor;
use Rowles\Console\Formatter;
use Illuminate\Console\Command;

class GenerateThumbnailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'generate-thumbnail {name}
        {--preview : render previews in gif format}
        {--start= : Starting point for preview (default: 10)}
        {--seconds= : Number of seconds to capture for preview (default: 10)}';

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
        $processor = new Processor($this->output);
        $console = new Formatter($this->output);

        if ($this->option('start')) {
            $processor->setStart($this->option('start'));
        }

        if ($this->option('seconds')) {
            $processor->setSeconds($this->option('seconds'));
        }

        $process = $processor->thumbnail($this->argument('name'), $this->option('preview'));

        if ($process['status'] === 'error') {
            if ($process['errors']['thumbnails'] > 0) {
                $console->error('failed to generate thumbnail.');
            } else {
                $console->error('unspecified error.');
            }
        } else {
            $console->success('thumbnail successfully generated.');
        }
    }
}
