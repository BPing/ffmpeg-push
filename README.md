# ffmpeg-push

    用ffmpeg命令推流库
    
# 快速开始

```php
require __DIR__ . '/trunk/vendor/autoload.php';

use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use FFMpegPush\PushVideo;

// 获取视频文件信息
///** @var  $ffprobe FFProbeCommand */
//$ffprobe = FFProbeCommand::create();
//var_dump($ffprobe->format('test/test.mp4'));
//var_dump($ffprobe->stream('test/test.mp4'));

// 推流
// ffmpeg -re  -i  \"test/test.mp4\" -c:v copy -c:a copy -f flv rtmp://pili-publish.heliwebs.com
$pushUrl = 'rtmp://pili-publish.heliwebs.com;
$pushCmd = PushVideo::create();
// 监听推流进度
$pushCmd->onPregress(function ($percent,$remaining,$rate) {
//    var_dump(func_get_args());
    echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
});
$pushinfo = $pushCmd->setInput(PushInput::create()->setInputVideo('test/test.mp4'))
    ->setFormat(PushFormat::create())
    ->setOutput(PushOutput::create()->setPushUrl($pushUrl))
    ->push();
    
//是否成功
  $pushinfo->isSuccessful()    
//输出
  $pushinfo->getOutput()    
//错误输出
  $pushinfo->getErrOutput()    
//执行返回码
  $pushinfo->getExitCode()
//执行返回码
  $pushinfo->getExitCode()
//目前推流时间，可以用中途断流重推起点时间
  $pushinfo->getCurrentTime()
//更多请看 PushInfo类  
        
```

# 安装

```cmd
composer require bping/ffmpeg-push
```



# 配置

### `binaries`:

> ffmpeg命令名称或者路径。如果想使用简单名称，记得把ffmpeg加入环境变量`PATH` 中。 默认值：ffmpeg

### `timeout`:

> 命令执行的超时时长，单位（s）.考虑到推流时长一般较长，所以默认值为一天


# 主要依赖

* [Symfony/Process](https://github.com/symfony/symfony/tree/master/src/Symfony/Component/Process)
