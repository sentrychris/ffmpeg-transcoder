<?php

namespace Rowles;

use Exception;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use FFMpeg\Coordinate\{TimeCode, Dimension};
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\{DefaultVideo, X264, WMV, WebM};

/**
 * Class Processor
 */
class Processor extends AbstractProcessor
{
    /** @var FFMpeg $ffmpeg */
    protected $ffmpeg;

    /** @var int $kiloBitrate */
    protected $kiloBitrate = 1000;

    /** @var int $audioChannels */
    protected $audioChannels = 2;

    /** @var int $audioKiloBitrate */
    protected $audioKiloBitrate = 256;

    /** @var array $errors */
    protected $errors = ['thumbnails' => 0, 'previews' => 0];

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

        $this->console = $console;
    }

    /**
     * generate gif/jpeg thumbnails for videos.
     *
     * @param string $name
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
                $this->console->writeln('<fg=blue>[info]</> transcoding '.$name.' to '.$ext);
                $format->on('progress', function ($video, $format, $percentage) {
                    if ($video && $format) {
                        $this->console->writeln('[progress] '.$percentage.'% complete');
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
                $media->filters()->clip(TimeCode::fromSeconds(2), TimeCode::fromSeconds(10));
                $media->save($this->getNewFormat(), $this->videoStorage($name, true));

                if ($this->console) {
                    $this->console->writeln('<info>[success]</info> [video '.$name.'] previews created');
                }
            } catch (Exception $e) {
                if ($this->console) {
                    $this->console->writeln('<fg=red>[error]</> [video '.$name.'] '.$e->getMessage());
                }

                ++$this->errors['previews'];
            }
        }
    }

    /**
     * Generate a thumbnail
     *
     * @param string $name
     * @param $isGif
     * @return void
     */
    private function generateThumbnail(string $name, $isGif): void
    {
        if (!file_exists($this->thumbnailStorage($name, $isGif))) {
            try {
                if ($isGif) {
                    $this->openVideo($this->videoStorage($name))->gif(TimeCode::fromSeconds(360), new Dimension(350, 151), 20)
                        ->save($this->thumbnailStorage($name, $isGif));
                } else {
                    $this->openVideo($this->videoStorage($name))->frame(TimeCode::fromSeconds(10))
                        ->save($this->thumbnailStorage($name));
                }

                if ($this->console) {
                    $this->console->writeln('<info>[success]</info> [video '.$name.'] thumbnail created');
                }
            } catch (Exception $e) {
                if ($this->console) {
                    $this->console->writeln('<fg=red>[error]</> [video '.$name.'] '.$e->getMessage());
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
