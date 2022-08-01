<?php

namespace Rowles\Console\Processors;

use Exception;
use FFMpeg\Coordinate\{Dimension, TimeCode};

class ThumbnailProcessor extends BaseProcessor
{
    /** @var array $errors */
    protected array $errors = ['thumbnails' => 0];

    /**
     * ThumbnailProcessor constructor.
     * @param bool $console
     */
    public function __construct($console = false)
    {
        parent::__construct($console);
    }

    /**
     * @param mixed $name
     * @param bool $isGif
     * @param bool $bulkMode
     * @return array
     */
    public function thumbnail($name = null, bool $isGif = false, bool $bulkMode = false): array
    {
        if (is_null($name) && $bulkMode) {
            $files = array_slice(scandir($this->videoStorageSource()), 2);
            foreach ($files as $file) {
                $this->ffmpegThumbnail($file, $isGif);
            }
        } else {
            if (!file_exists($this->thumbnailStorageDestination($name, $isGif))) {
                $this->ffmpegThumbnail($name, $isGif);
            }
        }

        if ($this->errors['thumbnails'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * @param string $name
     * @param bool $isGif
     */
    private function ffmpegThumbnail(string $name, bool $isGif): void
    {
        try {
            if ($isGif) {
                $this->openVideo($this->videoStorageSource($name))->gif(TimeCode::fromSeconds($this->start), new Dimension(350, 151), $this->seconds)
                    ->save($this->thumbnailStorageDestination($name.'.gif', $isGif));
            } else {
                $this->openVideo($this->videoStorageSource($name))->frame(TimeCode::fromSeconds($this->start))
                    ->save($this->thumbnailStorageDestination($name.'.jpg'));
            }

            if ($this->console) {
                $this->console->success('[' . $name . '] thumbnail created');
            }
        } catch (Exception $e) {
            if ($this->console) {
                $this->console->error('[' . $name . '] ' . $e->getMessage());
            }

            ++$this->errors['thumbnails'];
        }
    }
}