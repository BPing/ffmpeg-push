<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/3
 * Time: 14:11
 */

namespace FFMpegPush;


class PushFormat
{

    /** @var string */
    protected $videoCodec = 'copy';

    /** @var Integer 码率（K） */
    protected $videoKiloBitrate = null;


    /** @var string */
    protected $audioCodec = 'copy';

    /** @var integer 码率（K） */
    protected $audioKiloBitrate = null;

    /** @var integer */
    protected $audioChannels = null;

    /** @var Array */
    protected $additionalParamaters = null;

    /**
     * @param string $videoCodec
     * @return $this
     */
    public function setVideoCodec($videoCodec)
    {
        $this->videoCodec = $videoCodec;
        return $this;
    }

    /**
     * @param int $videoKiloBitrate
     * @return $this
     */
    public function setVideoKiloBitrate($videoKiloBitrate)
    {
        $this->videoKiloBitrate = $videoKiloBitrate;
        return $this;
    }


    /**
     * @param array $additionalParamaters
     * @return $this
     */
    public function setAdditionalParamaters(array $additionalParamaters)
    {
        $this->additionalParamaters = $additionalParamaters;
        return $this;
    }

    /**
     * @param $audioCodec
     * @return $this
     */
    public function setAudioCodec($audioCodec)
    {
        $this->audioCodec = $audioCodec;
        return $this;
    }

    /**
     * @param $audioKiloBitrate
     * @return $this
     */
    public function setAudioKiloBitrate($audioKiloBitrate)
    {
        $this->audioKiloBitrate = $audioKiloBitrate;
        return $this;
    }

    /**
     * @param $audioChannels
     * @return $this
     * @return $this
     */
    public function setAudioChannels($audioChannels)
    {
        $this->audioChannels = $audioChannels;
        return $this;
    }


    function getFormats()
    {
        $formats = array();
        $formats = array_merge($formats, array('-vcodec', $this->videoCodec));
        $formats = array_merge($formats, array('-acodec', $this->audioCodec));

        if (null !== $this->videoKiloBitrate) {
            $formats = array_merge($formats, array('-b:v', $this->videoKiloBitrate . 'k'));
        }

        if (null !== $this->audioKiloBitrate) {
            $formats = array_merge($formats, array('-b:a', $this->audioKiloBitrate . 'k'));
        }

        if (null !== $this->audioChannels) {
            $formats[] = '-ac';
            $formats[] = $this->audioChannels;
        }

        if (null !== $this->additionalParamaters) {
            foreach ($this->additionalParamaters as $additionalParameter) {
                $formats[] = $additionalParameter;
            }
        }

        return $formats;
    }

    public static function create()
    {
        return new static();
    }

}