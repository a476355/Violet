<?php

namespace subsystem;

class FileManage{

    public static $TYPE_FILE = "file";

    public static $TYPE_DIR = "dir";

    /**
     * 清理目录内文件
     * @param $path                 目录路径
     * @param bool $delDir          fals 保留目录 true 删除目录
     * @return bool                 清理结果
     */
    public static function deleteDirOrFile(string $path, $delDir = false) {
        if(file_exists($path)){
            $handle = opendir($path);
            if ($handle){
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..")
                        is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
                }
                closedir($handle);
                if ($delDir)
                    return rmdir($path);
            }else {
                if (file_exists($path)) {
                    return unlink($path);
                } else {
                    return false;
                }
            }
        }
        else{
            return false;
        }
    }
    
    /**
     * @param string $dir 查询的文件目录
     * @param string|null $type 文件类型 file dir
     * @return array 匹配结果的文件路径
     */
    public static function getfileList(string $dir,string $type = null){
        $List = array();

        if (is_dir($dir)){
            if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    if($file == '.' || $file == '..'){
                        //系统目录通常情况下直接忽略;
                    }else{

                        $pathName = $dir.'\\'.$file;

                        if($type == null){
                            array_push($List,$dir.'\\'.$file);
                        }
                        else if($type == 'file' && is_file($pathName)){
                            array_push($List,$pathName);
                        }
                        else if($type == 'dir' && is_dir($pathName) == 1){
                            array_push($List,$pathName);
                        }
                    }
                }
                //关闭目录
                closedir($dh);
            }
        }

        return $List;
    }

    /**
     * 递归创建多级目录
     * @param $path
     */
    public static function mkdir(string $path){
        mkdir($path,null,true);
    }

    /**
     * 以覆盖文件的方式，写入数据
     * @param string $filePath
     * @param string $data
     * @return bool
     */
    public static function coreOutFile(string $filePath,string $data){
        $path = dirname($filePath);
        if(!file_exists($path)){
            self::mkdir($path);
        }
        $length = file_put_contents($filePath,$data);
        if($length > 0){
            return true;
        }else{
            return false;
        }
    }

}