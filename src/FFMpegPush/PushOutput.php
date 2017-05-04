<?php
namespace FFMpegPush;


class PushOutput
{

    protected $pushUrl;

    /**
     * @param mixed $pushUrl
     */
    public function setPushUrl($pushUrl)
    {
        $this->pushUrl = $pushUrl;
        return $this;

    }

    public function getOutPuts()
    {
        return array(
            '-f',
            'flv',
            $this->pushUrl
        );
    }

    public static function create()
    {
        return new static();
    }

}