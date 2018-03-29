<?php
/**
 * 上传接口
 * User: myf
 * Date: 2018/3/29
 * Time: 15:26
 */

use Myf\Libs\AdapterManager;
use Overtrue\Flysystem\Qiniu\Plugins\FileUrl;

define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';

$fileSystem = AdapterManager::getFilesystem();
//上传的文件句柄名称
$uploadName = 'file';

//oss存储的key
$key = getHeader('filename');
//如果没有指定key，则用文件名作为key
if(empty($key)){
    $key = $_FILES[$uploadName]['name'];
}
$stream = fopen($_FILES[$uploadName]['tmp_name'],'r+');
$fileSystem->putStream($key,$stream);
if(is_resource($stream)){
    fclose($stream);
}

//本机地址
$domain = config('base.domain');
$url = sprintf("%s/%s",$domain,$key);

//原始地址,可用于下载
$fileSystem->addPlugin(new FileUrl());
$origin = $fileSystem->getUrl($key);

$data = [
    'key'=>$key,
    'url'=>$url,
    'origin'=>$origin,
];
header('Content-Type:application/json; charset=utf-8');
exit(json_encode($data));

