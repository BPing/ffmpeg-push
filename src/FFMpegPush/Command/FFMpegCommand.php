<?php
namespace FFMpegPush\Command;

use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use Psr\Log\LoggerInterface;

class FFMpegCommand extends Command
{
    /**
     * FFMpegCommand constructor.
     */
    public function __construct($configuration = array(), LoggerInterface $logger = null)
    {
        $this->name = 'FFMpeg';
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }
        $configuration->set('binaries', $configuration->get('binaries', array('ffmpeg')));
        parent::__construct($configuration, $logger);
    }

    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        return new static($configuration, $logger);
    }
}