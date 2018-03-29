<?php
/**
 * 本机存储器
 * User: myf
 * Date: 2018/3/29
 * Time: 15:56
 */

namespace Myf\Adapter;


use League\Flysystem\Adapter\Local;

class LocalAdapter extends Local
{
    private $domain;

    public function __construct($param) {
        $root = $param['root'];
        $this->domain = $param['domain'];
        $writeFlags = LOCK_EX;
        $linkHandling = self::DISALLOW_LINKS;
        $permissions = [];
        parent::__construct($root, $writeFlags, $linkHandling, $permissions);
    }

    public function getUrl($path){
        return sprintf("%s/oss/%s",$this->domain,$path);
    }

}