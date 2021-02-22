<?php

namespace Rowles\Console\Commands;

use Rowles\Processor;
use Illuminate\Console\Command;

class TranscodeVideoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcode-video {name}
        {--format= : The selected format}
        {--bitrate= : Kilo bitrate (default: 1000)}
        {--audio-bitrate= : Audio bitrate (default: 256)}
        {--audio-channels= : Audio channels (default: 2)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command transcodes videos to the selected format.';

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

        if ($this->option('bitrate')) {
            $processor->setKiloBitrate($this->option('bitrate'));
        }

        if ($this->option('audio-bitrate')) {
            $processor->setAudioKiloBitrate($this->option('audio-bitrate'));
        }

        if ($this->option('audio-channels')) {
            $processor->setAudioChannels($this->option('audio-channels'));
        }

        if ($this->option('bitrate')) {
            $processor->setKiloBitrate($this->option('bitrate'));
        }

        $process = $processor->transcode(
            $this->argument('name'),
            $this->option('ext')
        );

        if ($process['status'] === 'error') {
            $this->output->writeln('<fg=red>[error]</> ' . $process['errors']);
        } else {
            $this->output->writeln('<info>[success]</info> new video stored at ' . $process['output']);
            $this->output->writeln('<fg=blue>[info]</> file path updated');
        }
    }
}
