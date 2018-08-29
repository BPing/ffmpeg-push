<?php

namespace FFMpegPush\Command;

use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 *  ffmpeg 可执行命令
 *
 * @package FFMpegPush\Command
 */
class FFMpegCommand extends Command
{
    /**
     * FFMpegCommand constructor.
     * @param array $configuration
     * @param LoggerInterface|null $logger
     */
    public function __construct($configuration = array(), LoggerInterface $logger = null)
    {
        $this->name = 'FFMpeg';
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }
        $configuration->set('binaries', $configuration->get('ffmpeg.binaries', array('ffmpeg')));
        parent::__construct($configuration, $logger);
    }

    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        return new static($configuration, $logger);
    }
}