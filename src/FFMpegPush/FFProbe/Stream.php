<?php
namespace FFMpegPush\FFProbe;

use FFMpegPush\Configuration;

class Stream extends Configuration
{
    /**
     * Returns true if the stream is an audio stream.
     *
     * @return Boolean
     */
    public function isAudio()
    {
        return $this->get('codec_type') === 'audio';
    }

    /**
     * Returns true if the stream is a video stream.
     *
     * @return Boolean
     */
    public function isVideo()
    {
        return $this->get('codec_type') === 'video';
    }
}