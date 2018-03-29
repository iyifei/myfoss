<?php
namespace Myf\GEnum;

/**
 * remark
 * User: myf
 * Date: 2018/3/29
 * Time: 21:11
 */
class ExtensionType
{

    //图片
    const IMAGE = 1;

    //视频
    const VIDEO = 2;

    /**
     * 转换后缀为格式枚举
     * @param $ext
     * @return int
     */
    public static function switchType($ext){
        $type = 0;
        if(self::isSupportVideo($ext)){
            $type = self::VIDEO;
        }elseif(self::isSupportImage($ext)){
            $type = self::IMAGE;
        }
        return $type;
    }

    /**
     * 获取图片的后缀数组
     * @return array
     */
    public static function getImages(){
        $ext = ['jpg','jpeg','png','gif'];
        return $ext;
    }

    /**
     * 获取视频支持的后缀数组
     * flv,mpg,mpeg,avi,wmv,mov,asf,rm,rmvb,mkv,m4v,mp4
     * @return array
     */
    public static function getVideos(){
        $ext = ['asf','flv','mpg','mpeg','avi','mp4','mov','3gp','wmv','rm','rmvb'];
        return $ext;
    }

    /**
     * 判断是否支持缩略图
     * @param $ext
     * @return bool
     */
    public static function isSupportThumbnail($ext){
        $exts = array_merge(self::getImages(),self::getVideos());
        return in_array(strtolower($ext),$exts);
    }

    /**
     * 是否是支持的视频格式
     * @param $ext
     * @return bool
     */
    public static function isSupportVideo($ext){
        return in_array(strtolower($ext),self::getVideos());
    }

    /**
     * 是否为支持的图片格式
     * @param $ext
     * @return bool
     */
    public static function isSupportImage($ext){
        return in_array(strtolower($ext),self::getImages());
    }

}