<?php
/**
 * remark
 * User: myf
 * Date: 2018/3/29
 * Time: 16:01
 */

namespace Myf\Adapter;


use Xxtime\Flysystem\Aliyun\OssAdapter;

class AliyunAdapter extends OssAdapter
{

    private $bucket;
    private $endpoint;

    public function __construct(array $param = []) {
        $this->bucket = $param['bucket'];
        $this->endpoint = $param['endpoint'];
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

    private function normalizeHost(){
        $uri = 'http://' . $this->bucket . '.' . $this->endpoint . '/' ;
        return $uri;
    }

}