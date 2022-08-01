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

            if ($this->console) {
                $this->console->info('transcoding ' . $name . ' to ' . $ext);
                $format->on('progress', function ($video, $format, $percentage) {
                    if ($video && $format) {
                        $this->console->info($percentage . '% complete');
                    }
                });
            }

            $format->setKiloBitrate($this->kiloBitrate)
                ->setAudioChannels($this->audioChannels)
                ->setAudioKiloBitrate($this->audioKiloBitrate);

            $format->setAdditionalParameters(['-crf', $this->constantRateFactor]);
            $filename = $this->videoStorageDestination($name) . '.' . $ext;

            try {
                $media->save($format, $filename);
            } catch (Exception $e) {
                return ['status' => 'error', 'errors' => $e->getMessage()];
            }

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
}