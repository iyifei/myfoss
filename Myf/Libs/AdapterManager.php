<?php
namespace Myf\Libs;

use Illuminate\Container\Container;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;

/**
 * 适配器管理
 * User: myf
 * Date: 2018/3/29
 * Time: 17:22
 */
class AdapterManager
{

    /**
     * 获取Filesystem实例
     * @return Filesystem
     */
    public static function getFilesystem(){
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
        return $fileSystem;
    }

}