<?php
/**
 * Created by PhpStorm.
 * User: 77632
 * Date: 2017/12/8
 * Time: 9:50
 */

namespace mikkle\tp_master;

use think\Facade;
use think\image\Exception as ImageException;

class Image extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Image';
    }
    /**
     * 显示自定义尺寸缩略图
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param int $w
     * @param int $h
     */
    public function showImage($w=200,$h=200){

        if($w>600) $w=600;
        if($h>800) $h=800;
        $this->thumb($w,$h);
        Header("Content-type: image/png");
        ob_start(); //打开缓冲区
        imagepng($this->im, null, 8);
        ob_end_flush();//输出全部内容到浏览器

        die;
        return;

    }

    /**
     * 下载自定义尺寸图片
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param int $w
     * @param int $h
     */
    public function downloadImage($w=200,$h=200){
        $this->thumb($w,$h);
        header("Content-type: octet/stream");
        header("Content-disposition:attachment;filename=image.png;");
        ob_start(); //打开缓冲区
        imagepng($this->im, null, 9);
        ob_end_flush();//输出全部内容到浏览器
        die;
        return;
    }

    /**
     * 根据路径加载图片文件
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param \SplFileInfo|string $file
     * @return Image
     */
    public static function open($file)
    {
        if (is_string($file)) {
            $file = new \SplFileInfo($file);
        }
        if (!$file->isFile()) {
            throw new ImageException('image file not exist');
        }
        return new self($file);
    }



}