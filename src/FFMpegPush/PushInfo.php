<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/4
 * Time: 18:32.
 */

namespace FFMpegPush;

use Symfony\Component\Process\Process;

/**
 * 推流信息。
 */
class PushInfo
{
    /**
     * 底层执行.
     *
     * @var Process
     */
    private $process;

    /**
     * 文件（视频）.
     *
     * @var string
     */
    private $pathfile;

    /**
     * 视频时长（s）.
     *
     * @var float
     */
    private $duration;

    /**
     * 视频文件大小（Kb）.
     *
     * @var float
     * */
    private $totalSize;

    /** @var int */
    private $currentSize;

    /** @var int */
    private $currentTime;

    /**
     *  推流速率（Kb/s）.
     *
     * @var int
     */
    private $rate;

    /**
     * 推流进度百分比.
     *
     * @var int
     */
    private $percent = 0;

    /**
     * 按照当前速率，预计还需要多少秒.
     *
     * @var int
     */
    private $remaining = null;

    /**
     * @param mixed $process
     *
     * @return PushInfo
     */
    public function setProcess($process)
    {
        $this->process = $process;

        return $this;
    }

    public function isSuccessful()
    {
        if (!$this->process) {
            return;
        }

        return $this->process->isSuccessful();
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        if (!$this->process) {
            return;
        }

        return $this->process->getOutput();
    }

    /**
     * @return mixed
     */
    public function getErrOutput()
    {
        if (!$this->process) {
            return;
        }

        return $this->process->getErrorOutput();
    }

    /**
     * @return mixed
     */
    public function getExitCode()
    {
        if (!$this->process) {
            return;
        }

        return $this->process->getExitCode();
    }

    public function getCommandLine()
    {
        if (!$this->process) {
            return;
        }

        return $this->process->getCommandLine();
    }

    /**
     * @return int
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }

    /**
     * @param int $totalSize
     *
     * @return PushInfo
     */
    public function setTotalSize($totalSize)
    {
        $this->totalSize = $totalSize;

        return $this;
    }

    /**
     * @return int
     * @return PushInfo
     */
    public function getCurrentSize()
    {
        return $this->currentSize;
    }

    /**
     * @param int $currentSize
     *
     * @return $this
     */
    public function setCurrentSize($currentSize)
    {
        $this->currentSize = $currentSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * @param int $currentTime
     *
     * @return PushInfo
     */
    public function setCurrentTime($currentTime)
    {
        $this->currentTime = $currentTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathfile()
    {
        return $this->pathfile;
    }

    /**
     * @param string $pathfile
     *
     * @return PushInfo
     */
    public function setPathfile($pathfile)
    {
        $this->pathfile = $pathfile;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     *
     * @return PushInfo
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param int $rate
     *
     * @return PushInfo
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return int
     * @return PushInfo
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param int $percent
     *
     * @return PushInfo
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @return int
     */
    public function getRemaining()
    {
        return $this->remaining;
    }

    /**
     * @param int $remaining
     *
     * @return PushInfo
     */
    public function setRemaining($remaining)
    {
        $this->remaining = $remaining;

        return $this;
    }

    public static function create()
    {
        return new static();
    }
}
