<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/3
 * Time: 14:11.
 */

namespace FFMpegPush;

use FFMpegPush\Exception\FileException;

/**
 * 输入部分命令参数构造器。
 *       -re -ss -i inputpath.
 *
 * Class PushInput
 */
class PushInput
{
    /**
     * 开始推流时间点（秒）或者 hh:mm:ss.xxx.
     *
     * @var int|string
     */
    protected $startTime = 0;

    /**
     * 输入文件.
     *
     * @var string
     */
    protected $inputVideo = '';

    /**
     * @return string
     */
    public function getInputVideo()
    {
        return $this->inputVideo;
    }

    /**
     * @param string $inputVideo
     *
     * @return PushInput
     */
    public function setInputVideo($inputVideo)
    {
        $this->inputVideo = $inputVideo;

        return $this;
    }

    /**
     * @param int|string $startTime 秒
     *
     * @return PushInput
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * 返回输入部分构造完成参数数组.
     *
     * @used PushVideo
     *
     * @return array
     */
    public function getInputs()
    {
        if ('' === $this->inputVideo
            || !is_file($this->inputVideo)
            || 'MP4' !== $this->get_extension($this->inputVideo)
        ) {
            throw new FileException('Not found or Not a MP4 file format');
        }

        return [
            '-loglevel',
            'info',
            '-re',
            '-ss',
            ''.$this->startTime,
            '-i',
            $this->inputVideo,
        ];
    }

    private function get_extension($file)
    {
        $tmp = explode('.', $file);

        return strtoupper(end($tmp));
    }

    public static function create()
    {
        return new static();
    }
}
