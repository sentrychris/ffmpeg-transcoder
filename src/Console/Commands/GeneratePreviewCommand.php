<?php

namespace Rowles\Console\Commands;

use Rowles\Processor;
use Illuminate\Console\Command;

class GeneratePreviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'generate-preview {name}
        {--start= : Starting point for preview (default: 10)}
        {--seconds= : Number of seconds to capture for preview (default: 10)}';

    /**
     * The console command description.
     *
     * @var mixed
     */
    protected $description = 'This command generates 10-second previews for videos.';

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

        if ($this->option('start')) {
            $processor->setStart($this->option('start'));
        }

        if ($this->option('seconds')) {
            $processor->setSeconds($this->option('seconds'));
        }

        $process = $processor->preview($this->argument('name'));

        if ($process['status'] === 'error') {

            if ($process['errors'] === 'not-found') {
                $this->output->writeln('<fg=red>[error]</> video ID '.$this->argument('id').' not found.');
            } else if ($process['errors']['previews'] > 0) {
                $this->output->writeln('<comment>[error]</comment> failed to generate ' . $process['errors']['previews'] . ' previews');
            } else {
                $this->output->writeln('<fg=red>[error]</> unspecified error.');
            }

        } else {
            $this->output->writeln('<info>[success]</info> all videos processed');
        }
    }
}
