<?php
namespace FFMpegPush;


use Evenement\EventEmitter;
use FFMpegPush\Command\FFProbeCommand;
use FFMpegPush\Exception\RuntimeException;
use FFMpegPush\Listeners\ListenerInterface;

/**
 * 推流监听。
 *      计算推流进度等度量信息
 *
 * Class PushProgressListener
 * @package FFMpegPush
 */
class PushProgressListener extends EventEmitter implements ListenerInterface
{
    /** @var integer */
    private $duration;

    /** @var integer */
    private $totalSize;

    /** @var integer */
    private $currentSize;

    /** @var integer */
    private $currentTime;

    /** @var double */
    private $lastOutput = null;

    /** @var FFProbeCommand */
    private $ffprobe;

    /** @var string */
    private $pathfile;


    /** @var Boolean */
    private $initialized = false;

    /**
     *  rate in kb/s
     *
     * @var integer
     */
    private $rate;

    /**
     * Percentage of transcoding progress (0 - 100)
     *
     * @var integer
     */
    private $percent = 0;

    /**
     * Time remaining (seconds)
     *
     * @var integer
     */
    private $remaining = null;

    /**
     * @param string $pathfile
     */
    public function setPathfile($pathfile)
    {
        $this->pathfile = $pathfile;
        return $this;
    }


    public function __construct(FFProbeCommand $ffprobe, $pathfile)
    {
        $this->ffprobe = $ffprobe;
        $this->pathfile = $pathfile;
    }

    /**
     * @return FFProbeCommand
     */
    public function getFFProbe()
    {
        return $this->ffprobe;
    }

    /**
     * @return string
     */
    public function getPathfile()
    {
        return $this->pathfile;
    }

    /**
     * @return int
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($type, $data)
    {
        if (null !== $progress = $this->parseProgress($data)) {
            $this->emit('progress', array_values($progress));
        }
    }

    /**
     * @return string
     */
    protected function getPattern()
    {
        return '/size=(.*?) time=(.*?) /';
    }

    /**
     * @param $progress
     * @return array|null|void
     */
    private function parseProgress($progress)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        if (null === $this->totalSize || null === $this->duration) {
            return;
        }

        $matches = array();

        if (preg_match($this->getPattern(), $progress, $matches) !== 1) {
            return null;
        }

        $currentDuration = $this->convertDuration($matches[2]);
        $currentTime = microtime(true);
        $currentSize = trim(str_replace('kb', '', strtolower(($matches[1]))));
        $percent = max(0, min(1, $currentDuration / $this->duration));

        if ($this->lastOutput !== null) {
            $delta = $currentTime - $this->lastOutput;

            // Check the type of the currentSize variable and convert it to an integer if needed.
            if (!is_numeric($currentSize)) {
                $currentSize = (int)$currentSize;
            }

            $deltaSize = $currentSize - $this->currentSize;
            $rate = $deltaSize * $delta;
            if ($rate > 0) {
                $totalDuration = $this->totalSize / $rate;
                $this->remaining = floor($totalDuration - ($totalDuration * $percent));
                $this->rate = floor($rate);
            } else {
                $this->remaining = 0;
                $this->rate = 0;
            }
        }

        $this->percent = floor($percent * 100);
        $this->lastOutput = $currentTime;
        $this->currentSize = (int)$currentSize;
        $this->currentTime = $currentDuration;

        return $this->getProgressInfo();
    }

    /**
     *
     * @param  string $rawDuration in the format 00:00:00.00
     * @return number
     */
    private function convertDuration($rawDuration)
    {
        $ar = array_reverse(explode(":", $rawDuration));
        $duration = floatval($ar[0]);
        if (!empty($ar[1])) {
            $duration += intval($ar[1]) * 60;
        }
        if (!empty($ar[2])) {
            $duration += intval($ar[2]) * 60 * 60;
        }

        return $duration;
    }

    /**
     * @return array
     */
    private function getProgressInfo()
    {
        if ($this->remaining === null) {
            return null;
        }

        return array(
            'percent' => $this->percent,
            'remaining' => $this->remaining,
            'rate' => $this->rate,
            'pushInfo' => $this->getPushInfo()
        );
    }


    private function initialize()
    {
        try {
            $format = $this->ffprobe->format($this->pathfile);
        } catch (RuntimeException $e) {
            return;
        }

        if (false === $format->has('size') || false === $format->has('duration')) {
            return;
        }

        $this->totalSize = $format->get('size') / 1024;
        $this->duration = $format->get('duration');

        $this->initialized = true;
    }

    /**
     * @return PushInfo
     */
    public function getPushInfo()
    {
        return PushInfo::create()
            ->setCurrentSize($this->currentSize)
            ->setCurrentTime($this->currentTime)
            ->setDuration($this->duration)
            ->setPathfile($this->pathfile)
            ->setRate($this->rate)
            ->setRemaining($this->remaining)
            ->setPercent($this->percent)
            ->setTotalSize($this->totalSize);
    }

    /**
     * @param FFProbeCommand $ffprobe
     * @param string $pathfile
     * @return static
     */
    public static function create(FFProbeCommand $ffprobe, $pathfile = "")
    {
        return new static($ffprobe, $pathfile);
    }
}