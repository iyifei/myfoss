<?php

namespace Myf\Adapter;
use Myf\GEnum\ExtensionType;

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