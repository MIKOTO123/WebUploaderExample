<?php
/**
 * 用来存放一些公共方法
 */


/**
 *  上传文件助手类
 *
 */
class UploadFileHelper
{


    //上传分片进行一系列判断


    const FILE_VAL = 'Filedata';


    const CHUNK_FORMAT_DEFAULT = 1;

    const CHUNK_FORMAT_STR_COMMON = "chunk_{chunk}";


    const CHUNK_FORMAT_STR_NEEDMD5 = "chunk_{chunk}_{md5}";


    /**
     * 分片上传的储蓄基础路径
     * @return string 分片上传的储蓄基础路径
     */
    public static function getChunkUploadBasePath()
    {
        $filePath = 'D:\Users\ys-8564\workspace\WebUploaderExample\netdisk\upload_tmp\\';
        return $filePath;
    }


    /**
     * 获取合成文件的储蓄基础路径
     * @return string
     */
    public static function getComposePath()
    {
        $filePath = 'D:\Users\ys-8564\workspace\WebUploaderExample\netdisk\upload\\';
        return $filePath;
    }


    /**
     * 获取文件分片上传临时存放目录名
     * 用文件名和md5值组合成一个新的文件夹名字,这个文件夹用来存放分片
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     * @return string 新的文件夹名字 文件夹名字=md5(文件名)_文件的md5值 例如:c8c751b8f8f74cf6de7a3653a2009862_5dd743303b8e851bf87468aa77ec10fd
     */
    public static function getFileChunkUploadDirName($fileName, $fileMd5)
    {
        return md5($fileName) . "_" . $fileMd5;
    }


    /**
     * 获取文件分片上传临时存放目录路径
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     * @param string $chunkUploadBasePath 分片上传的储蓄基础路径
     * @return string 例如:\netdisk\upload_tmp\e043966c3fa13cf3f53c7a0c19e142c2_4635cc052bc2751549fac41701a9a735\
     */
    public static function getFileChunkUploadDirNamePath($fileName, $fileMd5, $chunkUploadBasePath = "")
    {
        if (!$chunkUploadBasePath) {
            $chunkUploadBasePath = self::getChunkUploadBasePath();
        }
        $dirName = self::getFileChunkUploadDirName($fileName, $fileMd5);
        $dirPath = $chunkUploadBasePath . $dirName . DIRECTORY_SEPARATOR;
        return $dirPath;
    }


    /**
     * 创建文件夹路径
     * @param $path
     * @return void
     */
    public static function createPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);//这个方法不行?
            //后续这使用命令行创建文件夹
        }
    }


    /**
     * 获取分片名
     * @param string $str 自定义格式 示例:chunk_{chunk}_{md5}
     * @param array $replace_arr 替换的数组 ["{chunk}"=>1,"{md5}"=>"r143966c3fa13cf3f53c7a0c19e142f4"]
     * @return array|mixed|string|string[]  分片名称,格式为:chunk_1或者chunk_1_md5值
     */
    public static function getFileChunkName($str = self::CHUNK_FORMAT_STR_COMMON, $replace_arr = [])
    {
        if ($replace_arr) {
            $str = str_replace(array_keys($replace_arr), array_values($replace_arr), $str);
        }
        return $str;
    }


//    /**
//     * 获取分片名
//     * @param string $chunkIndex 分片索引
//     * @param bool $checkChunkmd5 是否检查分片md5
//     * @param string $chunkMd5 上传到服务器之后的分片的md5值
//     * @return string 分片名称,格式为:chunk_1或者chunk_1_md5值,如果$checkChunkmd5为true,则格式为:chunk_1_md5值
//     */
//    public static function getFileChunkName($chunkIndex, $checkChunkmd5 = false, $chunkMd5 = "")
//    {
//        $chunkName = "chunk_" . $chunkIndex;
//        if ($checkChunkmd5) {
//            $chunkName .= "_" . $chunkMd5;//这样的组装格式,在断点续传的时候可以直接取得md5值,省去了重复计算的资源,也节省了时间
//        }
//        return $chunkName;
//    }

    /**
     * 获取分片名的路径
     * 用  md5(文件名)_文件md5值 作为存放分片的路径
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     * @param int $chunkIndex 分片索引
     * @param string $chunkUploadBasePath 文件分片上传临时存放目录路径
     * @param bool $checkChunkmd5 是否检查分片md5
     * @param string $chunk_tmp_path 上传到服务器之后的分片的临时位置
     * @return string 分片名的路径 例如:\netdisk\upload_tmp\e043966c3fa13cf3f53c7a0c19e142c2_4635cc052bc2751549fac41701a9a735\chunk_2
     */
    public static function getFileChunkNamePath($fileName, $fileMd5, $chunkIndex, $chunkUploadBasePath = "", $checkChunkmd5 = false, $chunk_tmp_path = "")
    {
        $chunkUploadPath = self::getFileChunkUploadDirNamePath($fileName, $fileMd5, $chunkUploadBasePath);
        if ($checkChunkmd5) {
            $chunkMd5 = md5_file($chunk_tmp_path);
            $chunkName = self::getFileChunkName(self::CHUNK_FORMAT_STR_NEEDMD5, ['{chunk}' => $chunkIndex, '{md5}' => $chunkMd5]);
        } else {
            $chunkName = self::getFileChunkName(self::CHUNK_FORMAT_STR_COMMON, ['{chunk}' => $chunkIndex]);
        }
        return self::getFileChunkNamePathByPathAndName($chunkUploadPath, $chunkName);
    }


    /**
     *
     * @param $chunkUploadPath 分片路径
     * @param $chunkName 分片名
     * @return string 分片名的路径 例如:\netdisk\upload_tmp\e043966c3fa13cf3f53c7a0c19e142c2_4635cc052bc2751549fac41701a9a735\chunk_2
     */
    public static function getFileChunkNamePathByPathAndName($chunkUploadPath, $chunkName)
    {
        return $chunkUploadPath . $chunkName;
    }


    /**
     * 获取合成文件名的储蓄路径
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     * @param string $composePath 合成文件的储蓄基础路径
     * @return string 合成文件的储蓄路径 示例:\netdisk\upload\e043966c3fa13cf3f53c7a0c19e142c2_4635cc052bc2751549fac41701a9a735
     */
    public static function getComposeFileNamePath($fileName, $fileMd5, $composePath = "")
    {
        if (!$composePath) {
            $composePath = self::getComposePath();
        }
        $dirName = self::getComposeFileName($fileName, $fileMd5);
        $dirPath = $composePath . $dirName;
        return $dirPath;

    }


    /**
     * 获取合成文件名
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     */
    public static function getComposeFileName($fileName, $fileMd5)
    {
        return md5($fileName) . "_" . $fileMd5;
    }


    //todo:校验文件是否完整

    /**
     * 校验分片是否完整,完整返回true,否则返回false,
     * 一般只校验文件大小即可,因为文件$_FILES['file']['error'] 等于 0 代表文件上传没有错误.
     * 如果$checkChunkmd5等于true,则还要校验文件的md5值,如果文件的md5值不一致,则返回false
     * @param string $filePath 文件路径
     * @param string $size 上传之前的文件大小,如果为0,则说明文件上传失败,返回false
     * @param string $file_val 上传文件的name,默认为Filedata,也可以是其他的名字,例如:Filedata1,Filedata2,Filedata3,Filedata4,Filedata
     * @param bool $checkChunkmd5 是否检查分片md5
     * @param string $fileMd5 上传之前的文件md5值
     * @return bool
     */
    public static function checkChunkIsComplete($filePath, $size, $file_val = self::FILE_VAL, $checkChunkmd5 = false, $fileMd5 = "")
    {
        if ($_FILES[$file_val]['error'] != 0) {
            return false;
        }

        if ($checkChunkmd5) {
            $chunkMd5 = self::getFileChunkMd5($filePath);
            if ($chunkMd5 != $fileMd5) {
                return false;
            }
        }
        if ($size == 0) {
            return false;
        }
        //获取文件大小,进行对比是否等于$size
//        $fileSize = $_FILES[$file_val]['size'];//这边size指的是实际上传到服务器的文件大小?
        $fileSize = filesize($filePath);
        if ($fileSize != $size) {
            return false;
        }


        return true;

    }


    /**
     * 获取文件分片的md5值
     * @param $filePath
     * @return false|string
     */
    private static function getFileChunkMd5($filePath)
    {
        $basename = basename($filePath);
        //检测文件格式是否符合特定规则,快速计算md5
        if (preg_match("/^chunk_([0-9]+)_([0-9a-z]+)$/", $basename, $matches)) {
            return $matches[2];
        }
        return md5_file($filePath);
    }


    /**
     * 判断文件是否全部上传完毕,完整返回true,否则返回false
     * @param string $fileName 文件名
     * @param string $fileMd5 文件的md5值
     * @param int $chunks 分片数量
     * @param string $chunkUploadBasePath 分片上传的储蓄基础路径
     * @param bool $checkChunkmd5 是否检查分片md5
     * @return bool
     */
    public static function checkAllChunkIsUploaded($fileName, $fileMd5, $chunks, $chunkUploadBasePath = "", $checkChunkmd5 = false)
    {
        $fileChunkUploadDirNamePath = self::getFileChunkUploadDirNamePath($fileName, $fileMd5, $chunkUploadBasePath);
        //判断文件夹是否存在
        if (!file_exists($fileChunkUploadDirNamePath)) {
            return false;
        }
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists(self::getFileChunkNamePath($fileName, $fileMd5, $index, $chunkUploadBasePath, $checkChunkmd5))) {
                $done = false;
                break;
            }
        }
        return $done;
    }


    /**
     * 合成文件
     * @param string $composeFileNamePath 合成文件名的路径
     * @param int $chunks 分片数量
     * @param string $chunkUploadPath 分片上传的储蓄路径
     * @param callable $getUsernameFunc 获取分片名的函数,例如:function($index){return "chunk_$index";}
     * @param mixed ...$args 传递给函数的额外参数
     */
    public static function composeFile($composeFileNamePath, $chunks, $chunkUploadPath, $getChunkNameFunc, ...$args)
    {
        //创建合成路径
        $composePath = dirname($composeFileNamePath);
        UploadFileHelper::createPath($composePath);


        //合成文件
        try {

            if (!$out = @fopen($composeFileNamePath, "wb")) {
                throw new Exception('无法写入文件');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    //获取分片名
                    $chunkName = call_user_func($getChunkNameFunc, $index, ...$args);//获取文件名
                    $chunkNamePath = self::getFileChunkNamePathByPathAndName($chunkUploadPath, $chunkName);

                    if (!$in = @fopen($chunkNamePath, "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink($chunkNamePath);
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        } catch (Exception $e) {
            throw new Exception('合成文件失败');
        }

    }


}




//判断文件是否传成功的标准
//判断$_FILES['file']['error']的值是否为0,加上size的对比
