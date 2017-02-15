<?php
//截取字符串
function subtext($text, $length){
    if(mb_strlen($text, 'utf8') > $length)
		return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}

//取当前客户端IP
function GetIP(){
	// $ip = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "unknown";
	if (strpos($ip,'::')>-1)	// localhost识别为 -> ::1
		$ip = '127.0.0.1';
	return $ip;
}

//生成指定长度的随机数
function randomkeys($length){
	$key = "";
	$pattern='1234567890abcdefghijklmnopqrstuvwxyz';
	for($i=0;$i<$length;$i++){
		$key .= $pattern[mt_rand(0,35)];
	}
	return $key;
}

//创建目录
function createFolder($path){
	if (!file_exists($path)){
		createFolder(dirname($path));
		mkdir($path, 0777);
	}
}

?>