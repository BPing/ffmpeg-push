<?php
namespace FFMpegPush\FFProbe;

use FFMpegPush\Configuration;

/**
 *
 * 流结构信息
 *
 * @package FFMpegPush\FFProbe
 */
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