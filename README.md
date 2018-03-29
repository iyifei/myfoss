# myfoss
云存储基于flysystem,视频支持依赖ffmpeg

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

http://myfoss.minyifei.cn/[key]?thumbnail=w-200/h-150

获取改key对应的宽度为200px和150高的缩略图

示例：

http://myfoss.minyifei.cn//xjkx02.mp4?thumbnail=w-200
</code>



获取文件基本信息-支持视频/图片/其他

http://myfoss.minyifei.cn/[key]?info

视频示例：

http://myfoss.minyifei.cn/xjkx02.mp4?info,返回结果：

``` json 
{
    "basename": "xjkx02.mp4",
    "filename": "xjkx02",
    "format": "mp4",
    "mimetype": "video/mp4",
    "key": "xjkx02.mp4",
    "size": 2511197,
    "duration": "00:00:15.02",
    "seconds": 15.02,
    "start": "0.000000",
    "width": 856,
    "height": 480,
    "vcodec": "mpeg4"
}  

```

图片示例：

http://myfoss.minyifei.cn/test/file-abc.jpg?info,返回结果：

``` json 
{
    "basename": "file-abc.jpg",
    "filename": "file-abc",
    "format": "jpg",
    "mimetype": "image/jpeg",
    "key": "test/file-abc.jpg",
    "size": 131548,
    "height": 592,
    "width": 1024
} 

```


其他文件示例：

http://myfoss.minyifei.cn/ioncube.txt?info,返回结果：

``` json 
{
    "basename": "ioncube.txt",
    "filename": "ioncube",
    "format": "txt",
    "mimetype": "text/plain",
    "key": "ioncube.txt",
    "size": 1266
}

```