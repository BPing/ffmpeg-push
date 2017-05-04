<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/3
 * Time: 14:11
 */

namespace FFMpegPush;


use FFMpegPush\Exception\FileException;

class PushInput
{
    protected $time = 0;

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
     */
    public function setInputVideo($inputVideo)
    {
        $this->inputVideo = $inputVideo;
        return $this;
    }

    /**
     * @param int $time 秒
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }


    public function getInputs()
    {
        if ('' === $this->inputVideo
            || !is_file($this->inputVideo)
            || 'MP4' !== $this->get_extension($this->inputVideo)
        ) {
            throw new FileException("Not found or Not a MP4 file format");
        }
        return array(
            '-loglevel',
            'info',
            '-re',
            '-ss',
            '' . $this->time,
            '-i',
            $this->inputVideo,
        );
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