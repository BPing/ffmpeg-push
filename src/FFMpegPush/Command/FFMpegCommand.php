<?php

namespace FFMpegPush\Command;

use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use Psr\Log\LoggerInterface;

class FFMpegCommand extends Command
{
    /**
     * FFMpegCommand constructor.
     *
     * @param array                $configuration
     * @param LoggerInterface|null $logger
     */
    public function __construct($configuration = [], LoggerInterface $logger = null)
    {
        $this->name = 'FFMpeg';
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }
        $configuration->set('binaries', $configuration->get('ffmpeg.binaries', ['ffmpeg']));
        parent::__construct($configuration, $logger);
    }

    public static function create($configuration = [], LoggerInterface $logger = null)
    {
        return new static($configuration, $logger);
    }
}
