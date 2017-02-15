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

//时间转为几分钟前、几小时前 ···
function time_tran($the_time) {  
    $now_time = date("Y-m-d H:i:s", time());  
    //echo $now_time;  
    $now_time = strtotime($now_time);  
    $show_time = strtotime($the_time);  
    $dur = $now_time - $show_time;  
    if ($dur < 0) {  
        return $the_time;  
    } else {  
        if ($dur < 60) {  
            return $dur . '秒前';  
        } else {  
            if ($dur < 3600) {  
                return floor($dur / 60) . '分钟前';  
            } else {  
                if ($dur < 86400) {  
                    return floor($dur / 3600) . '小时前';  
                } else {  
                    if ($dur < 259200) {//3天内  
                        return floor($dur / 86400) . '天前';  
                    } else {  
                        return substr($the_time,0,10);
                    }  
                }  
            }  
        }  
    }  
}

//获取用户系统
function getOS(){
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;
    if (preg_match('/win/i', $agent) && strpos($agent, '95')){
        $os = 'Windows 95';
    }else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')){
        $os = 'Windows ME';
    }else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)){
        $os = 'Windows 98';
    }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)){
        $os = 'Windows XP';
    }else if (preg_match('/win/i', $agent) && preg_match('/nt 5.2/i', $agent)){
        $os = 'Windows 2003';
    }else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)){
        $os = 'Windows 2000';
    }else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)){
        $os = 'Windows NT';
    }else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)){
        $os = 'Windows 32';
    }else if (preg_match('/linux/i', $agent)){
        $os = 'Linux';
    }else if (preg_match('/unix/i', $agent)){
        $os = 'Unix';
    }else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)){
        $os = 'SunOS';
    }else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)){
        $os = 'IBM OS/2';
    }else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)){
        $os = 'Macintosh';
    }else if (preg_match('/PowerPC/i', $agent)){
        $os = 'PowerPC';
    }else if (preg_match('/AIX/i', $agent)){
        $os = 'AIX';
    }else if (preg_match('/HPUX/i', $agent)){
        $os = 'HPUX';
    }else if (preg_match('/NetBSD/i', $agent)){
        $os = 'NetBSD';
    }else if (preg_match('/BSD/i', $agent)){
        $os = 'BSD';
    }else if (preg_match('/OSF1/i', $agent)){
        $os = 'OSF1';
    }else if (preg_match('/IRIX/i', $agent)){
        $os = 'IRIX';
    }else if (preg_match('/FreeBSD/i', $agent)){
        $os = 'FreeBSD';
    }else if (preg_match('/teleport/i', $agent)){
        $os = 'teleport';
    }else if (preg_match('/flashget/i', $agent)){
        $os = 'flashget';
    }else if (preg_match('/webzip/i', $agent)){
        $os = 'webzip';
    }else if (preg_match('/offline/i', $agent)){
        $os = 'offline';
    }else{
        $os = 'Unknown';
    }
    return $os;
}

//获取浏览器
function getBrowse(){
    $Agent = $_SERVER['HTTP_USER_AGENT'];
    $browseinfo='';
    if(preg_match('/Opera/', $Agent)) {
        $browseinfo = 'Opera';
    }else if(preg_match('/Mozilla/', $Agent) && preg_match('/MSIE/', $Agent)){
        $browseinfo = 'Internet Explorer';
    }else if(preg_match('/Chrome/', $Agent)){
        $browseinfo="Chrome";
    }else if(preg_match('/Safari/', $Agent)){
        $browseinfo="Safari";
    }else if(preg_match('/Firefox/', $Agent)){
        $browseinfo="Firefox";
    }else if(preg_match('/Mozilla/', $Agent) && !preg_match('/MSIE/', $Agent)){
        $browseinfo = 'Netscape Navigator';
    }else{
        $browseinfo="Unknown";
    }
    return $browseinfo;
}

?>