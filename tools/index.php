<?php
require_once 'Excel/PHPExcel.php';
$type = isset ( $_GET ['type'] ) ? $_GET ['type'] : 0;
if (isset ( $type ) && 1 == $type) {
	header ( "Content-Type:text/html;charset=utf-8" );
	$file = $_FILES;
	$objPHPExcel = new PHPExcel ();
	$filename = explode ( ".", $file ['myfile'] ['name'] );
	if ($file ['myfile'] ['error']) {
		echo '文件出错！';
		exit;
	}
	if ('xlsx' != end($filename)) {
		echo '文件格式不对！请确定文件为Excel2007！！';
		exit;
	}
	$filePath = $file ['myfile'] ['tmp_name'];
	
	$PHPExcel = new PHPExcel ();
	$PHPReader = new PHPExcel_Reader_Excel2007 ();
	$PHPExcel = $PHPReader->load ( $filePath );
	$currentSheet = $PHPExcel->getActiveSheet ()->toArray ();
	
	// 获取用户浏览器的型号
	$ua = $_SERVER ["HTTP_USER_AGENT"];
	$filename = $filename [0] . ".txt";
	
	$encoded_filename = urlencode ( $filename );
	$encoded_filename = str_replace ( "+", "%20", $encoded_filename );
	header ( "Content-Type: application/octet-stream" );
	if (preg_match ( "/MSIE/", $ua )) {
		header ( 'Content-Disposition:  attachment; filename="' . $encoded_filename . '"' );
	} elseif (preg_match ( "/Firefox/", $ua )) {
		header ( 'Content-Disposition: attachment; filename*="utf8' . $filename . '"' );
	} else {
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	}
	// 输出
	for($i = 1; $i < count ( $currentSheet ); $i ++) {
		$row = "";
		foreach ( $currentSheet [$i] as $value ) {
			$row .= $value . "|";
		}
		echo $row . "\r\n";
	}
} else if (isset ( $type ) && 2 == $type) {
	header ( "Content-Type:text/html;charset=utf-8" );
	$file = $_FILES;
	$filename = explode ( ".", $file ['myfile'] ['name'] );
	if ($file ['myfile'] ['error']) {
		echo '文件出错！';
		exit;
	}
	if ('txt' != end($filename)) {
		echo '文件格式不对！请确定文件为txt！！';
		exit;
	}
	$filePath = $file ['myfile'] ['tmp_name'];
	$fp = fopen($filePath, "r");
	$data = array();

	$i=0;
	while(! feof($fp))
	{
		$rows = trim(fgets($fp));
		$rows = iconv("GBK", "UTF-8", $rows );
		$rows = str_replace("|", ",", $rows);
		$rows = explode(",", $rows);
		if (isset($rows[3])) {
			switch ($rows[3]) {
				case "01":
					$rows[3] = "错误数据";
					break;
				case "000":
					$rows[3] = "已收到申请";
					break;
				case "2":
					$rows[3] = "已分发至营销中心";
					break;
				case "4":
					$rows[3] = "待签收或联系客户";
					break;
				case "9":
					$rows[3] = "已签收联系客户或资料收集录入中";
					break;
				case "105":
					$rows[3] = "已核卡";
					break;
				case "53":
					$rows[3] = "已拒绝";
					break;
				case "52":
					$rows[3] = "已放弃";
					break;
				case "5":
				case "6":
				case "7":
				case "8":
				case "15":
				case "104":
					$rows[3] = "信审审批中";
					break;
			}
		}
		foreach ($rows as $key=>$row) {
			$data[$i][$key] = $row;
		}
		$i++;
	}
	fclose($fp);
	$filename = $filename [0];
	explor($filename, $data);
} else {
	exit;
}

function explor($filename,$data){
	$filename = $filename . ".xlsx";
	$ua = $_SERVER["HTTP_USER_AGENT"];
	$encoded_filename = urlencode($filename);
	$encoded_filename = str_replace("+", "%20", $encoded_filename);
	$objPHPExcel = new PHPExcel();
	/*以下是一些设置 ，什么作者  标题啊之类的*/
	$objPHPExcel->getProperties()->setCreator("hotsales")
	->setLastModifiedBy("hotsales")
	->setTitle("数据EXCEL导出")
	->setSubject("数据EXCEL导出")
	->setDescription("备份数据")
	->setKeywords("excel")
	->setCategory("result file");
	
	$objPHPExcel->setActiveSheetIndex(0);
	$objActSheet = $objPHPExcel->getActiveSheet();
	$objActSheet->setTitle($filename);
	
	for ($i=0; $i<count($data); $i++) {
		if (isset($data[$i]) && !empty($data[$i][0])) {
			$objPHPExcel->getActiveSheet()->setCellValue('A'.($i+1), " ".$data[$i][0]);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.($i+1), " ".$data[$i][1]);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.($i+1), " ".$data[$i][2]);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.($i+1), " ".$data[$i][3]);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.($i+1), " ".$data[$i][4]);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.($i+1), " ".$data[$i][5]);
		}
	}
	
	header('Content-Type: application/force-download');
	if (preg_match("/MSIE/",$ua) ) {
		header ( 'Content-Disposition:  attachment; filename="' . $encoded_filename . '"' );
	} elseif (preg_match ( "/Firefox/", $ua )) {
		header ( 'Content-Disposition: attachment; filename*="utf8' . $filename . '"' );
	} else {
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	}
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
}