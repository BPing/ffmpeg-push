<?php

namespace FFMpegPush\Command;

interface CommandInterface
{
    /**
     * 执行命令
     *
     * @param mixed $command 命令参数
     * @return mixed
     */
    public function command($command);
}