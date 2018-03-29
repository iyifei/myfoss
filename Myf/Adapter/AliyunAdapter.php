<?php
/**
 * remark
 * User: myf
 * Date: 2018/3/29
 * Time: 16:01
 */

namespace Myf\Adapter;


use Myf\GEnum\ExtensionType;
use Myf\Libs\AdapterManager;
use Myf\Libs\FfmpegUtil;
use Xxtime\Flysystem\Aliyun\OssAdapter;

class AliyunAdapter extends OssAdapter
{

    private $bucket;
    private $endpoint;
    private $config;

    public function __construct(array $param = []) {
        $this->bucket = $param['bucket'];
        $this->endpoint = $param['endpoint'];
        $this->config = $param;
        parent::__construct($param);
    }

    /**
     * Get resource url.
     * @param string $path
     * @return string
     */
    public function getUrl($path)
    {
        return $this->normalizeHost().ltrim($path, '/');
    }


    /**
     * 获取阿里云的文件基本属性信息
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
                $imgInfoUrl = sprintf("%s?x-oss-process=image/info",$url);
                $data = file_get_contents($imgInfoUrl);
                if(!empty($data)){
                    $json = json_decode($data,true);
                    $info['size']=intval($json['FileSize']['value']);
                    $info['format']=strtolower($json['Format']['value']);
                    $info['height']=intval($json['ImageHeight']['value']);
                    $info['width']=intval($json['ImageWidth']['value']);
                }
                break;
            //视频
            case ExtensionType::VIDEO;
                $tmpPath = sys_get_temp_dir();
                $tmpFile = sprintf("%s/%s.jpg",rtrim($tmpPath,'/'),uniqid(rand(1,1000),true));
                $videoInfo = FfmpegUtil::createVideoThumbAndGetInfo($url,$tmpFile);
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
                    $info['size']=$fs->getSize($key);
                }
                break;
            default:
                $info['size'] = $fs->getSize($key);
                break;
        }
        return $info;
    }

    /**
     * 获取阿里云的缩略图-支持图片缩略图
     * @param $key
     * @param $param
     * @return string
     */
    public function getThumbnail($key,$param){
        $originUrl = $this->getUrl($key);

        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];
        if(ExtensionType::isSupportVideo($ext)){
            //视频截图key
            $originKey = sprintf("%s.jpg",$key);
            if(!$this->has($originKey)){
                $tmpPath = sys_get_temp_dir();
                $tmpFile = sprintf("%s/%s.jpg",rtrim($tmpPath,'/'),uniqid(rand(1,1000),true));
                //通过ffmpeg生成缩略图
                FfmpegUtil::createVideoThumbAndGetInfo($originUrl,$tmpFile);
                if(is_file($tmpFile)){
                    $stream = fopen($tmpFile,'r+');
                    AdapterManager::getFilesystem()->putStream($originKey,$stream);
                    if(is_resource($stream)){
                        fclose($stream);
                    }
                }
            }
            $originUrl = $this->getUrl($originKey);
        }

        $sc = [];
        if($param['w']>0){
            $sc[]=sprintf('w_%d',$param['w']);
        }
        if($param['h']>0){
            $sc[]=sprintf('h_%d',$param['h']);
        }
        $url = sprintf("%s?x-oss-process=image/resize,%s", $originUrl, join(',',$sc));
        return $url;
    }

    private function normalizeHost(){
        $uri = 'https://' . $this->bucket . '.' . $this->endpoint . '/' ;
        return $uri;
    }



}