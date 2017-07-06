<?php
namespace Test\FFMpegPush;

use FFMpegPush\Command\FFProbeCommand;
use FFMpegPush\Listeners\LineListener;
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

        $pushCmd->onProgress(function ($percent, $remaining, $rate) {
            echo "progress:$percent% remaining:$remaining(s) rate:$rate(kb/s)\n";
        });

        $pushCmd->setInput(PushInput::create()->setInputVideo('res/test.mp4'))
            ->setFormat(PushFormat::create())
            ->setOutput(PushOutput::create()->setPushUrl($pushUrl));

        echo $pushCmd->getCommandLine();
        $pushCmd->push();
        $this->assertTrue(!$pushCmd->isSuccessful(), 'cmd should run fail');
        $this->assertTrue(!$pushCmd->getExitCode() !== 1);
        $this->assertNotEmpty($pushCmd->getCommandLine(), 'cmd should not empty');
        $this->assertNotEmpty($pushCmd->getExitCodeText() == 'General error', 'ExitCodeText:General error');
        $this->assertContains(': Unknown error occurred', $lastStr->strMsg, 'Unknown error occurred');
        $this->assertContains(': Unknown error occurred', $pushCmd->getErrorOutput(), 'error output Unknown error occurred');
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

    /**
     * @expectedException FFMpegPush\Exception\ConfigException
     * @expectedExceptionMessage input|format|output should not be null.
     */
    public function testPushException()
    {
        $pushCmd = PushVideo::create();
        $pushCmd->push();
    }

    /**
     * @expectedException FFMpegPush\Exception\FileException
     * @expectedExceptionMessage File 【../res/test.mp4】 not found.
     */
    public function testFFprobeException()
    {
        $ffprobe = FFProbeCommand::create();
        $ffprobe->streams('../res/test.mp4');
    }

    /**
     * ffprobe
     */
    public function testFFprobe()
    {
        $ffprobe = FFProbeCommand::create();
        $streams = $ffprobe->streams('res/test.mp4');
        $video = $streams->videos()->all()[0];
        $audio = $streams->audios()->first();

        $this->assertTrue($streams->count() == 2, 'two streams');
        $this->assertTrue($streams->getIterator()->count() == 2, 'getIterator:two streams');

        $this->assertTrue($video->get('codec_name') == 'h264', 'codec_name:h264');
        $this->assertTrue($video->get('r_frame_rate') == '25/1', 'r_frame_rate:25/1');

        $this->assertTrue($audio->get('codec_name') == 'aac', 'codec_name:aac');
        $this->assertTrue($audio->get('sample_rate') == '44100', 'sample_rate:44100');

        $format = $ffprobe->format('res/test.mp4');
        $this->assertTrue($format->get('filename') == 'res/test.mp4', 'filename:res/test.mp4');
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
