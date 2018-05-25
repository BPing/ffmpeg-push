<?php

namespace FFMpegPush\Command;

use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use FFMpegPush\Exception\FileException;
use FFMpegPush\Exception\RuntimeException;
use FFMpegPush\FFProbe\DataHandler;
use FFMpegPush\FFProbe\Format;
use FFMpegPush\FFProbe\StreamCollection;
use Psr\Log\LoggerInterface;

class FFProbeCommand extends Command
{
    const TYPE_STREAMS = 'streams';
    const TYPE_FORMAT = 'format';
    /** @var DataHandler $dataHandler */
    private $dataHandler;

    public function __construct($configuration = [], LoggerInterface $logger = null)
    {
        $this->name = 'FFProbe';
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }
        $configuration->set('binaries', $configuration->get('ffprobe.binaries', ['ffprobe']));
        parent::__construct($configuration, $logger);
        $this->dataHandler = new DataHandler();
    }

    public static function create($configuration = [], LoggerInterface $logger = null)
    {
        return new static ($configuration, $logger);
    }

    public function format($pathfile)
    {
        return $this->probe($pathfile, '-show_format', static::TYPE_FORMAT);
    }

    public function streams($pathfile)
    {
        return $this->probe($pathfile, '-show_streams', static::TYPE_STREAMS);
    }

    /**
     * @param $pathfile
     * @param $command
     * @param $type
     *
     * @throws FileException
     *
     * @return Format|StreamCollection
     */
    private function probe($pathfile, $command, $type)
    {
        if (!is_file($pathfile)) {
            throw new FileException('File 【'.$pathfile.'】 not found.');
        }

        $commands = [$pathfile, $command];
        // allowed in latest PHP-FFmpeg version
        $commands[] = '-print_format';
        $commands[] = 'json';

        try {
            $this->command($commands);
            $output = $this->getOutput();
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('Unable to probe %s', $pathfile), $e->getCode(), $e);
        }

        return $this->dataHandler->map($type, $this->parseJson($output));
    }

    private function parseJson($data)
    {
        $ret = @json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException(sprintf('Unable to parse json %s src %s', $ret, $data));
        }

        return $ret;
    }
}
