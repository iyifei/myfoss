<?php
/**
 * 本机存储器
 * User: myf
 * Date: 2018/3/29
 * Time: 15:56
 */

namespace Myf\Adapter;


use Intervention\Image\ImageManagerStatic;
use League\Flysystem\Adapter\Local;
use Myf\GEnum\ExtensionType;
use Myf\Libs\AdapterManager;
use Myf\Libs\FfmpegUtil;

class LocalAdapter extends Local
{
    private $domain;
    private $root;

    public function __construct($param) {
        $root = $param['root'];
        $this->root = $root;
        $this->domain = $param['domain'];
        $writeFlags = LOCK_EX;
        $linkHandling = self::DISALLOW_LINKS;
        $permissions = [];
        parent::__construct($root, $writeFlags, $linkHandling, $permissions);
    }

    public function getUrl($key){
        return sprintf("%s/oss/%s",$this->domain,$key);
    }

    /**
     * 获取文件基本信息
     * @param $key
     * @return array
     */
    public function getInfo($key){
        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];
        //本机文件
        $file = sprintf("%s/%s",$this->root,$key);
        $fs = AdapterManager::getFilesystem();
        $info = [
            'basename'=>$pathInfo['basename'],
            'filename'=>$pathInfo['filename'],
            'format'=>strtolower($ext),
            'mimetype'=>$fs->getMimetype($key),
            'key'=>$key,
            'size'=>intval(filesize($file)),
        ];
        //转换为文件格式
        $type = ExtensionType::switchType($ext);
        switch ($type){
            //图片
            case ExtensionType::IMAGE:
                $imageInfo = getimagesize($file);
                $info['width']=intval($imageInfo[0]);
                $info['height']=intval($imageInfo[1]);
                break;
            //视频
            case ExtensionType::VIDEO;
                $tmpPath = sys_get_temp_dir();
                $tmpFile = sprintf("%s/%s.jpg",rtrim($tmpPath,'/'),uniqid(rand(1,1000),true));
                $videoInfo = FfmpegUtil::createVideoThumbAndGetInfo($file,$tmpFile);
                if(is_file($tmpFile)){
                    //保存视频截图key
                    $originThumbKey = sprintf("%s.jpg",$key);
                    if(!$fs->has($originThumbKey)){
                        $stream = fopen($tmpFile,'r+');
                        $fs->putStream($originThumbKey,$stream);
                        if(is_resource($stream)){
                            fclose($stream);
                        }
                    }
                    //读取视频基本信息
                    $video = $videoInfo['video'];
                    $vcodeArr =explode(" ",$video['vcodec']);
                    $info['duration']=$video['duration'];
                    $info['seconds']=$video['seconds'];
                    $info['start']=$video['start'];
                    $info['width']=intval($video['width']);
                    $info['height']=intval($video['height']);
                    $info['vcodec']=trim($vcodeArr[0]);
                }
                break;
            default:
                $info['size'] = $fs->getSize($key);
                break;
        }
        return $info;

    }

    /**
     * 获取缩略图地址
     * @param $key
     * @param $param
     * @return string
     */
    public function getThumbnail($key,$param){
        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];
        $w = intval($param['w']);
        $h = intval($param['h']);
        if($w==0 && $h==0){
            $w = 100;
        }
        //缩略图key
        $thumbKey = sprintf("%s-%s-%s.jpg",$key,$w,$h);
        if(!$this->has($thumbKey)){
            //判断是否为视频格式
            if(ExtensionType::isSupportVideo($ext)){
                $originVideo = sprintf("%s/%s",$this->root,$key);
                //视频原始图片
                $originKey = sprintf("%s.jpg",$key);
                $originFile = sprintf("%s/%s",$this->root,$originKey);
                //如果视频原始图片不存在,调用ffmpeg截取
                if(!is_file($originFile)){
                    FfmpegUtil::createVideoThumbAndGetInfo($originVideo,$originFile);
                }
            }else{
                //原始图片
                $originFile = sprintf("%s/%s",$this->root,$key);
            }
            //缩略图
            $thumbnailFile = sprintf("%s/%s",$this->root,$thumbKey);
            $img = ImageManagerStatic::make($originFile);
            $height = $img->height();
            $width = $img->width();
            $minWidth = 100;
            $rWidth = 0;
            $rHeight = 0;
            if($width>$minWidth){
                if($w>0){
                    $rWidth = $w;
                    $rHeight = intval($rWidth/$width*$height);
                }
                if($h>0){
                    $rHeight = $h;
                    if($rWidth==0){
                        $rWidth = intval($rHeight/$height*$width);
                    }
                }

                $img->resize($rWidth,$rHeight);
                $img->save($thumbnailFile);
            }else{
                //如果待压缩的图片宽度小于200，则不进行压缩，直接复制
                copy($originFile,$thumbnailFile);
            }
        }
        return $this->getUrl($thumbKey);
    }

}