<?php
/**
 * Created by PhpStorm.
 * User: cbping-user
 * Date: 2017/5/5
 * Time: 10:17.
 */

namespace FFMpegPush\FFProbe;

use FFMpegPush\Command\FFProbeCommand;
use FFMpegPush\Exception\InvalidArgumentException;

class DataHandler
{
    /**
     * @param $type
     * @param $data
     *
     * @return Format|StreamCollection
     */
    public function map($type, $data)
    {
        switch ($type) {
            case FFProbeCommand::TYPE_FORMAT:
                return $this->mapFormat($data);
            case FFProbeCommand::TYPE_STREAMS:
                return $this->mapStreams($data);
            default:
                throw new InvalidArgumentException(sprintf(
                    'Invalid type `%s`.', $type
                ));
        }
    }

    private function mapFormat($data)
    {
        return new Format($data['format']);
    }

    private function mapStreams($data)
    {
        $streams = new StreamCollection();

        foreach ($data['streams'] as $properties) {
            $streams->add(new Stream($properties));
        }

        return $streams;
    }
}
