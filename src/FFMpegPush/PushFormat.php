<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/3
 * Time: 14:11
 */

namespace FFMpegPush;

/**
 * 处理部分命令参数构造器。
 *      -vcodec -acodec -v:b  -a:b ....
 *
 *   对输入的视频文件进行转码等处理
 *
 * @author cbping
 * @package FFMpegPush
 */
class PushFormat
{
    // 视频转码
    const CODE_V_H264 = 'h264';
    const CODE_V_COPY = 'copy';

    // 音频转码
    const CODE_A_AAC  = 'aac';
    const CODE_A_COPY = 'copy';


    /**
     * 视频转码格式
     *
     * @var string
     */
    protected $videoCodec = self::CODE_V_COPY;

    /**
     * 视频输出码率（K）
     *
     * @var Integer
     */
    protected $videoKiloBitrate = null;


    /**
     * 音频输出转码格式
     *
     * @var string
     */
    protected $audioCodec = self::CODE_A_COPY;

    /**
     *  音频输出码率（K）
     *
     * @var integer
     */
    protected $audioKiloBitrate = null;

    /**
     *  音频输出通道
     *
     * @var integer
     */
    protected $audioChannels = null;

    /**
     * 额外参数，作为补充
     *
     * @var Array
     */
    protected $additionalParamaters = null;

    /**
     * @param string $videoCodec
     * @return $this
     * @throws \Exception
     */
    public function setVideoCodec($videoCodec = self::CODE_V_COPY)
    {
        $codeArr = [self::CODE_V_COPY, self::CODE_V_H264];
        if (!in_array($videoCodec, $codeArr)) {
            throw new \Exception('the video encoding format [' . $videoCodec . '] is not supported. support ['
                . implode(',', $codeArr) . ']');
        }
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
     * @param string $audioCodec
     * @return $this
     * @throws \Exception
     */
    public function setAudioCodec($audioCodec = self::CODE_A_COPY)
    {
        $codeArr = [self::CODE_A_COPY, self::CODE_A_AAC];
        if (!in_array($audioCodec, $codeArr)) {
            throw new \Exception('the audio encoding format [' . $audioCodec . '] is not supported. support ['
                . implode(',', $codeArr) . ']');
        }
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

    /**
     * @return array
     */
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