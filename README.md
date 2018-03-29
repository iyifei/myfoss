# myfoss
云存储基于flysystem

可以灵活配置aliyun，七牛、本机存储

需要在根目录下创建一个configs文件夹，在里面创建一个base.config.php文件，内容如下：

``` php 
  
  <?php
/**
 * 存储适配器
 * User: myf
 * Date: 2018/2/27
 * Time: 14:22
 */

return [
    //七牛
    
    'adapter'=>[
        'class'=>\Myf\Adapter\QiniuAdapter::class,
        'param' => [
            'accessKey'=>'七牛accessKey',
            'secretKey'=>'七牛secretKey',
            'bucket'=>'七牛bucket',
            'domain'=>'七牛域名',
        ],
    ],
    //本机存储
    'adapter'=>[
        'class'=>\Myf\Adapter\LocalAdapter::class,
        'param' => [
            'root'=>APP_PATH.'/oss',
            'domain'=>'http://myfoss.minyifei.cn'
        ],
    ],
   
     //阿里云
    'adapter'=>[
        'class'=>\Myf\Adapter\AliyunAdapter::class,
        'param' => [
            'access_id'     => '阿里云access_id',
            'access_secret' => '阿里云的access_secret',
            'bucket'        => '阿里云的bucket',
            'endpoint'       => 'oss-cn-hangzhou.aliyuncs.com',
        ],
    ],
];
  
 ```
 
 用法：
 
 ``` txt 
 上传文件：
 
 地址：
 http://myfoss.minyifei.cn/upload.php
 
 
 
 HEADERS:

 Content-Type: multipart/form-data
 filename: test/file-abc.jpg
 
 Body
 
 file:File 
  ```

<code>

获取缩略图-支持视频/图片

http://myfoss.minyifei.cn/[key]??thumbnail=w-200/h-150

获取改key对应的宽度为200px和150高的缩略图

示例：

http://myfoss.minyifei.cn//xjkx02.mp4?thumbnail=w-200
</code>