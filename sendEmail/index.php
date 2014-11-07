<?php
require_once 'smtp.php';
require_once 'config.php';


//收件人邮箱
$smtpemailto = "***@***";

//邮件内容 
$mailbody = "HELLO, 测试成功了哟!";
//邮件格式（HTML/TXT）,TXT为文本邮件 
$mailtype = "TXT";

//发送邮件
$smtp->sendmail($smtpemailto, SMTPUSERMAIL, MAILSUBJECT, $mailbody, $mailtype);
?>