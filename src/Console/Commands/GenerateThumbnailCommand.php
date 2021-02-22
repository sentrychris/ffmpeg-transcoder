<?php

namespace Rowles\Console\Commands;

use Rowles\Processor;
use Illuminate\Console\Command;

class GenerateThumbnailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rowles:generate-thumbnails {name} {--preview : render previews in gif format}';

    /**
     * The console command description.
     *
     * @var string
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
        $process = $processor->thumbnail($this->argument('name'), $this->option('preview'));

        if ($process['status'] === 'error') {
            if ($process['errors'] === 'not-found') {
                $this->output->writeln('<fg=red>[error]</> video ID '.$this->argument('id').' not found.');
            } else if($process['errors']['thumbnails'] > 0) {
                $this->output->writeln('<comment>[warning]</comment> failed to generate '.$process['errors']['thumbnails'].' thumbnails');
            } else {
                $this->output->writeln('<fg=red>[error]</> unspecified error');
            }

        } else {
            $this->output->writeln('<info>[success]</info> all videos processed');
        }
    }
}
