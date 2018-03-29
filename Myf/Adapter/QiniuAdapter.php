<?php

namespace Myf\Adapter;

/**
 * remark
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

}