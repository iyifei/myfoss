<?php
/**
 * 上传接口
 * User: myf
 * Date: 2018/3/29
 * Time: 15:26
 */

use Illuminate\Container\Container;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Overtrue\Flysystem\Qiniu\Plugins\FileUrl;

define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';

//初始化容器
$container = Container::getInstance();
//配置信息
$adapterConfig = config('base.adapter');
//读取自定义的扩展
$container->bind(AdapterInterface::class,$adapterConfig['class']);
$container->when($adapterConfig['class'])->needs('$param')->give($adapterConfig['param']);

//实例化Filesystem
/**
 * @var Filesystem $fileSystem
 */
$fileSystem = $container->make(Filesystem::class);

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
//获取访问地址
$fileSystem->addPlugin(new FileUrl());
$url = $fileSystem->getUrl($key);

$data = [
    'key'=>$key,
    'url'=>$url
];
header('Content-Type:application/json; charset=utf-8');
exit(json_encode($data));

