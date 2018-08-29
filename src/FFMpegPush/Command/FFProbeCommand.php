<?php

namespace FFMpegPush\Command;

use FFMpegPush\Exception\FileException;
use FFMpegPush\Exception\RuntimeException;
use FFMpegPush\FFProbe\DataHandler;
use FFMpegPush\FFProbe\Format;
use FFMpegPush\FFProbe\StreamCollection;
use FFMpegPush\VideoInfo;
use FFMpegPush\Configuration;
use FFMpegPush\ConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * FFProbe 可执行命令
 *
 * @package FFMpegPush\Command
 */
class FFProbeCommand extends Command
{
    const TYPE_STREAMS = 'streams';
    const TYPE_FORMAT  = 'format';
    /** @var   DataHandler $dataHandler */
    private $dataHandler;

    /**
     * 构造函数
     *
     * FFProbeCommand constructor.
     * @param array $configuration
     * @param LoggerInterface|null $logger
     */
    public function __construct($configuration = array(), LoggerInterface $logger = null)
    {
        $this->name = 'FFProbe';
        if (!$configuration instanceof ConfigurationInterface) {
            $configuration = new Configuration($configuration);
        }
        $configuration->set('binaries', $configuration->get('ffprobe.binaries', array('ffprobe')));
        parent::__construct($configuration, $logger);
        $this->dataHandler = new DataHandler();
    }

    /**
     * @param array $configuration
     * @param LoggerInterface|null $logger
     * @return static
     */
    public static function create($configuration = array(), LoggerInterface $logger = null)
    {
        return new static ($configuration, $logger);
    }

    /**
     * 获取多媒体的封装格式
     *
     * @param $pathfile
     * @return Format|StreamCollection
     */
    public function format($pathfile)
    {
        return $this->probe($pathfile, '-show_format', static::TYPE_FORMAT);
    }

    /**
     * 查看多媒体文件中的流信息
     *
     * 如图所示，可以看到流的信息：
     *
     * <table border="1" cellspacing="0" cellpadding="0" style="border:none;">
     * <tbody>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">属性</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">说明</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">值</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Index</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">流所在的索引区域</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>0</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Codec_name</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码名</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Mpeg4</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Codec_long_name</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码全名</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>MPEG-4 part 2</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>profile</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码的</span><span>profile </span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Simple Profile</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>level</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码的</span><span>level</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>1</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Has_b_frames</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">包含</span><span>B</span><span style="font-family:宋体;">帧信息</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>0</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Codec_tyoe</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码类型</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Video</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Codec_time_base</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码的时间戳计算基础单位</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>1/15</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Pix_fmt</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">图像显示图像色彩格式</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Yuv420p</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Coded_width</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">图像的宽度</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>608</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Coded_height</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">图像的高度</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>320</span>
     * </p>
     * </td>
     * </tr>
     * <tr>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Codec_tag_string</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span style="font-family:宋体;">编码的标签数据</span>
     * </p>
     * </td>
     * <td width="142" valign="top" style="border:solid windowtext 1.0pt;">
     * <p>
     * <span>Mp4v</span>
     * </p>
     * </td>
     * </tr>
     * </tbody>
     * </table>
     *
     * @param $pathfile
     * @return Format|StreamCollection
     */
    public function streams($pathfile)
    {
        return $this->probe($pathfile, '-show_streams', static::TYPE_STREAMS);
    }

    /**
     * @param $pathfile
     * @param $command
     * @param $type
     * @return Format|StreamCollection
     * @throws FileException
     */
    private function probe($pathfile, $command, $type)
    {
        if (!is_file($pathfile)) {
            throw new FileException('File 【' . $pathfile . "】 not found.");
        }

        $commands = array($pathfile, $command);
        // allowed in latest PHP-FFmpeg version
        $commands[] = '-print_format';
        $commands[] = 'json';
        try {
            $this->command($commands);
            $output = $this->getOutput();
        } catch (\Exception $e) {
            throw new RuntimeException(sprintf('Unable to probe %s', $pathfile), $e->getCode(), $e);
        }
        return $this->dataHandler->map($type, $this->parseJson($output));
    }

    private function parseJson($data)
    {
        $ret = @json_decode($data, true);
        if (JSON_ERROR_NONE!==json_last_error()) {
            throw new RuntimeException(sprintf('Unable to parse json %s src %s', $ret, $data));
        }
        return $ret;
    }
}