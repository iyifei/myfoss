<?php
/**
 * 错误码
 * User: myf
 * Date: 2018/3/30
 * Time: 10:46
 */

namespace Myf\GEnum;


class ErrorCode
{

    const SUCCESS = 0;

    //未知错误
    const ERROR = 1;

    //授权key错误
    const ACCESS_KEY_ERROR = 2;

    //授权错误
    const SIGN_ERROR = 3;

    //时间过期
    const TIME_EXPIRED = 4;

    //token错误
    const TOKEN_ERROR = 5;

}