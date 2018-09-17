<?php

namespace FFMpegPush\Listeners;

use Evenement\EventEmitterInterface;

interface ListenerInterface extends EventEmitterInterface
{
    /**
     * @param string $type The data type, one of Process::ERR, Process::OUT constants
     * @param string $data The output
     */
    public function handle($type, $data);
}
