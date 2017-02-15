<?php
$the_host = $_SERVER['HTTP_HOST'];//取得当前域名
$the_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';//判断地址后面部分
if(strpos($the_host,'www')<0 || false===strpos($the_host,'www'))//如果域名是不带www的网址那么进行下面的301跳转
{
	header('HTTP/1.1 301 Moved Permanently');//发出301头部
	header('Location:http://www.'.$the_host.$the_url);//跳转到带www的网址
	exit(0);
}
?>