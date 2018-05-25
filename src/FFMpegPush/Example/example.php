<?php

namespace FFMpegPush\Example;

use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use FFMpegPush\PushVideo;

///** @var  $ffprobe FFProbeCommand */
//$ffprobe = FFProbeCommand::create();
//var_dump($ffprobe->format('test.mp4'));

$pushUrl = 'rtmp://pili-publish.heliwebs.com';

$pushCmd = PushVideo::create();

$pushCmd->onProgress(function ($percent, $remaining, $rate) {
//    var_dump(func_get_args());
    echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
});

$pushCmd->setInput(
    PushInput::create()
        ->setStartTime(0)
        ->setInputVideo('res/test.mp4')
)
    ->setFormat(
        PushFormat::create()
            ->setVideoCodec(PushFormat::CODE_V_COPY)
    )
    ->setOutput(
        PushOutput::create()
            ->setPushUrl($pushUrl)
    );

echo $pushCmd->getCommandLine();

// 开始推流
$pushCmd->push();

echo $pushCmd->getErrorOutput();
echo "\n";
echo 'Exit Code: '.$pushCmd->getExitCode();
