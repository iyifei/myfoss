<?php
/**
 * 获取文件基本信息
 * User: myf
 * Date: 2018/3/30
 * Time: 04:49
 */

namespace Myf\Plugin;


use League\Flysystem\Plugin\AbstractPlugin;

class FileInfo extends AbstractPlugin
{

    public function getMethod()
    {
        return 'getInfo';
    }

    public function handle($path)
    {
        return $this->filesystem->getAdapter()->getInfo($path);
    }

}