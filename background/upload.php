<?php
/**
 * 这个方法用来处理上传部分逻辑
 */

require_once 'common.php';

//忽略警告
error_reporting(E_ALL ^ E_NOTICE);

//print_r($_FILES);

move_uploaded_file($_FILES ['Filedata']["tmp_name"], 'D:\Users\ys-8564\workspace\WebUploaderExample\netdisk\\' . $_FILES ['Filedata']["name"]);


//print_r($_POST);
//print_r($_REQUEST);
//echo $_FILES ['Filedata']["tmp_name"];

echo json_encode(['status' => 1, "msg" => "你错了没"]);//分片错误就抛出异常
die;


echo UploadFileHelper::getFileChunkNamePath($_FILES ['Filedata']["name"], $_POST['cusmd5val'], $_POST['chunk']);
echo "\n";
echo UploadFileHelper::getComposeFileNamePath($_FILES ['Filedata']["name"], $_POST['cusmd5val']);


die;

//上传逻辑
//1.基础条件过滤
checkEroor();
//2.文件分片上传
try {
    $uploadComplete = uploadChunk();
} catch (Exception $e) {
    echo json_encode(['status' => 1, "msg" => $e->getMessage()]);//分片错误就抛出异常
}

if ($uploadComplete) {
    //3.合并文件
    composeFile();
}
echo json_encode(['status' => 0, "msg" => "单分片完成"]);


/**
 * 进行一系列条件过滤,筛选,错误抛出异常
 * @return void
 */
function checkEroor()
{

    //判断$_FILES 是否存在错误
    if ($_FILES ['Filedata']["error"] > 0) {
        throw new Exception("文件上传错误:" . $_FILES ['Filedata']["error"]);
    }

    //比如空间不足,文件太大,文件格式不正确等等


}

/**
 * 操作分片,并判断上传完成,返回true
 * @return bool
 */
function uploadChunk()
{
    //赋值
    $fileName = $_FILES ['Filedata']["name"];
    $fileMd5 = $_POST['cusmd5val'];
    $chunk = $_POST['chunk'];//当前分片编号
    $chunks = $_POST['chunks'];//分片总数
    $size = $_POST['size'];//文件大小
    $chunkactualsize = $_POST['chunkactualsize'];//分片上传前实际大小
    //创建路径
    $fileChunkUploadDirNamePath = UploadFileHelper::getFileChunkUploadDirNamePath($fileName, $fileMd5);
    UploadFileHelper::createPath($fileChunkUploadDirNamePath);
    $chunkPath = UploadFileHelper::getFileChunkNamePath($fileName, $fileMd5, $chunk);
    //移动文件
    if (!move_uploaded_file($_FILES ['Filedata']["tmp_name"], $chunkPath)) {
        throw new Exception("文件上传失败1");//
    }
    //校验文件是否完整
    $file_val = 'Filedata';//这个是表单的name值,如果是多个文件的话,这个值就会变成数组
    if (!UploadFileHelper::checkChunkIsComplete($fileName, $chunkactualsize, $file_val)) {
        throw new Exception("文件上传失败2");
    }

    //检测所有分片是否已经全部上传完
    return UploadFileHelper::checkAllChunkIsUploaded($fileName, $fileMd5, $chunks);

}


function composeFile()
{
    //建议另外写一个php命令去异步合成.//这边为了方柏直接同步合成
    //php -q  mergeFIle.php newfilename chunkpath
    //合成文件


    //组装参数,新文件
    $fileName = $_FILES ['Filedata']["name"];
    $fileMd5 = $_POST['cusmd5val'];
    $chunks = $_POST['chunks'];

    $fileChunkUploadDirNamePath = UploadFileHelper::getFileChunkUploadDirNamePath($fileName, $fileMd5);
    $composeFileNamePath = UploadFileHelper::getComposeFileNamePath($fileName, $fileMd5);


    //创建合成路径
    $composePath = UploadFileHelper::getComposePath();
    UploadFileHelper::createPath($composePath);


    //合成文件
    try {

        if (!$out = @fopen($composeFileNamePath, "wb")) {
            throw new Exception('无法写入文件');
        }

        if (flock($out, LOCK_EX)) {
            for ($index = 0; $index < $chunks; $index++) {
                if (!$in = @fopen(UploadFileHelper::getFileChunkNamePath($fileName, $fileMd5, $index), "rb")) {
                    break;
                }

                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }

                @fclose($in);
                @unlink(UploadFileHelper::getFileChunkNamePath($fileName, $fileMd5, $index));
            }

            flock($out, LOCK_UN);
        }
        @fclose($out);
    } catch (Exception $e) {
        throw new Exception('合成文件失败');
    }


}


?>