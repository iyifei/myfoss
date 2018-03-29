<?php
/**
 * 获取缩略图
 * thumbnail w-100/h-80
 * User: myf
 * Date: 2018/3/29
 * Time: 14:46
 */
use Myf\GEnum\ExtensionType;
use Myf\Libs\AdapterManager;
use Myf\Plugin\FileThumbnail;
use Overtrue\Flysystem\Qiniu\Plugins\FileUrl;

define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';
$key = request('key');
if(!empty($key) && $key!='/'){
    //存储key
    $key = ltrim($key, '/');

    //缩略图
    $thumbnail = get('thumbnail');
    //缩略图参数
    $thumbParam = [];
    if(!empty($thumbnail)){
        $thumbParams = explode('/',$thumbnail);
        foreach ($thumbParams as $tt){
            list($k,$v) = explode('-',$tt);
            if(intval($v)>0 && in_array($k,['w','h'])){
                $thumbParam[$k]=intval($v);
            }
        }
    }

    //获取Filesystem实例
    $fileSystem = AdapterManager::getFilesystem();

    $has = $fileSystem->has($key);
    if($has){
        //资源后缀
        $pathInfo = pathinfo($key);
        $ext = $pathInfo['extension'];
        //读取缩略图,需要是图片、视频格式
        if(!empty($thumbParam) && ExtensionType::isSupportThumbnail($ext)){
            $fileSystem->addPlugin(new FileThumbnail());
            $url = $fileSystem->getThumbnail($key,$thumbParam);
        }else{
            //读取原图
            $fileSystem->addPlugin(new FileUrl());
            $url = $fileSystem->getUrl($key);
        }
        header("Location:".$url);
        exit;
    }
}
Header("HTTP/1.1 404 Not Found");
exit;