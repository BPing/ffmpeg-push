#ffmpeg-push

    用ffmpeg命令推流库
    
# 快速开始

```php
require __DIR__ . '/trunk/vendor/autoload.php';

use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use FFMpegPush\PushVideo;

///** @var  $ffprobe FFProbeCommand */
//$ffprobe = FFProbeCommand::create();
//var_dump($ffprobe->format('test/test.mp4'));

$pushUrl = 'rtmp://pili-publish.heliwebs.com;

$pushCmd = PushVideo::create();

$pushCmd->onPregress(function ($percent,$remaining,$rate) {
//    var_dump(func_get_args());
    echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
});

$res = $pushCmd->setInput(PushInput::create()->setInputVideo('test/test.mp4'))
    ->setFormat(PushFormat::create())
    ->setOutput(PushOutput::create()->setPushUrl($pushUrl))
    ->push();

var_dump($res);
echo $pushCmd->getProcess()->getCommandLine();
echo $pushCmd->getExitCode();

```

# 配置

### `binaries`:

> 运行的命令名称或者路径

### `timeout`:

> 命令执行的超时时长，单位（s）.考虑到推流时长一般较长，所以默认值为一天


# 主要依赖

* [Symfony/Process](https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Process)
