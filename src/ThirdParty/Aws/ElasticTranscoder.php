<?php

namespace Rowles\ThirdParty\Aws;

use Aws\Result;
use Aws\ElasticTranscoder\ElasticTranscoderClient;

class ElasticTranscoder
{
    /** @var ElasticTranscoderClient $client */
    protected ElasticTranscoderClient $client;

    /** @var mixed $presets */
    protected $presets;

    /** @var Result */
    protected Result $job;

    /**
     * ElasticTranscoder constructor.
     */
    public function __construct()
    {
        $this->client = new ElasticTranscoderClient([
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
            'region' => $_ENV['AWS_REGION'],
            'version' => 'latest',
            'ua_append' => [
                'L5MOD/3.6.0',
            ]
        ]);
        $this->presets = [
            'Audio AAC-256 k' => '1351620000001-100110',
            'Audio AAC-160 k' => '1351620000001-100120',
            'Audio AAC-128 k' => '1351620000001-100130',
            'Audio AAC-64 k' => '1351620000001-100141',
            'Audio MP3-320 k' => '1351620000001-300010',
            'Audio MP3-192 k' => '1351620000001-300020',
            'Audio MP3-160 k' => '1351620000001-300030',
            'Audio MP3-128 k' => '1351620000001-300040',
            'Audio WAV 44100 Hz, 16 bit' => '1351620000001-300300',
            'Audio WAV 44100 Hz, 8 bit' => '1351620000001-300200',
            'Amazon Kindle Fire HDX' => '1351620000001-100150',
            'Amazon Kindle Fire HD 8.9' => '1351620000001-100090',
            'Amazon Kindle Fire HD' => '1351620000001-100080',
            'Apple TV 3G, Roku HD/2 XD' => '1351620000001-100060',
            'Apple TV 2 G' => '1351620000001-100050',
            'FLAC-CD' => '1351620000001-300110',
            'Full HD 1080i50' => '1351620000001-100180',
            'Full HD 1080i60' => '1351620000001-100190',
            'Full HD 1080i50-XDCAM422' => '1351620000001-100230',
            'Full HD 1080i60-XDCAM422' => '1351620000001-100220',
            'Generic 1080p' => '1351620000001-000001',
            'Generic 720p' => '1351620000001-000010',
            'Generic 480p 16:9' => '1351620000001-000020',
            'Generic 480p 4:3' => '1351620000001-000030',
            'Generic 360p 16:9' => '1351620000001-000040',
            'Generic 360p 4:3' => '1351620000001-000050',
            'Generic 320x240' => '1351620000001-000061',
            'Gif' => '1351620000001-100200',
            'HLS v3(Apple HTTP Live Streaming), 2 megabits / second' => '1351620000001-200010',
            'HLS v3 and v4 (Apple HTTP Live Streaming), 2 megabits/second, Video-only' => '1351620000001-200015',
            'HLS v3(Apple HTTP Live Streaming), 1.5 megabits / second' => '1351620000001-200020',
            'HLS v3 and v4 (Apple HTTP Live Streaming), 1.5 megabits/second, Video-only' => '1351620000001-200025',
            'HLS v3(Apple HTTP Live Streaming), 1 megabit / second' => '1351620000001-200030',
            'HLS v3 and v4 (Apple HTTP Live Streaming), 1 megabit/second, Video-only' => '1351620000001-200035',
            'HLS v3(Apple HTTP Live Streaming), 600 kilobits / second' => '1351620000001-200040',
            'HLS v3 and v4 (Apple HTTP Live Streaming), 600 kilobits/second, Video-only' => '1351620000001-200045',
            'HLS v3(Apple HTTP Live Streaming), 400 kilobits / second' => '1351620000001-200050',
            'HLS v3 and v4 (Apple HTTP Live Streaming), 400 kilobits/second, Video-only' => '1351620000001-200055',
            'HLS v3 and v4 Audio, 160 k' => '1351620000001-200060',
            'HLS v3 and v4 Audio, 64 k (Uses auto for Audio:CodecOptions:Profile)' => '1351620000001-200071',
            'iPhone 5, iPhone 4S, iPad 4G and 3G, iPad mini, Samsung Galaxy S2 / S3 / Tab 2' => '1351620000001-100020',
            'iPod touch, iPhone 3 and 1, iPod classic' => '1351620000001-100040',
            'MPEG-DASH Audio 128 k' => '1351620000001-500060',
            'MPEG-DASH Video 600 k' => '1351620000001-500050',
            'MPEG-DASH Video 1200 k' => '1351620000001-500040',
            'MPEG-DASH Video 2400 k' => '1351620000001-500030',
            'MPEG-DASH Video 4800 k' => '1351620000001-500020',
            'NTSC' => '1351620000001-100160',
            'PAL' => '1351620000001-100170',
            'Smooth Streaming 3 megabits/second' => '1351620000001-400010',
            'Smooth Streaming 2 megabits / second' => '1351620000001-400020',
            'Smooth Streaming 1.5 megabits/second' => '1351620000001-400030',
            'Smooth Streaming 1 megabits / second' => '1351620000001-400040',
            'Smooth Streaming 800 kilobits/second' => '1351620000001-400050',
            'Smooth Streaming 600 kilobits / second' => '1351620000001-400060',
            'Smooth Streaming 500 kilobits/second' => '1351620000001-400070',
            'Smooth Streaming 400 kilobits / second' => '1351620000001-400080',
            'Web (MP4)' => '1351620000001-100070',
            'Web: Facebook, SmugMug, Vimeo, YouTube' => '1351620000001-100070',
            'Web: Flash Video' => '1351620000001-100210',
            'Webm 720p' => '1351620000001-100240',
            'Webm VP9 720p' => '1351620000001-100250',
            'Webm VP9 360p' => '1351620000001-100260'
        ];
    }


    /**
     * Create transcoding job.
     *
     * @param string $pipeline
     * @param string $inputKey
     * @param string $outputKey
     * @param string $presetId
     * @param string $outputKeyPrefix
     * @return mixed|null
     */
    public function createJob(string $pipeline, string $inputKey, string $outputKey, string $presetId, string $outputKeyPrefix = '')
    {
        $this->job = $this->client->createJob([
            'PipelineId' => $pipeline,

            'outputKeyPrefix' => $outputKeyPrefix,

            'Input' => [
                'Key' => $inputKey,
                'FrameRate' => 'auto',
                'Resolution' => 'auto',
                'AspectRatio' => 'auto',
                'Interlaced' => 'auto',
                'Container' => 'auto',
            ],

            'Output' => [
                'Key' => $outputKey,
                'Rotate' => 'auto',
                'PresetId' => $presetId,
            ]
        ]);

        return $this->job->get('Job');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function readJob($id)
    {
        $job = $this->client->readJob(['Id' => $id]);
        return $job->get('Job');
    }

    /**
     * @return Result
     */
    public function listPipelines(): Result
    {
        return $this->client->listPipelines();
    }
}
