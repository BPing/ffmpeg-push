<?php
namespace FFMpegPush\Command;

use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use FFMpegPush\Exception\ConfigException;
use FFMpegPush\Exception\ExecutableNotFoundException;
use FFMpegPush\Exception\RuntimeException;
use FFMpegPush\Listeners\ListenerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class Command implements CommandInterface
{
    protected $name = '';

    /** @var  $process Process */
    protected $process;
    /** @var $config Configuration */
    protected $config;
    /** @var $logger  LoggerInterface */
    protected $logger;
    /** @var */
    protected $binary;
    /** one day */
    const TimeOut = 86400;
    /** @var $listeners ListenerInterface[] */
    protected $listeners = array();

    /**
     * Command constructor.
     * @param array $config
     *          array(
     *                "binarys"=>array(),   // 可执行命令,数组类型。只有一个有效，优先级和数组顺序一致
     *                "timeout"=>"", // 默认一天。如果非法格式，也就是非数字类型，都统一采用默认值
     *            )
     * @param $logger
     * @throws ConfigException
     * @throws ExecutableNotFoundException
     */
    public function __construct($config = array(), LoggerInterface $logger = null)
    {
        if (is_array($config)) {
            $this->config = new Configuration($config);
        } elseif ($config instanceof ConfigurationInterface) {
            $this->config = $config;
        } else {
            throw new ConfigException("config should not be null");
        }

        if (!$this->config->has('binaries')) {
            throw new ConfigException("config should has the key 'binaries' ");
        }
        if (!$this->config->has('timeout') || is_int($this->config->has('timeout'))) {
            $this->config->set('timeout', self::TimeOut);
        }

        $finder = new ExecutableFinder();
        $binary = null;
        $binaries = $this->config->get('binaries');
        $binaries = is_array($binaries) ? $binaries : array($binaries);

        foreach ($binaries as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                $binary = $candidate;
                break;
            }
            if (null !== $binary = $finder->find($candidate)) {
                break;
            }
        }
        if (null === $binary) {
            throw new ExecutableNotFoundException(sprintf(
                'Executable not found, proposed : %s', implode(', ', $binaries)
            ));
        }

        $this->binary = $binary;

        if (null === $logger) {
            $logger = new Logger(__NAMESPACE__ . ' logger');
            $logger->pushHandler(new NullHandler());
        }
        $this->logger = $logger;
    }

    /**
     * @param $command
     * @return Process
     */
    private function initProcess($command)
    {
        $processBuilder = ProcessBuilder::create($command)
            ->setPrefix($this->binary)
            ->setTimeout($this->config->get('timeout'));
        $this->process = $processBuilder->getProcess();

        return $this->process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param ListenerInterface $listener
     * @return $this
     */
    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * @return ListenerInterface[]
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param $command
     * @return string
     */
    public function command($command)
    {
        $process = $this->initProcess($command);
        if (!$process) {
            throw new RuntimeException('the process not exist');
        }
        $this->logger->info(sprintf(
            '%s running command %s', $this->name, $process->getCommandLine()
        ));
        $process->run($this->buildCallback($this->getListeners()));
        if (!$process->isSuccessful()) {
            $this->logger->error(sprintf(
                '%s failed to execute command %s error %s', $this->name, $process->getCommandLine(), $process->getErrorOutput()
            ));
        } else {
            $this->logger->info(sprintf('%s executed command successfully', $this->name));
        }
    }

    /**
     * 停止执行
     */
    public function stop()
    {
        if ($this->process) {
            $this->logger->info(sprintf('%s stopping.pid【%s】', $this->name, $this->process->getPid()));
            return $this->process->stop();
        }
        return 0;
    }

    /**
     * @param $listeners
     * @return \Closure
     */
    private function buildCallback($listeners)
    {
        return function ($type, $data) use ($listeners) {
            foreach ($listeners as $listener) {
                $listener->handle($type, $data);
            }
        };
    }

    public function getExitCode()
    {
        return $this->process->getExitCode();
    }

    public function getExitCodeText()
    {
        return $this->process->getExitCodeText();
    }

    public function getErrorOutput()
    {
        return $this->process->getErrorOutput();
    }

    public function getOutput()
    {
        return $this->process->getOutput();
    }

    public function isSuccessful()
    {
        return $this->process->isSuccessful();
    }

    public function clear()
    {

    }
}