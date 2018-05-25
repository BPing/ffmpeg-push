<?php

namespace FFMpegPush;

/**
 * 输出部分命令参数构造器。
 *       -f flv output.
 *
 * @author cbping
 */
class PushOutput
{
    /**
     * 输出路径 如：rtmp://;http://.
     *
     * @var string
     */
    protected $pushUrl;

    /**
     * @param mixed $pushUrl
     *
     * @return PushOutput
     */
    public function setPushUrl($pushUrl)
    {
        $this->pushUrl = $pushUrl;

        return $this;
    }

    public function getOutPuts()
    {
        return [
            '-f',
            'flv',
            $this->pushUrl,
        ];
    }

    public static function create()
    {
        return new static();
    }
}
