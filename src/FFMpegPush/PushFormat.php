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

    // 音频常用采样率
    const SAMPLE_RATE_44_1_KHZ  = 44100;
    const SAMPLE_RATE_48_KHZ    = 48000;
    const SAMPLE_RATE_22_05_KHZ = 22050;
    const SAMPLE_RATE_FM        = self::SAMPLE_RATE_22_05_KHZ; //FM广播的声音品质
    const SAMPLE_RATE_CD        = self::SAMPLE_RATE_44_1_KHZ;  //CD音质
    const SAMPLE_RATE_DVD       = self::SAMPLE_RATE_48_KHZ;    //DVD音质

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
    protected $videoKiloBitRate = null;


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
    protected $audioKiloBitRate = null;

    /**
     *  音频输出通道
     *
     * @var integer
     */
    protected $audioChannels = null;

    /**
     * 音频采样率
     *
     * @var integer
     */
    protected $audioSampleRate = null;

    /**
     * 额外参数，作为补充
     *
     * @var Array
     */
    protected $additionalParameters = null;

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
     * @param int $videoKiloBitRate
     * @return $this
     */
    public function setVideoKiloBitrate($videoKiloBitRate)
    {
        $this->videoKiloBitRate = $videoKiloBitRate;
        return $this;
    }

    /**
     * @param array $additionalParameters
     * @return $this
     */
    public function setAdditionalParamaters(array $additionalParameters)
    {
        $this->additionalParameters = $additionalParameters;
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
     * @param $audioKiloBitRate
     * @return $this
     */
    public function setAudioKiloBitrate($audioKiloBitRate)
    {
        $this->audioKiloBitRate = $audioKiloBitRate;
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
     * @param $audioSampleRate
     * @return $this
     */
    public function setAudioSampleRate($audioSampleRate)
    {
        $this->audioSampleRate = $audioSampleRate;
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

        if (null !== $this->videoKiloBitRate) {
            $formats = array_merge($formats, array('-b:v', $this->videoKiloBitRate . 'k'));
        }

        if (null !== $this->audioKiloBitRate) {
            $formats = array_merge($formats, array('-b:a', $this->audioKiloBitRate . 'k'));
        }

        if (null !== $this->audioKiloBitRate) {
            $formats[] = '-ar';
            $formats[] = $this->audioSampleRate;
        }
        if (null !== $this->audioChannels) {
            $formats[] = '-ac';
            $formats[] = $this->audioChannels;
        }

        if (null !== $this->additionalParameters) {
            foreach ($this->additionalParameters as $additionalParameter) {
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