<?php

namespace Rowles\Console\Processors;

use Exception;
use FFMpeg\Coordinate\TimeCode;

class PreviewProcessor extends BaseProcessor
{
    /** @var array $errors */
    protected array $errors = ['previews' => 0];

    /**
     * PreviewProcessor constructor.
     * @param bool $console
     */
    public function __construct($console = false)
    {
        parent::__construct($console);
    }

    /**
     * @param mixed $name
     * @param bool $bulkMode
     * @return array
     */
    public function preview($name = null, bool $bulkMode = false): array
    {
        if (is_null($name) && $bulkMode) {
            $files = array_slice(scandir($this->videoStorageSource()), 2);
            foreach ($files as $file) {
                $this->ffmpegPreview($file);
            }
        } else {
            if (!file_exists($this->previewStorageDestination($name))) {
                $this->ffmpegPreview($name);
            }
        }

        if ($this->errors['previews'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * @param string $name
     * @return void
     */
    private function ffmpegPreview(string $name): void
    {
        try {
            $media = $this->openVideo($this->videoStorageSource($name));
            $media->filters()->clip(TimeCode::fromSeconds($this->start), TimeCode::fromSeconds($this->seconds));
            $media->save($this->getNewFormat(), $this->previewStorageDestination($name));

            if ($this->console) {
                $this->console->success('[' . $name . '] preview created');
            }
        } catch (Exception $e) {
            if ($this->console) {
                $this->console->error('[' . $name . '] ' . $e->getMessage());
            }

            ++$this->errors['previews'];
        }
    }
}