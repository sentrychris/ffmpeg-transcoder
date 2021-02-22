<?php

namespace Rowles\Console\Commands;

use Rowles\Processor;
use Illuminate\Console\Command;

class GeneratePreviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rowles:generate-previews {name}';

    /**
     * The console command description.
     *
     * @var string
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
        $process = $processor->preview($this->argument('name'));

        if ($process['status'] === 'error') {

            if ($process['errors'] === 'not-found') {
                $this->output->writeln('<fg=red>[error]</> video ID '.$this->argument('id').' not found.');
            } else if ($process['errors']['previews'] > 0) {
                $this->output->writeln('<comment>[warning]</comment> failed to generate ' . $process['errors']['previews'] . ' previews');
            } else {
                $this->output->writeln('<fg=red>[error]</> unspecified error.');
            }

        } else {
            $this->output->writeln('<info>[success]</info> all videos processed');
        }
    }
}
