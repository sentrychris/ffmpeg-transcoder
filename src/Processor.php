<?php

namespace Rowles;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Rowles\Console\Formatter;
use FFMpeg\Coordinate\{TimeCode, Dimension};
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\{DefaultVideo, X264, WMV, WebM};

/**
 * Class Processor
 */
class Processor
{
    /** @var FFMpeg $ffmpeg */
    protected FFMpeg $ffmpeg;

    /** @var int $kiloBitrate */
    protected int $kiloBitrate = 1000;

    /** @var int $audioChannels */
    protected int $audioChannels = 2;

    /** @var int $audioKiloBitrate */
    protected int $audioKiloBitrate = 256;

    protected int $start = 10;

    protected int $seconds = 10;

    /** @var array $errors */
    protected array $errors = ['thumbnails' => 0, 'previews' => 0];

    /** @var mixed $console */
    protected $console = false;

    /**
     * Processor constructor.
     *
     * @param mixed $console
     */
    public function __construct($console = false)
    {
        $this->ffmpeg = FFMpeg::create([
            'ffprobe.binaries' => $_ENV['FFPROBE_BINARY'],
            'ffmpeg.binaries' => $_ENV['FFMPEG_BINARY'],
            'ffmpeg.threads' => $_ENV['FFMPEG_THREADS'],
            'timeout' => $_ENV['FFMPEG_TIMEOUT'],
        ]);

        if ($console) {
            $this->console = new Formatter($console);
        }
    }

    /**
     * generate gif/jpeg thumbnails for videos.
     *
     * @param null $name
     * @param bool $isGif
     * @return array
     */
    public function thumbnail($name = null, bool $isGif = false): array
    {
        if(!file_exists($this->thumbnailStorage($name, $isGif))) {
            $this->generateThumbnail($name, $isGif);
        }

        if($this->errors['thumbnails'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * Generate 10-second preview video clips.
     *
     * @param string $name
     * @return array
     */
    public function preview(string $name): array
    {
        if(!file_exists($this->videoStorage($name, true))) {
            $this->generatePreview($name);
        }

        if($this->errors['previews'] > 0) {
            return ['status' => 'error', 'errors' => $this->errors];
        }

        return ['status' => 'success', 'errors' => null];
    }

    /**
     * Transcode videos to the selected format
     *
     * @param string $name
     * @param string $ext
     * @return array
     */
    public function transcode(string $name, string $ext = 'mp4'): array
    {
        try {
            $media = $this->openVideo($name);
            $format = $this->getNewFormat($ext);

            if ($this->console) {
                $this->console->info('transcoding '.$name.' to '.$ext);
                $format->on('progress', function ($video, $format, $percentage) {
                    if ($video && $format) {
                        $this->console->info($percentage.'% complete');
                    }
                });
            }

            $format->setKiloBitrate($this->kiloBitrate)
                ->setAudioChannels($this->audioChannels)
                ->setAudioKiloBitrate($this->audioKiloBitrate);
            $format->setAdditionalParameters( [ '-crf', '20' ] );
            $filename = $this->videoStorage($name).'.'.$ext;

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

    public function setStart(int $value): Processor {
        $this->start = $value;
        return $this;
    }

    public function setSeconds(int $value): Processor {
        $this->seconds = $value;
        return $this;
    }

    /**
     * @param int $kiloBitrate
     * @return Processor
     */
    public function setKiloBitrate(int $kiloBitrate): Processor
    {
        $this->kiloBitrate = $kiloBitrate;
        return $this;
    }

    /**
     * @param int $audioChannels
     * @return Processor
     */
    public function setAudioChannels(int $audioChannels): Processor
    {
        $this->audioChannels = $audioChannels;
        return $this;
    }

    /**
     * @param int $audioKiloBitrate
     * @return Processor
     */
    public function setAudioKiloBitrate(int $audioKiloBitrate): Processor
    {
        $this->audioKiloBitrate = $audioKiloBitrate;
        return $this;
    }

    /**
     * Fetch transcoding format
     *
     * @param string $ext
     * @return WebM|WMV|X264
     */
    private function getNewFormat($ext = null): DefaultVideo
    {
        switch ($ext) {
            case 'wmv':
                $format = new WMV('wmav2', 'wmv2');
                break;
            case 'webm':
                $format = new WebM('libvorbis', 'libvpx');
                break;
            default:
                $format = new X264('aac', 'libx264');
                break;
        }

        return $format;
    }

    /**
     * Generate a previews
     *
     * @param string $name
     * @return void
     */
    private function generatePreview(string $name): void
    {
        if(!file_exists($this->videoStorage($name, true))) {
            try {
                $media = $this->openVideo($this->videoStorage($name));
                $media->filters()->clip(TimeCode::fromSeconds($this->start), TimeCode::fromSeconds($this->seconds));
                $media->save($this->getNewFormat(), $this->videoStorage($name, true));

                if ($this->console) {
                    $this->console->success('['.$name.'] preview created');
                }
            } catch (Exception $e) {
                if ($this->console) {
                    $this->console->error('['.$name.'] '.$e->getMessage());
                }

                ++$this->errors['previews'];
            }
        }
    }

    /**
     * Generate a thumbnail
     *
     * @param string $name
     * @param bool $isGif
     * @return void
     */
    private function generateThumbnail(string $name, bool $isGif): void
    {
        if (!file_exists($this->thumbnailStorage($name, $isGif))) {
            try {
                if ($isGif) {
                    $this->openVideo($this->videoStorage($name))->gif(TimeCode::fromSeconds($this->start), new Dimension(350, 151), $this->seconds)
                        ->save($this->thumbnailStorage($name, $isGif));
                } else {
                    $this->openVideo($this->videoStorage($name))->frame(TimeCode::fromSeconds($this->start))
                        ->save($this->thumbnailStorage($name));
                }

                if ($this->console) {
                    $this->console->success('['.$name.'] thumbnail created');
                }
            } catch (Exception $e) {
                if ($this->console) {
                    $this->console->error('['.$name.'] '.$e->getMessage());
                }

                ++$this->errors['thumbnails'];
            }
        }
    }

    /**
     * @param string $name
     * @return Video
     * @throws InvalidArgumentException
     */
    private function openVideo(string $name): Video
    {
        return $this->ffmpeg->open($name);
    }

    /**
     * @param string $name
     * @param bool $isGif
     * @return string
     */
    private function thumbnailStorage(string $name, bool $isGif = false): string
    {
        if ($isGif) {
            $folder = 'previews';
            $ext = '.gif';
        } else {
            $folder = 'static';
            $ext = '.jpg';
        }

        return __DIR__ . '/..'.$_ENV['IMAGE_STORAGE'].'/'.$folder.'/'.$name.$ext;
    }

    /**
     * Get path to video storage
     *
     * @param string $name
     * @param bool $isPreview
     * @return string
     */
    private function videoStorage(string $name, bool $isPreview = false): string
    {
        $folder = ($isPreview === false) ? 'videos' : 'previews';

        return __DIR__ . '/..'.$_ENV['VIDEO_STORAGE'].'/'.$folder.'/'.$name;
    }
}
