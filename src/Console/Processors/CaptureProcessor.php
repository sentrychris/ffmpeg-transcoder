<?php

namespace Rowles\Console\Processors;

use Exception;
use FFMpeg\Coordinate\{Dimension, TimeCode};

class CaptureProcessor extends BaseProcessor
{
    /** @var array $errors */
    protected array $errors = ['captures' => 0];

    /**
     * CaptureProcessor constructor.
     * @param bool $console
     */
    public function __construct($console = false)
    {
        parent::__construct($console);
    }

    /**
     * @param null|string $name
     * @param bool $isGif
     * @param bool $bulkMode
     * @return array
     */
    public function run(null|string $name = null, bool $isGif = false, bool $bulkMode = false): array
    {
        if ($bulkMode && is_null($name)) {
            $files = array_slice(scandir($this->videoStorageSource()), 2);
            foreach ($files as $file) {
                $this->capture($file, $isGif);
            }
        } else {
            if (!file_exists($this->captureStorageDestination($name, $isGif))) {
                $this->capture($name, $isGif);
            }
        }

        if ($this->errors['captures'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * @param null|string $name
     * @param bool $isGif
     */
    private function capture(null|string $name, bool $isGif): void
    {
        try {
            if ($isGif) {
                $this->openVideo($this->videoStorageSource($name))
                    ->gif(
                        TimeCode::fromSeconds($this->from),
                        new Dimension(350, 151),
                        $this->seconds
                    )
                    ->save($this->captureStorageDestination($name.'.gif', true));
            } else {
                $this->openVideo($this->videoStorageSource($name))
                    ->frame(TimeCode::fromSeconds($this->from))
                    ->save($this->captureStorageDestination($name.'.jpg'));
            }

            $this->console->success('[' . $name . '] capture created');
        } catch (Exception $e) {
            $this->console->error('[' . $name . '] ' . $e->getMessage());

            ++$this->errors['captures'];
        }
    }
}