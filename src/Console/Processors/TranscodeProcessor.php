<?php

namespace Rowles\Console\Processors;

use Exception;

class TranscodeProcessor extends BaseProcessor
{
    /** @var int $kiloBitrate */
    protected int $kiloBitrate = 1000;

    /** @var int $audioChannels */
    protected int $audioChannels = 2;

    /** @var int $audioKiloBitrate */
    protected int $audioKiloBitrate = 256;

    /** @var int $constantRateFactor */
    protected int $constantRateFactor = 20;

    /**
     * TranscodeProcessor constructor.
     * @param bool $console
     */
    public function __construct($console = false)
    {
        parent::__construct($console);
    }

    /**
     * @param string $name
     * @param string $ext
     * @return array
     */
    public function run(string $name, string $ext = 'mp4'): array
    {
        try {
            $media = $this->openVideo($this->videoStorageSource($name));
            $format = $this->getNewFormat($ext);

            $this->console->info('transcoding ' . $name . ' to ' . $ext);
            $info = $media->getFormat();

            $this->console->output->createProgressBar();
            $this->console->output->progressStart();
            $format->on('progress', function ($video, $format, $percentage) {
                if ($video && $format) {
                    $this->console->output->writeln($percentage);
                    $this->console->output->progressAdvance($percentage);
                }
            });

            $format->setKiloBitrate($this->kiloBitrate)
                ->setAudioChannels($this->audioChannels)
                ->setAudioKiloBitrate($this->audioKiloBitrate);

            $format->setAdditionalParameters(['-crf', $this->constantRateFactor]);
            $filename = $this->videoStorageDestination($name) . '.' . $ext;

            $media->save($format, $filename);
            $this->console->output->progressFinish();
        } catch (Exception $e) {
            return ['status' => 'error', 'errors' => $e->getMessage()];
        }

        return ['status' => 'success', 'errors' => null, 'output' => $filename];
    }

    /**
     * @param int $kiloBitrate
     * @return self
     */
    public function setKiloBitrate(int $kiloBitrate): self
    {
        $this->kiloBitrate = $kiloBitrate;
        return $this;
    }

    /**
     * @param int $audioChannels
     * @return self
     */
    public function setAudioChannels(int $audioChannels): self
    {
        $this->audioChannels = $audioChannels;
        return $this;
    }

    /**
     * @param int $audioKiloBitrate
     * @return self
     */
    public function setAudioKiloBitrate(int $audioKiloBitrate): self
    {
        $this->audioKiloBitrate = $audioKiloBitrate;
        return $this;
    }

    /**
     * @param int $constantRateFactor
     * @return self
     */
    public function setConstantRateFactor(int $constantRateFactor): self
    {
        $this->constantRateFactor = $constantRateFactor;
        return $this;
    }

    private function formatBytes($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2);
    }
}