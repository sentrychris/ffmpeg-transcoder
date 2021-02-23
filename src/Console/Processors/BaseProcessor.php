<?php

namespace Rowles\Console\Processors;

use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Rowles\Console\Formatter;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Format\Video\{DefaultVideo, X264, WMV, WebM};

class BaseProcessor
{
    /** @var FFMpeg $ffmpeg */
    protected FFMpeg $ffmpeg;

    /** @var int $start */
    protected int $start = 10;

    /** @var int $seconds */
    protected int $seconds = 10;

    /** @var mixed $console */
    protected $console = false;

    /**
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
     * @param int $value
     * @return $this
     */
    public function setStart(int $value): self
    {
        $this->start = $value;
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
     * @param bool $isPreview
     * @return string
     */
    protected function thumbnailStorage(string $name, bool $isPreview = false): string
    {
        if (is_file($name)) {
            return $name;
        }

        $folder = $isPreview ? 'previews' : 'static';

        return __DIR__ . '/../../../' . $_ENV['IMAGE_STORAGE'] . '/' . $folder . '/' . $name;
    }

    /**
     * @param string $name
     * @param bool $isPreview
     * @return string
     */
    protected function videoStorage(string $name, bool $isPreview = false): string
    {
        if (is_file($name)) {
            return $name;
        }

        $folder = ($isPreview === false) ? 'videos' : 'previews';

        return $_ENV['VIDEO_STORAGE'] . '/' . $folder . '/' . $name;
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
