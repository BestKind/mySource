<?php
/**
 * 构建上传信息
 * @return array	上传信息的二维数组（一个文件一个一维数组）
*/
function buildInfo(){
	if (!$_FILES) {
		return;
	}
	$i = 0;
	$file = array();
	foreach ($_FILES as $v){
		if (is_string($v['name'])) {
			//单文件
			$file[$i] = $v;
			$i++;
		} else {
			//多文件
			foreach ($v['name'] as $key=>$val) {
				$file[$i]['name'] = $val;
				$file[$i]['size'] = $v['size'][$key];
				$file[$i]['tmp_name'] = $v['tmp_name'][$key];
				$file[$i]['error'] = $v['error'][$key];
				$file[$i]['type'] = $v['type'][$key];
				$i++;
			}
		}
	}
	return $file;
}

/**
 * 多文件上传
 * @param string $path		上传路径
 * @param array $allowExt	允许上传文件的扩展名
 * @param number $maxSize	允许上传的最大大小
 * @param string $imgFlag	是否只允许上传图片
 * @return array			上传文件的文件名
 */
function uploadFile($path = "uploads", $allowExt = array("gif", "jpeg", "jpg", "png", "wbmp"), $maxSize = 2097152, $imgFlag = true) {
	if (! file_exists ( $path )) {
		mkdir ( $path, 0777, true );
	}
	$i = 0;
	$files = buildInfo ();
	if (!($files && is_array($files))) {
		return;
	}
	foreach ( $files as $file ) {
		if (UPLOAD_ERR_OK == $file ['error']) {
			// 判断文件是否是通过HTTP的POST方式上传上来的
			$ext = getExt ( $file ['name'] );
			if (! in_array ( $ext, $allowExt )) {
				exit ( "非法文件类型" );
			}
			if ($file ['size'] > $maxSize) {
				exit ( "文件过大" );
			}
			// 验证上传文件是否是一个真正的图片类型
			if ($imgFlag) {
				if (! getimagesize ( $file ['tmp_name'] )) {
					exit ( "不是真正的图片类型" );
				}
			}
				
			$filename = getUniName () . "." . $ext;
			if (! file_exists ( $path )) {
				mkdir ( $path, 0777, true );
			}
			$destination = $path . "/" . $filename;
			if (! is_uploaded_file ( $file ['tmp_name'] )) {
				exit ( "文件不是通过HTTP的POST方式上传上来的" );
			}
			if (move_uploaded_file ( $file ['tmp_name'], $destination )) {
				$file ['name'] = $filename;
				unset ( $file ['error'], $file ['tmp_name'], $file ['size'], $file ['type'] );
				$uploadedFiles [$i] = $file;
				$i ++;
			}
		} else {
			switch ($file ['error']) {
				case 1 :
					$msg = "超过了配置文件上传文件的大小"; // UPLOAD_ERR_INI_SIZE
					break;
				case 2 :
					$msg = "超过了表单设置上传文件的大小"; // UPLOAD_ERR_FORM_SIZE
					break;
				case 3 :
					$msg = "文件部分被上传"; // UPLOAD_ERR_PARTIAL
					break;
				case 4 :
					$msg = "没有文件被上传"; // UPLOAD_ERR_NO_FILE
					break;
				case 5 :
					$msg = "没有找到临时目录"; // UPLOAD_ERR_NO_TMP_DIR
					break;
				case 6 :
					$msg = "文件不可写"; // UPLOAD_ERR_CANT_WRITE
					break;
				case 7 :
					$msg = "由于PHP扩展程序中断了文件上传"; // UPLOAD_ERR_EXTENSION
					break;
			}
			echo $msg;
		}
	}
	return $uploadedFiles;
}