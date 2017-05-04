<?php
namespace FFMpegPush;

use FFMpegPush\Command\FFMpegCommand;
use FFMpegPush\Command\FFProbeCommand;
use Psr\Log\LoggerInterface;

class PushVideo extends FFMpegCommand
{
    /** @var $input PushInput */
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
     * @param $input
     */
    public function __construct($configuration = array(), LoggerInterface $logger = null)
    {
        parent::__construct($configuration, $logger);
        $this->progressListener = PushProgressListener::create(FFProbeCommand::create($configuration, $logger));
        $this->addListerner($this->progressListener);
    }

    public function push()
    {
        $this->progressListener->setPathfile($this->input->getInputVideo());
        return $this->command(array_merge(
            $this->input->getInputs(),
            $this->format->getFormats(),
            $this->output->getOutPuts()
        ));
    }


    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        return new static($configuration, $logger);
    }

    public function onPregress(callable $listener)
    {
        return $this->progressListener->on('progress', $listener);
    }
}