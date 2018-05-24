<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/7/27
 * Time: 11:05
 */

namespace mikkle\tp_tools;


use mikkle\tp_master\Cache;
use mikkle\tp_master\Config;
use mikkle\tp_master\Db;
use mikkle\tp_master\File;
use mikkle\tp_master\Request;
use think\Exception;
use think\Log;


class Upload
{
    static public function uploadBase64($base64,$is_record=true,$route=true){
        $aData = $base64 ?: 'no pic';
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $aData, $matches)) {
            $base64 = substr(strstr($aData, ','), 1);
            empty($aExt) && $aExt = $matches[2];
            if (!in_array($aExt, array('jpg', 'gif', 'png', 'jpeg'))){
                return ShowCode::CodeWithoutData(1003, '非法操作,上传照片格式不符。');
            }
        } else {
            $base64 = $aData;
        }

        $im = base64_decode($base64);
        if (empty($im) || strpos($im, '<?php') !== false) return ShowCode::codeWithoutData(1003, '非法操作,上传照片格式不符。');
        $file_hash_md5 = md5($im);
        $file_hash_sha1 = sha1($im);
        $return=[
            "code"=>1001,
            "data"=>[
                "md5"=>$file_hash_md5,
            ],
            "msg"=>"图片上传成功",
        ];
        //判断数据库中是否存在
        if ($is_record){
            $images_table = Config::get("upload.upload_images_table");
            $map = [
                "md5"=>$file_hash_md5,
                "sha1"=>$file_hash_sha1,
            ];
            $search_image=Db::table($images_table)->where($map)->find();
            if ($search_image){
                $return['data']["path"] =  $search_image["path"];
                $return["msg"] =  "获取已存在图像成功";
                $return['data']['id'] = $search_image["id"];
                if($route){
                    $return['data']['path'] = self::getRouteUrl($file_hash_md5,"images");
                }else{
                    $return['data']['path'] = $search_image["path"];
                }
                $return['data']['type'] = "images" ;
                return $return;
            }
        }
            $data['name'] = "image_".date("Ymdhis", time())."_".Rand::createRandNum(6).".png";
            $data['path'] = self::getSavePath("images",false).DS.$data['name'] ;
            $data['path'] = str_replace('\\', '/', $data['path']);
            $data['md5'] = $file_hash_md5;
            $data['sha1'] = $file_hash_sha1;
            $data['size'] = strlen($im);
            $data['type'] = 'local';
            $data['create_time'] = time();
            $data['width'] = 0;
            $data['height'] = 0;
            $rs = file_put_contents(self::getSavePath("images").DS.$data['name'] , $im);
            if(!$rs){
                return ShowCode::codeWithoutData(1009, '保存文件失败');
            }

            if($is_record){
                $images_table = Config::get("upload.upload_images_table");
                if ( $id = Db::table($images_table)->insertGetId($data) ) {
                    $return['data']['id'] = $id;
                    if($route){
                        $return['data']['path'] = Request::instance()->host().self::getRouteUrl($file_hash_md5,"images");
                    }else{
                        $return['data']['path'] = Request::instance()->host().$data['path'];
                    }
                    $return['data']['type'] = "images" ;
                } else {
                    $return['code'] = 1041;
                    $return['msg'] = '记录到数据库失败！';
                }
            }else{
                if($route){
                    $return['data']['path'] = Request::instance()->host().self::getRouteUrl($file_hash_md5,"images");
                }else{
                    $return['data']['path'] = Request::instance()->host().$data['path'];
                }
                $return['data']['type'] = "images" ;
            }


        return $return;

    }

    /**
     * @title uploadPicture
     * @description
     * User: Mikkle
     * QQ:776329498
     * @param $file_name
     * @param string $save_path
     * @param bool $is_record
     * @param array $rule
     * @param bool $route
     * @return array
     */
    static public function uploadPicture( $file_name,$save_path="",$is_record=true ,$rule=[],$route=true) {

        try {
            $file = Request::instance()->file($file_name);
            if (!$file) {
                ShowCode::jsonCodeWithoutData(1010);
            }
            $file_hash_md5 = $file->hash("md5");
            $file_hash_sha1 = $file->hash("sha1");
            $return = [
                "code" => 1001,
                "data" => [
                    "md5" => $file_hash_md5,
                ],
                "msg" => "图片上传成功",
            ];

            //判断数据库中是否存在
            if ($is_record) {
                $images_table = Config::get("upload.upload_images_table");
                $map = [
                    "md5" => $file_hash_md5,
                    "sha1" => $file_hash_sha1,
                ];
                $search_image = Db::table($images_table)->where($map)->find();
                if ($search_image) {
                    $return['data']["path"] = $search_image["path"];
                    $return["msg"] = "获取已存在图像成功";

                    $return['data']['id'] = $search_image["id"];
                    if ($route) {
                        $return['data']['path'] = self::getRouteUrl($file_hash_md5, "images");
                    } else {
                        $return['data']['path'] =  $search_image["path"];
                    }
                    $return['data']['type'] = "images";
                    return $return;
                }
            }

            $save_path = self::getSavePath("images", true, false);
            $validate_rule = $rule ? $rule : Config::get("upload.upload_images_validate");
            $info = $file->validate($validate_rule)->move($save_path);


            if ($info) {
                $oinfo = $info->getInfo();
                $data['name'] = $oinfo['name'];
                $data['path'] = self::getSavePath("images", false, false) . DS . $info->getSaveName();
                $data['path'] = str_replace('\\', '/', $data['path']);
                $data['md5'] = $file_hash_md5;
                $data['sha1'] = $file_hash_sha1;
                $data['size'] = $oinfo['size'];
                $data['type'] = 'local';
                $data['create_time'] = time();
                $data['width'] = 0;
                $data['height'] = 0;
                $data['status'] = 1;

                if ($is_record) {
                    $images_table = Config::get("upload.upload_images_table");
                    if ($id = Db::table($images_table)->insertGetId($data)) {
                        $return['data']['id'] = $id;
                        if ($route) {
                            $return['data']['path'] =  self::getRouteUrl($file_hash_md5, "images");
                        } else {
                            $return['data']['path'] =  $data['path'];
                        }
                        $return['data']['type'] = "images";
                    } else {
                        $return['code'] = 1041;
                        $return['msg'] = '记录到数据库失败！';
                    }
                } else {
                    if ($route) {
                        $return['data']['path'] = self::getRouteUrl($file_hash_md5, "images");
                    } else {
                        $return['data']['path'] = $data['path'];
                    }
                    $return['data']['type'] = "images";
                }
            } else {
                $return['code'] = 1040;
                $return['msg'] = $file->getError();
            }
            return $return;
        } catch (Exception $e) {
            Log::error($e);
            return ShowCode::code(1008, $e->getMessage());
        }
    }

    /**
     * @title uploadFile
     * @description
     * User: Mikkle
     * QQ:776329498
     * @param $file_name
     * @param string $save_path
     * @param bool $is_record
     * @param array $rule
     * @param bool $route
     * @return array
     */
    static public function uploadFile($file_name,$save_path="",$is_record=true,$rule=[],$route=true ) {
        $return=[
            "code"=>1001,
            "data"=>"",
            "msg"=>"文件上传成功",
        ];

        $file = Request::instance()-> file($file_name);
        if (!$file){
            ShowCode::jsonCodeWithoutData(1010);
        }
        $file_hash_md5 = $file->hash("md5");
        $file_hash_sha1 = $file->hash("sha1");
        //判断数据库中是否存在
        if ($is_record){
            $files_table = Config::get("upload.upload_files_table");
            $map = [
                "md5"=>$file_hash_md5,
                "sha1"=>$file_hash_sha1,
            ];
            $search_file=Db::table($files_table)->where($map)->find();
            if ($search_file){
                $return['data']["path"] =  Request::instance()->host().$search_file["path"];
                $return['data']["md5"] =  $file_hash_md5;
                $return["msg"] =  "获取已存在文件成功";

                $return['data']['id'] = $search_file["id"];

                if($route){
                    $return['data']['path'] = Request::instance()->host().self::getRouteUrl($file_hash_md5,"files");
                    $return['data']["md5"] =  $file_hash_md5;
                }else{
                    $return['data']['path'] = Request::instance()->host().$search_file["path"];
                    $return['data']["md5"] =  $file_hash_md5;
                }

                $return['data']['type'] = "files" ;
                return $return;
            }
        }

        $save_path = self::getSavePath("files");
        $validate_rule = $validate_rule = $rule ? $rule : Config::get("upload.upload_files_validate");
        $info = $file->validate($validate_rule)->move($save_path);

        if ( $info ) {
            $oinfo = $info->getInfo();

            $data['name'] = $oinfo['name'];

            $data['path'] = self::getSavePath("files", false) . DS . $info->getSaveName();
            $data['path'] = str_replace('\\', '/', $data['path']);
            $data['md5'] = $file_hash_md5;
            $data['sha1'] = $file_hash_sha1;
            $data['size'] = $oinfo['size'];
            $data['type'] = 'local';
            $data['create_time'] = time();


            if($is_record){
                $files_table = Config::get("upload.upload_files_table");
                if ( $id = Db::table($files_table)->insertGetId($data) ) {
                    $return['data']['id'] = $id;
                    if($route){
                        $return['data']["md5"] =  $file_hash_md5;
                        $return['data']['path'] = Request::instance()->host().self::getRouteUrl($file_hash_md5,"files");
                    }else{
                        $return['data']["md5"] =  $file_hash_md5;
                        $return['data']['path'] = Request::instance()->host().$data['path'];
                    }

                    $return['data']['type'] = "images" ;
                } else {
                    $return['code'] = 1041;
                    $return['msg'] = '记录到数据库失败！';
                }
            }else{
                if($route){
                    $return['data']['path'] = Request::instance()->host().self::getRouteUrl($file_hash_md5,"files");
                }else{
                    $return['data']['path'] = Request::instance()->host().$data['path'];
                }
                $return['data']['type'] = "files" ;
            }

        } else {
            $return['code'] = 1040;
            $return['msg'] = $file->getError();
        }
        return $return;
    }

    /**
     * 根据图片Md5 反查path路径
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $md5
     * @param bool|true $full
     * @return bool|mixed|string
     */
    static public function getPicturePathByMd5($md5,$full=true ){
        $info = Cache::get("Picture_{$md5}_info");
        if(empty($info)){
            $info=Db::table(Config::get("upload.upload_images_table"))->where(["md5"=>$md5,"status"=>["<>",-1]])->find();
            if($info){
                Cache::set("Picture_{$md5}_info",$info);
            }
        }
        if(!empty($info) && $full == true ){
            return $_SERVER['CONTEXT_DOCUMENT_ROOT'].$info["path"];
        }elseif(!empty($info) && $full != true ){
            return $info["path"];
        }else{
            return false;
        }
    }

    static public function getFilePathByMd5($md5,$full=true ){
        $info = Cache::get("File_{$md5}_info");
        if(empty($info)){
            $info=Db::table(Config::get("upload.upload_files_table"))->where(["md5"=>$md5,"status"=>["<>",-1]])->find();
            if($info){
                Cache::set("File_{$md5}_info",$info);
            }
        }
        if(!empty($info) && $full == true ){
            return $_SERVER['CONTEXT_DOCUMENT_ROOT'].$info["path"];
        }elseif(!empty($info) && $full != true ){
            return $info["path"];
        }else{
            return false;
        }
    }


    static public function getPictureInfoByMd5($md5,$full=true ){
        $info = Cache::get("Picture_{$md5}_info");
        if(empty($info)){
            $info=Db::table(Config::get("upload.upload_images_table"))->where(["md5"=>$md5,"status"=>["<>",-1]])->find();
            if($info){
                Cache::set("Picture_{$md5}_info",$info);
            }
        }
        return $info;
    }

    static public function getFileInfoByMd5($md5,$full=true ){
        $info = Cache::get("File_{$md5}_info");
        if(empty($info)){
            $info=Db::table(Config::get("upload.upload_files_table"))->where(["md5"=>$md5,"status"=>["<>",-1]])->find();
            if($info){
                Cache::set("File_{$md5}_info",$info);
            }
        }
        return $info;
    }


    /**
     * 获取保存路径
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $type
     * @param bool $absolute_path
     * @param bool $date 是否按日期存放
     * @return string
     */
    static public function getSavePath($type,$absolute_path=true,$date=true){
        $root_path =$absolute_path? $_SERVER['CONTEXT_DOCUMENT_ROOT']:"";
        switch($type){
            case "images":
                $config_save_path = Config::get("upload.upload_images_path");
                if (!isset($save_path) && $config_save_path) {
                    $save_path = "{$root_path}{$config_save_path}";
                } elseif (!isset($save_path) && !$config_save_path) {
                    $save_path = "{$root_path}/upload/images";
                } else {
                    $save_path = "{$root_path}{$save_path}";
                }
            break;

            case "files":
                $config_save_path = Config::get("upload.upload_files_path");
                if (!isset($save_path) && $config_save_path) {
                    $save_path = "{$root_path}{$config_save_path}";
                } elseif (!isset($save_path) && !$config_save_path) {
                    $save_path = "{$root_path}/upload/files";
                } else {
                    $save_path = "{$root_path}{$save_path}";
                }
                break;
            default :
                $save_path = "{$root_path}/upload/others";
        }
        if ($date){
            $save_path=$save_path.date("/Y/m/d",time());
        }
        if($absolute_path&&!is_dir($save_path)){
            @mkdir($save_path,0755,true);
        }
        return $save_path;
    }

    static public function getRouteUrl($md5,$type,$width=480,$height=600){
        switch($type){
            case "images":
                    $save_path = "/upload/show_images/$md5/{$width}_{$height}";
                break;
            case "files":
                $save_path = "/upload/down_files/$md5";
                break;
            default :
                $save_path = "/upload/down_others/$md5";
        }
        return $save_path;
    }

    static public function getDownLoadUrl($md5,$type,$width=0,$height=0){
        switch($type){
            case "images":
                if($width>0&&$height>0){
                    $save_path = "/upload/down_images/$md5/{$width}_{$height}";
                }else{
                    $save_path = "/upload/down_images/$md5";
                }
                break;
            case "files":
                $save_path = "/upload/down_files/$md5";
                break;
            default :
                $save_path = "/upload/down_others/$md5";
        }

        return $save_path;
    }


    static public function downloadFileByMd5($md5){
        $info = self::getFileInfoByMd5($md5);
        $path=$_SERVER['CONTEXT_DOCUMENT_ROOT'].$info["path"];
        header("Content-type: octet/stream");
        header("Content-disposition:attachment;filename='".$info["name"]."';");
        header("Content-Length:".filesize($path));
        readfile($path);
        exit;
    }


    static public function downloadPictureByMd5($md5){
        $info = self::getPictureInfoByMd5($md5);
        $path=$_SERVER['CONTEXT_DOCUMENT_ROOT'].$info["path"];
        header("Content-type: octet/stream");
        header("Content-disposition:attachment;filename='".$info["name"]."';");
        header("Content-Length:".filesize($path));
        readfile($path);
        exit;
    }


}