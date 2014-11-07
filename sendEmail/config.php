<?php

//使用163邮箱服务器
const SMTPSERVER = "smtp.163.com";
//163邮箱服务器端口
const SMTPSERVERPORT = 25;
//你的163服务器邮箱账号
const SMTPUSERMAIL = "****@163.com";
//你的邮箱账号(去掉@163.com)
const SMTPUSER = "****";
//你的邮箱密码
const SMTPPASS = "****";
//邮件主题 
const MAILSUBJECT = "发一封测试一下";

//这里面的一个true是表示使用身份验证,否则不使用身份验证. 
$smtp = new smtp(SMTPSERVER, SMTPSERVERPORT, true, SMTPUSER, SMTPPASS);
//是否显示发送的调试信息 
$smtp->debug = false;

?>