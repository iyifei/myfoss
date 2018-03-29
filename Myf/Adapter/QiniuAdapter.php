<?php

namespace Myf\Adapter;
use Myf\GEnum\ExtensionType;
use Myf\Libs\AdapterManager;

/**
 * 七牛适配器
 * User: myf
 * Date: 2018/3/29
 * Time: 15:33
 */
class QiniuAdapter extends \Overtrue\Flysystem\Qiniu\QiniuAdapter
{

    public function __construct($param) {
        $accessKey = $param['accessKey'];
        $secretKey = $param['secretKey'];
        $bucket = $param['bucket'];
        $domain = $param['domain'];
        parent::__construct($accessKey, $secretKey, $bucket, $domain);
    }

    /**
     * 获取资源基本信息
     * @param $key
     * @return array
     */
    public function getInfo($key){
        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];
        $url = $this->getUrl($key);
        $fs = AdapterManager::getFilesystem();
        $info = [
            'basename'=>$pathInfo['basename'],
            'filename'=>$pathInfo['filename'],
            'format'=>strtolower($ext),
            'mimetype'=>$fs->getMimetype($key),
            'key'=>$key,
        ];
        //转换为文件格式
        $type = ExtensionType::switchType($ext);
        switch ($type){
            //图片
            case ExtensionType::IMAGE:
                $imgInfoUrl = sprintf("%s?imageInfo",$url);
                $data = file_get_contents($imgInfoUrl);
                if(!empty($data)){
                    $json = json_decode($data,true);
                    $info['size']=intval($json['size']);
                    $info['height']=intval($json['height']);
                    $info['width']=intval($json['width']);
                }
                break;
            //视频
            case ExtensionType::VIDEO;
                $imgInfoUrl = sprintf("%s?avinfo",$url);
                $data = file_get_contents($imgInfoUrl);
                if(!empty($data)){
                    //读取视频基本信息
                    $videoInfo = json_decode($data,true);
                    $streams = array_column($videoInfo['streams'],null,'codec_type');
                    $video = $streams['video'];
                    $format = $videoInfo['format'];
                    $info['seconds']=round($format['duration'],2);
                    $info['duration']=secToTime($info['seconds']);
                    $info['start']=$video['start_time'];
                    $info['width']=intval($video['width']);
                    $info['height']=intval($video['height']);
                    $info['vcodec']=$video['codec_name'];
                    $info['size']=intval($format['size']);
                }
                break;
            default:
                $info['size'] = $fs->getSize($key);
                break;
        }
        return $info;
    }

    /**
     * 获取七牛缩略图
     * @param $key
     * @param $param
     * @return string
     */
    public function getThumbnail($key,$param){
        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];

        $originUrl = $this->getUrl($key);
        $sc = [];
        if($param['w']>0){
            $sc[]=sprintf('w/%d',$param['w']);
        }
        if($param['h']>0){
            $sc[]=sprintf('h/%d',$param['h']);
        }
        if(ExtensionType::isSupportVideo($ext)){
            $url = sprintf("%s?vframe/jpg/offset/7/%s", $originUrl, join('/',$sc));
        }else{
            $url = sprintf("%s?imageView2/1/%s", $originUrl, join('/',$sc));
        }
        return $url;
    }

}