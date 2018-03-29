<?php
/**
 * 入库
 * User: myf
 * Date: 2018/3/29
 * Time: 14:46
 */
define("APP_PATH",__DIR__);
require_once APP_PATH.'/bootstrap/core.php';

$mysql = config('base');
var_dump($mysql);