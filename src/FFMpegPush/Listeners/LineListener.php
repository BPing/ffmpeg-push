<?php

namespace FFMpegPush\Listeners;

use Evenement\EventEmitter;
use Symfony\Component\Process\Process;

class LineListener extends EventEmitter implements ListenerInterface
{
    private $eventOut;
    private $eventErr;

    public function __construct($eventOut = 'debug', $eventErr = 'debug')
    {
        $this->eventOut = $eventOut;
        $this->eventErr = $eventErr;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($type, $data)
    {
        if (Process::ERR === $type) {
            $this->emitLines($this->eventErr, $data);
        } elseif (Process::OUT === $type) {
            $this->emitLines($this->eventOut, $data);
        }
    }

    private function emitLines($event, $lines)
    {
        foreach (explode("\n", $lines) as $line) {
            $this->emit($event, [$line]);
        }
    }
}
