<?php 
// 当前文件：download.php

$action = @$_GET['action'];

// 自己获取这些信息
$remote_url  = get_remote_file_url("./2.12_update.tar");
$file_size   = get_remote_file_size($remote_url);
$tmp_path    = get_tmp_path('./');

switch ($action) {
    case 'prepare-download':
        // 下载缓存文件夹
        $download_cache = __DIR__."/download_cache";

        if (!is_dir($download_cache)) {
            if (false === mkdir($download_cache)) {
                exit('创建下载缓存文件夹失败，请检查目录权限。');
            }
        }

        $tmp_path = $download_cache."/update_".time().".zip";

        save_tmp_path(); // 这里保存临时文件地址

        return json(compact('remote_url', 'tmp_path', 'file_size'));

        break;

    case 'start-download':

        // 这里检测下 tmp_path 是否存在

        try {
            set_time_limit(0);

            touch($tmp_path);

            // 做些日志处理

            if ($fp = fopen($remote_url, "rb")) {

                if (!$download_fp = fopen($tmp_path, "wb")) {
                    exit;
                }

                while (!feof($fp)) {

                    if (!file_exists($tmp_path)) {
                        // 如果临时文件被删除就取消下载
                        fclose($download_fp);

                        exit;
                    }

                    fwrite($download_fp, fread($fp, 1024 * 8 ), 1024 * 8);
                }

                fclose($download_fp);
                fclose($fp);

            } else {
                exit;
            }

        } catch (Exception $e) {
            Storage::remove($tmp_path);

            exit('发生错误：'.$e->getMessage());
        }

        return json(compact('tmp_path'));

        break;

    case 'get-file-size':

        // 这里检测下 tmp_path 是否存在

        if (file_exists($tmp_path)) {
            // 返回 JSON 格式的响应
            return json(['size' => filesize($tmp_path)]);
        }

        break;

    default:
        # code...
        break;
}