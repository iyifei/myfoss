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