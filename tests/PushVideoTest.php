<?php
namespace Test\FFMpegPush;

use FFMpegPush\Exception\FileException;
use FFMpegPush\Listeners\LineListener;
use Psr\Log\LogLevel;
use FFMpegPush\PushFormat;
use FFMpegPush\PushInput;
use FFMpegPush\PushOutput;
use FFMpegPush\PushVideo;

class PushVideoTest extends \PHPUnit_Framework_TestCase
{

    public function testPush()
    {
        $pushUrl = 'rtmp://';
        $pushCmd = PushVideo::create();

        $debugListener = new LineListener();
        $pushCmd->addListener($debugListener);
        $lastStr = LastMsg::create();
        $debugListener->on('debug', function ($line) use ($lastStr) {
            if (is_string($line) && '' !== $line && "" !== $line) {
                $lastStr->strMsg = $line;
            }
            return;
        });

        $pushCmd->onProgress(function ($percent,$remaining,$rate) {
            echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
        });

        $pushCmd->setInput(PushInput::create()->setInputVideo('res/test.mp4'))
            ->setFormat(PushFormat::create())
            ->setOutput(PushOutput::create()->setPushUrl($pushUrl))
            ->push();

        $this->assertTrue(!$pushCmd->isSuccessful(), 'cmd should run fail');
        $this->assertTrue(!$pushCmd->getExitCode() !== 1);
        $this->assertContains(': Unknown error occurred', $lastStr->strMsg, 'Unknown error occurred');

    }

    /**
     * @expectedException FFMpegPush\Exception\FileException
     * @expectedExceptionMessage Not found or Not a MP4 file format
     */
    public function testInputFile()
    {
        $pushUrl = 'rtmp://';
        $pushCmd = PushVideo::create();
        $pushCmd->setInput(PushInput::create()->setInputVideo('test.mp4'))
            ->setFormat(PushFormat::create())
            ->setOutput(PushOutput::create()->setPushUrl($pushUrl))
            ->push();
    }
}


class LastMsg
{
    public $strMsg = '';

    public static function create()
    {
        return new static();
    }
}
