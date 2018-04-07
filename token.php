<?php
/**
 * 获取操作token
 * User: myf
 * Date: 2018/3/30
 * Time: 10:36
 */

use Myf\GEnum\ErrorCode;
use Myf\GEnum\RedisKey;
use Myf\Libs\RedisClient;

define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';
//授权key
$appId = post('appId');
//过期时间,单位s-默认1小时
$expire = getInteger('expire',3600);
//授权签名
$sign = post('sign');
//当前时间戳,一天内有效
$time = post('time');
//可用的tokens
$access = config('access');
try{
    if(isset($access[$appId])){
        //1天内有效
        $now = time();
        if($now-$time>24*3600){
            throw new \Exception('time expired',ErrorCode::TIME_EXPIRED);
        }

        //校验签名
        $appSecret = $access[$appId]['appSecret'];
        $info = sprintf("%s_%s_%s",$appId,$time,$expire);
        $encodeSign = signEncode($info,$appSecret);
        if($encodeSign!=$sign){
            throw new \Exception('sign error',ErrorCode::SIGN_ERROR);
        }
        $token = md5(uniqid(rand(1,10000),true));
        $expireTime = date("Y-m-d H:i:s",strtotime("+".$expire."seconds"));
        $data = [
            'token'=>$token,
            'expire'=>$expire,
            'expireTime'=>$expireTime,
        ];
        //存入redis缓存
        $redis = RedisClient::getInstance();
        $key = sprintf("%s_%s",RedisKey::Token,$token);
        $redis->set($key,$token,$expire);
        $res = [
            'status'=>ErrorCode::SUCCESS,
            'data'=>$data,
        ];
    }else{
        throw new \Exception('access error',ErrorCode::ACCESS_KEY_ERROR);
    }
}catch (\Exception $e){
    $res = [
        'status'=>$e->getCode(),
        'error'=>$e->getMessage(),
    ];
}
exitJson($res);
