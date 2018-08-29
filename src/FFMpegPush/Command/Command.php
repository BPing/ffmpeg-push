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

/**
 * Class Command 命令基本类
 *
 * @package FFMpegPush\Command
 */
class Command implements CommandInterface
{
    /**
     * 名字
     *
     * @var string
     */
    protected $name = '';

    /**
     * 底层命令进程句柄。
     *
     * @var  $process Process
     */
    protected $process;

    /**
     * 命令配置信息
     *
     * @var $config Configuration
     */
    protected $config;

    /** @var $logger  LoggerInterface */
    protected $logger;

    /**
     * 命令可执行文件目录
     *
     * @var string
     */
    protected $binary;

    /**
     * 命令执行超时时间. 默认一天
     */
    const TimeOut = 86400;

    /**
     * 监听者集合。
     *   监听命令执行进度
     *
     * @var $listeners ListenerInterface[]
     */
    protected $listeners = array();

    /**
     * Command constructor.
     *
     * @param array $config
     *<code>
     * <?php
     *          array(
     *                "binarys"=>array(),   // 可执行命令,数组类型。只有一个有效，优先级和数组顺序一致
     *                "timeout"=>"", // 默认一天。如果非法格式，也就是非数字类型，都统一采用默认值
     *            )
     *
     * </code>
     * @param       $logger
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

        $finder   = new ExecutableFinder();
        $binary   = null;
        $binaries = $this->config->get('binaries');
        $binaries = is_array($binaries) ? $binaries : array($binaries);

        foreach ($binaries as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                $binary = $candidate;
                break;
            }
            if (null!==$binary = $finder->find($candidate)) {
                break;
            }
        }
        if (null===$binary) {
            throw new ExecutableNotFoundException(sprintf(
                'Executable not found, proposed : %s', implode(', ', $binaries)
            ));
        }

        $this->binary = $binary;

        if (null===$logger) {
            $logger = new Logger(__NAMESPACE__ . ' logger');
            $logger->pushHandler(new NullHandler());
        }
        $this->logger = $logger;
    }

    /**
     * 初始进程句柄
     *
     * @param $command
     * @return Process
     */
    protected function initProcess($command)
    {
        if (!is_null($command)) {
            $processBuilder = ProcessBuilder::create($command)
                ->setPrefix($this->binary)
                ->setTimeout($this->config->get('timeout'));
            $this->process  = $processBuilder->getProcess();
        }
        return $this->process;
    }

    /**
     * 获取底层命令执行进程句柄
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * 添加监听者
     *
     * @param ListenerInterface $listener
     * @return $this
     */
    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * 获取所有监听者
     *
     * @return ListenerInterface[]
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * 执行命令
     *
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
     * 返回监听回调函数体
     *
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

    /**
     * 获取执行结果代码
     *
     * @return int|null
     */
    public function getExitCode()
    {
        if ($this->process) {
            return $this->process->getExitCode();
        }
        return null;
    }

    /**
     * 获取执行命令具体文本信息
     *
     * @return null|string
     */
    public function getCommandLine()
    {
        if ($this->process) {
            return $this->process->getCommandLine();
        }
        return null;
    }

    /**
     * 获取执行结果代码文本信息
     *
     * @return null|string
     */
    public function getExitCodeText()
    {
        if ($this->process) {
            return $this->process->getExitCodeText();
        }
        return null;
    }

    /**
     * 获取错误输出内容
     *
     * @return null|string
     */
    public function getErrorOutput()
    {
        if ($this->process) {
            return $this->process->getErrorOutput();
        }
        return null;
    }

    /**
     * 获取输出内容
     *
     * @return null|string
     */
    public function getOutput()
    {
        if ($this->process) {
            return $this->process->getOutput();
        }
        return null;
    }

    /**
     * 是否执行成功
     *
     * @return bool|null
     */
    public function isSuccessful()
    {
        if ($this->process) {
            return $this->process->isSuccessful();
        }
        return null;
    }

    public function clear()
    {

    }
}