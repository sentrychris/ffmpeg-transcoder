# FFMPEG Transcoder

A Small console package to help transcode videos and generate thumbnails/previews.

## Installation

1. Clone the repository.
  ```sh
  git clone git@github.com:chrisrowles/ffmpeg-transcoder
  cd ffmpeg-transcoder
  ```
3. Install the dependencies with composer
  ```sh
  composer install
  ```
4. Copy .env.example to .env and enter your env variable values.
  ```sh
  cp .env.example .env
  ```

## Commands

Available commands:

```
  clip                This command clips videos.
  generate-thumbnail  This command generates thumbnails for videos.
  help                Displays help for a command
  list                Lists commands
  transcode-video     This command transcodes videos to the selected format.
```

### Transcoding
```
Description:
  This command transcodes videos to the selected format.

Usage:
  transcode-video [options] [--] <name>

Arguments:
  name  Filename (leave blank for bulk processing)

Options:
      --format[=FORMAT]                              The selected format
      --bitrate[=BITRATE]                            Kilo bitrate (default: 1000)
      --audio-bitrate[=AUDIO-BITRATE]                Audio bitrate (default: 256)
      --audio-channels[=AUDIO-CHANNELS]              Audio channels (default: 2)
      --constant-rate-factor[=CONSTANT-RATE-FACTOR]  Constant rate factor (default: 20)
```


### Thumbnails

```
Description:
  This command generates thumbnails for videos.

Usage:
  generate-thumbnail [options] [--] [<name>]

Arguments:
  name  Filename (leave blank for bulk processing)

Options:
      --gif                Render thumbnail(s) in gif format
      --bulk-mode          Generate thumbnails in bulk mode
      --start[=START]      Starting point for thumbnail(s) (default: 10)
      --seconds[=SECONDS]  Number of seconds to capture for gif thumbnail(s) (default: 10)
```

### Clips
```
Description:
  This command clips videos.

Usage:
  clip [options] [--] [<name>]

Arguments:
  name  Filename (leave blank for bulk processing)

Options:
      --bulk              Clip multiple videos
      --from[=START]      Starting point for clip
      --seconds[=SECONDS] Number of seconds to clip
```
