<?php
/**
 * 删除资源
 * User: myf
 * Date: 2018/3/30
 * Time: 15:37
 */
use Myf\GEnum\ErrorCode;
use Myf\GEnum\RedisKey;
use Myf\Libs\AdapterManager;
use Myf\Libs\RedisClient;

define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';
$key = request('key');
//授权token
$token = getHeader('token');
$redis = RedisClient::getInstance();
$tokenKey = sprintf("%s_%s",RedisKey::Token,$token);
try{
    if($redis->get($tokenKey)) {
        if (!empty($key) && $key != '/') {
            //存储key
            $key = ltrim($key, '/');

            $fileSystem = AdapterManager::getFilesystem();
            $fileSystem->delete($key);
            $res = [
                'status'=>ErrorCode::SUCCESS,
                'data'=>[
                    'key'=>$key
                ]
            ];
        } else {
            throw new Exception('key is not empty',ErrorCode::KEY_ERROR);
        }
    }else{
        throw new Exception('token error',ErrorCode::TOKEN_ERROR);
    }
}catch (Exception $e){
    $res = [
        'status'=>$e->getCode(),
        'error'=>$e->getMessage(),
    ];
}
exitJson($res);