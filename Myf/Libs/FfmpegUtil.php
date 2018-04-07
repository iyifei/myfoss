<?php
/**
 * ffmpeg操作类
 * User: myf
 * Date: 2018/3/29
 * Time: 21:35
 */

namespace Myf\Libs;


use Illuminate\Contracts\Logging\Log;

class FfmpegUtil
{

    /**
     * 生成视频缩略图并返回视频基本信息
     * @param string|url $videoFile 视频源文件或url地址
     * @param string $thumbFile 生成图片的文件地址
     * @param int $ss 截取视频第几秒的图片
     * @return array
     */
    public static function createVideoThumbAndGetInfo($videoFile,$thumbFile,$ss=3){
        $ret = null;
        if(is_file($videoFile)||strpos("#".$videoFile,"http")){
            //$command = sprintf('ffmpeg -y -ss 4 -t 8 -i "%s" -vf "select=eq(pict_type\\,I)" -vframes 1 -f image2 "%s" 2>&1', $videoFile,$thumbFile);
            if($ss>=1){
                $command = sprintf('ffmpeg -y -i "%s" -vframes 1 -ss %s "%s" 2>&1', $videoFile,$ss,$thumbFile);
            }else{
                $command = sprintf('ffmpeg -y -i "%s" -vframes 1 "%s" 2>&1', $videoFile,$thumbFile);
            }
            ob_start();
            passthru($command);
            $info = ob_get_contents();
            ob_end_clean();
            // 通过使用输出缓冲，获取到ffmpeg所有输出的内容。
            $video = array();
            // Duration: 01:24:12.73, start: 0.000000, bitrate: 456 kb/s
            if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
                $video['duration'] = $match[1]; // 提取出播放时间
                $da = explode(':', $match[1]);
                $video['seconds'] = $da[0] * 3600 + $da[1] * 60 + $da[2]; // 转换为秒
                $video['start'] = $match[2]; // 开始时间
                $video['bitrate'] = $match[3]; // bitrate 码率 单位 kb
            }

            // Stream #0.1: Video: rv40, yuv420p, 512x384, 355 kb/s, 12.05 fps, 12 tbr, 1k tbn, 12 tbc
            if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
                $video['vcodec'] = $match[1]; // 编码格式
                $video['vformat'] = $match[2]; // 视频格式
                $video['resolution'] = $match[3]; // 分辨率
                $a = explode('x', $match[3]);
                $video['width'] = $a[0];
                $video['height'] = $a[1];
            }

            // Stream #0.0: Audio: cook, 44100 Hz, stereo, s16, 96 kb/s
            if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
                $video['acodec'] = $match[1];       // 音频编码
                $video['asamplerate'] = $match[2];  // 音频采样频率
            }

            if (isset($video['seconds']) && isset($video['start'])) {
                $video['play_time'] = $video['seconds'] + $video['start']; // 实际播放时间
            }
            if(is_file($videoFile)){
                $video['size'] = filesize($videoFile); // 文件大小
            }
            $video['file']=$videoFile;

            //缩略图信息
            $thumbInfo = [];
            $thumbSize = getimagesize($thumbFile);
            if($thumbSize){
                $thumbInfo['width']=$thumbSize[0];
                $thumbInfo['height']=$thumbSize[1];
                $thumbInfo['mime']=$thumbSize['mime'];
                $thumbInfo['file']=$thumbFile;
                $thumbInfo['size']=filesize($thumbFile);
                //如果视频截取不到长宽,取截图的
                if($video['width']<=0){
                    $video['width']=$thumbSize[0];
                    $video['height']=$thumbSize[1];
                }
            }else{
                //如果播放时长小于三秒，取第一秒的内容
                if($ss>0 && $video['seconds']<$ss && $video['seconds']>=0){
                    return self::createVideoThumbAndGetInfo($videoFile,$thumbFile,0);
                }
            }

            $ret = [
                'video'=>$video,
                'thumb'=>$thumbInfo,
                'command'=>$command
            ];
        }

        return $ret;
    }

}