<?php

namespace Rowles\Console\Commands;

use Rowles\Console\OutputFormatter;
use Illuminate\Console\Command;
use Rowles\Console\Processors\TranscodeProcessor;

class TranscodeVideoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var mixed
     */
    protected $signature = 'transcode-video {name}
        {--format= : The selected format}
        {--bitrate= : Kilo bitrate (default: 1000)}
        {--audio-bitrate= : Audio bitrate (default: 256)}
        {--audio-channels= : Audio channels (default: 2)};
        {--constant-rate-factor= : Constant rate factor (default: 20)}';

    /**
     * The console command description.
     *
     * @var mixed
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
        $console = new OutputFormatter($this->output);
        $processor = new TranscodeProcessor($this->output);

        if ($this->option('bitrate')) {
            $processor->setKiloBitrate($this->option('bitrate'));
        }

        if ($this->option('audio-bitrate')) {
            $processor->setAudioKiloBitrate($this->option('audio-bitrate'));
        }

        if ($this->option('audio-channels')) {
            $processor->setAudioChannels($this->option('audio-channels'));
        }

        if ($this->option('constant-rate-factor')) {
            $processor->setConstantRateFactor($this->option('constant-rate-factor'));
        }

        $process = $processor->transcode($this->argument('name'), $this->option('format'));

        if ($process['status'] === 'error') {
            $console->error($process['errors']);
        } else {
            $console->success('new format stored at '.$process['output']);
        }
    }
}
