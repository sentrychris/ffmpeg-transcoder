<?php

namespace Rowles\Console\Processors;

use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Illuminate\Console\OutputStyle;
use Rowles\Console\OutputFormatter;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\{DefaultVideo, X264, WMV, WebM};

abstract class BaseProcessor
{
    /** @var FFMpeg $ffmpeg */
    protected FFMpeg $ffmpeg;

    /** @var int $from */
    protected int $from;

    /** @var int $seconds */
    protected int $seconds;

    /** @var false|OutputFormatter $console */
    protected false|OutputFormatter $console = false;

    /**
     * @param false|OutputStyle $output
     */
    public function __construct(false|OutputStyle $output = false)
    {
        $this->ffmpeg = FFMpeg::create([
            'ffprobe.binaries' => $_ENV['FFPROBE_BINARY'],
            'ffmpeg.binaries' => $_ENV['FFMPEG_BINARY'],
            'ffmpeg.threads' => $_ENV['FFMPEG_THREADS'],
            'timeout' => $_ENV['FFMPEG_TIMEOUT'],
        ]);

        if ($output) {
            $this->console = new OutputFormatter($output);
        }
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setFrom(int $value): self
    {
        $this->from = $value;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setSeconds(int $value): self
    {
        $this->seconds = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Video
     * @throws InvalidArgumentException
     */
    protected function openVideo(string $name): Video
    {
        return $this->ffmpeg->open($name);
    }

    /**
     * @param string $name
     * @param bool $isGif
     * @return string
     */
    protected function thumbnailStorageSource(string $name = "", bool $isGif = false): string
    {
        if (is_file($name)) {
            return $name;
        }

        $folder = $isGif ? 'gifs' : 'jpegs';
        $directory = $_ENV['IMAGE_STORAGE_SOURCE'] . '/' . $folder;

        return $directory . '/' . $name;
    }

    /**
     * @param string $name
     * @param bool $isGif
     * @return string
     */
    protected function thumbnailStorageDestination(string $name, bool $isGif = false): string
    {
        if (is_file($name)) {
            return $name;
        }

        $folder = $isGif ? 'gifs' : 'jpegs';
        $directory = $_ENV['IMAGE_STORAGE_DESTINATION'] . '/' . $folder;

        return $directory . '/' . $name;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function clipStorageDestination(string $name): string
    {
        if (is_file($name)) {
            return $name;
        }

        return $_ENV['CLIP_STORAGE_DESTINATION'] . '/' . $name;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function videoStorageSource(string $name = ""): string
    {
        if (is_file($name)) {
            return $name;
        }

        return $_ENV['VIDEO_STORAGE_SOURCE'] . '/' . $name;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function videoStorageDestination(string $name): string
    {
        if (is_file($name)) {
            return $name;
        }

        return $_ENV['VIDEO_STORAGE_DESTINATION'] . '/' . $name;
    }

    /**
     * @param string $ext
     * @return WebM|WMV|X264
     */
    protected function getNewFormat($ext = null): DefaultVideo
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
}
