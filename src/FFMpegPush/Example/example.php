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

$pushCmd->onPregress(function ($percent,$remaining,$rate) {
//    var_dump(func_get_args());
    echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
});

$res = $pushCmd->setInput(PushInput::create()->setInputVideo('../../../res/test.mp4'))
    ->setFormat(PushFormat::create())
    ->setOutput(PushOutput::create()->setPushUrl($pushUrl))
    ->push();

var_dump($res);
echo $pushCmd->getProcess()->getCommandLine();
echo $pushCmd->getExitCode();