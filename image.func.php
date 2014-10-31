<?php
// 通过GD库做验证码
/**
 * 制作验证码
 * @param int $type			验证码的类型1.数字2.字母3.数字和字母
 * @param int $length		验证码的长度
 * @param int $pixel		干扰点的个数
 * @param int $line			干扰线的条数
 * @param string $sess_name	验证码存储在session中的名字
 */
function varifyImage($type = 1, $length = 4, $pixel = 0, $line = 0, $sess_name = "verify") {
	// 创建画布
	$width = 80;
	$height = 30;
	$image = imagecreatetruecolor ( $width, $height );
	$white = imagecolorallocate ( $image, 255, 255, 255 );
	$black = imagecolorallocate ( $image, 0, 0, 0 );
	// 用填充矩形填充画布
	imagefilledrectangle ( $image, 1, 1, $width - 2, $height - 2, $white );
	$chars = buildRandomString ( $type, $length );
	$_SESSION [$sess_name] = $chars;
	$fontfiles = array (
			'SIMLI.TTf',
			'SIMYOU.TTf',
			'STCAIYUN.TTf',
			'STLITI.TTf',
			'YGYXSZITI2.0.TTF' 
	);
	for($i = 0; $i < $length; $i ++) {
		$size = mt_rand ( 14, 18 );
		$angle = mt_rand ( - 15, 15 );
		$x = 5 + $i * $size;
		$y = mt_rand ( 20, 26 );
		$fontfile = "../fonts/" . $fontfiles [mt_rand ( 0, count ( $fontfiles ) - 1 )];
		$color = imagecolorallocate ( $image, mt_rand ( 50, 90 ), mt_rand ( 80, 200 ), mt_rand ( 80, 180 ) );
		$text = substr ( $chars, $i, 1 );
		imagettftext ( $image, $size, $angle, $x, $y, $color, $fontfile, $text );
	}
	if ($pixel) {
		for($i = 0; $i < $pixel; $i ++) {
			imagesetpixel ( $image, mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), $black );
		}
	}
	if ($line) {
		for($i = 1; $i < $line; $i ++) {
			$color = imagecolorallocate ( $image, mt_rand ( 50, 90 ), mt_rand ( 80, 200 ), mt_rand ( 90, 180 ) );
			imageline ( $image, mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), $color );
		}
	}
	header ( "content-type:image/gif" );
	imagegif ( $image );
	imagedestroy ( $image );
}


/**
 * 生成缩略图
 * @param string $filename			需要生成缩略图的原图片的文件名
 * @param string $destination		生成的缩略图的存储地址（文件夹）
 * @param string $dst_w				缩略图的宽
 * @param string $dst_h				缩略图的高
 * @param bool $isReservedSource	原文件是否需要保留
 * @param real $scale				缩略比例
 * @return string					缩略图的文件名
 */
function thumb($filename, $destination = null, $dst_w = null, $dst_h = null, $isReservedSource = true, $scale = 0.5) {
	list ( $src_w, $src_h, $imagetype ) = getimagesize ( $filename );
	if (is_null ( $dst_w ) || is_null ( $dst_h )) {
		$dst_w = ceil ( $src_w * $scale );
		$dst_h = ceil ( $src_h * $scale );
	}
	$mime = image_type_to_mime_type ( $imagetype );
	$createFun = str_replace ( "/", "createfrom", $mime );
	$outFun = str_replace ( "/", null, $mime );
	$src_image = $createFun ( $filename );
	$dst_image = imagecreatetruecolor ( $dst_w, $dst_h );
	imagecopyresampled ( $dst_image, $src_image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h );
	// image_50/sdfsdkfjkelwkerjle.jpg
	if ($destination && ! file_exists ( dirname ( $destination ) )) {
		mkdir ( dirname ( $destination ), 0777, true );
	}
	$dstFilename = $destination == null ? getUniName () . "." . getExt ( $filename ) : $destination;
	$outFun ( $dst_image, $dstFilename );
	imagedestroy ( $src_image );
	imagedestroy ( $dst_image );
	if (! $isReservedSource) {
		unlink ( $filename );
	}
	return $dstFilename;
}

/**
 * 添加文字水印
 * 
 * @param string $filename	图片地址
 * @param string $text		水印文字
 * @param string $fontfile	水印文字的字体   	
 */
function waterText($filename, $text = "tianf", $fontfile = "STLITI.TTF") {
	$fileInfo = getimagesize ( $filename );
	$mime = $fileInfo ['mime'];
	$createFun = str_replace ( "/", "createfrom", $mime );
	$outFun = str_replace ( "/", null, $mime );
	$image = $createFun ( $filename );
	$color = imagecolorallocatealpha ( $image, 255, 0, 0, 50 );
	$fontfile = "../fonts/{$fontfile}";
	imagettftext ( $image, 14, 0, 0, 14, $color, $fontfile, $text );
	$outFun ( $image, $filename );
	imagedestroy ( $image );
}

/**
 * 添加图片水印
 * 
 * @param string $dstFile	需要水印的图片地址
 * @param string $srcFile	水印图片地址
 * @param int $pct			透明度
 */
function waterPic($dstFile, $srcFile = "../image_50/jju.jpg", $pct = 30) {
	$srcFileInfo = getimagesize ( $srcFile );
	$src_w = $srcFileInfo [0];
	$src_h = $srcFileInfo [1];
	$srcMime = $srcFileInfo ['mime'];
	$createSrcFun = str_replace ( "/", "createfrom", $srcMime );
	$src_im = $createSrcFun ( $srcFile );
	$dstFileInfo = getimagesize ( $dstFile );
	$dstMime = $dstFileInfo ['mime'];
	$createDstFun = str_replace ( "/", "createfrom", $dstMime );
	$outDstFun = str_replace ( "/", null, $dstMime );
	$dst_im = $createDstFun ( $dstFile );
	imagecopymerge ( $dst_im, $src_im, 0, 0, 0, 0, $src_w, $src_h, $pct );
	$outDstFun ( $dst_im, $dstFile );
	imagedestroy ( $dst_im );
	imagedestroy ( $src_im );
}