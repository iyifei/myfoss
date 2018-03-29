<?php
namespace Myf\Plugin;

use League\Flysystem\Plugin\AbstractPlugin;

/**
 * 文件缩略图
 * User: myf
 * Date: 2018/3/29
 * Time: 20:06
 */
class FileThumbnail extends AbstractPlugin
{

    public function getMethod()
    {
        return 'getThumbnail';
    }

    public function handle($path,$param)
    {
        return $this->filesystem->getAdapter()->getThumbnail($path,$param);
    }
}