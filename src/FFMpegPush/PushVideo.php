<?php
namespace FFMpegPush;

use FFMpegPush\Command\FFMpegCommand;
use FFMpegPush\Command\FFProbeCommand;
use Psr\Log\LoggerInterface;

/**
 * 推流命令
 *
 * Class PushVideo
 * @package FFMpegPush
 */
class PushVideo extends FFMpegCommand
{
    /**
     * 构造输入部分命令
     *
     * @var $input PushInput
     */
    protected $input;
    /** @var  PushFormat */
    protected $format;
    /** @var  PushOutput */
    protected $output;
    /** @var  PushProgressListener */
    protected $progressListener;

    /**
     * @param PushInput $input
     * @return $this
     */
    public function setInput(PushInput $input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @param mixed $format
     * @return $this
     */
    public function setFormat(PushFormat $format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param mixed $output
     * @return $this
     */
    public function setOutput(PushOutput $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * PushVideo constructor.
     * @param array $configuration
     * @param LoggerInterface|null $logger
     */
    public function __construct($configuration = array(), LoggerInterface $logger = null)
    {
        parent::__construct($configuration, $logger);
        $this->progressListener = PushProgressListener::create(FFProbeCommand::create($configuration, $logger));
        $this->addListener($this->progressListener);
    }

    /**
     * 推流
     *
     * @notice 阻塞
     * @return PushInfo
     */
    public function push()
    {
        $this->progressListener->setPathfile($this->input->getInputVideo());
        $this->command(array_merge(
            $this->input->getInputs(),
            $this->format->getFormats(),
            $this->output->getOutPuts()
        ));
        return $this->getPushInfo();
    }


    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        return new static($configuration, $logger);
    }

    /**
     * 监听推流进度
     *
     * @param callable $listener
     */
    public function onPregress(callable $listener)
    {
        $this->progressListener->on('progress', $listener);
    }

    /**
     * 获取此时推流的一些状态值
     *
     * @return PushInfo
     */
    public function getPushInfo()
    {
        return $this->progressListener->getPushInfo()
            ->setProcess($this->getProcess());
    }
}