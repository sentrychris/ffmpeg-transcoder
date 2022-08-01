<?php

namespace Rowles\Console\Processors;

use Exception;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Console\OutputStyle;

class ClipProcessor extends BaseProcessor
{
    /** @var array $errors */
    protected array $errors = ['clips' => 0];

    /**
     * ClipProcessor constructor.
     * @param false|OutputStyle $output
     */
    public function __construct(false|OutputStyle $output = false)
    {
        parent::__construct($output);
    }

    /**
     * @param null|string $name
     * @param bool $bulkMode
     * @return array
     */
    public function run(null|string $name = null, bool $bulkMode = false): array
    {
        if ($bulkMode && is_null($name)) {
            $files = array_slice(scandir($this->videoStorageSource()), 2);
            foreach ($files as $file) {
                $this->clip($file);
            }
        } else {
            if (!file_exists($this->clipStorageDestination($name))) {
                $this->clip($name);
            }
        }

        if ($this->errors['clips'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * @param string $name
     * @return void
     */
    private function clip(string $name): void
    {
        try {
            $media = $this->openVideo($this->videoStorageSource($name));
            $media->filters()->clip(TimeCode::fromSeconds($this->from), TimeCode::fromSeconds($this->seconds));
            $media->save($this->getNewFormat(), $this->clipStorageDestination($name));

            $this->console->success('[' . $name . '] clip created');
        } catch (Exception $e) {
            $this->console->error('[' . $name . '] ' . $e->getMessage());

            ++$this->errors['clips'];
        }
    }
}