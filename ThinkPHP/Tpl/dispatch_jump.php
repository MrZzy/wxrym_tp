<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<LINK rel="Bookmark" href="/favicon.ico" >
<LINK rel="Shortcut Icon" href="/favicon.ico" />
<title>提示信息 - <?php echo (!empty($cfg_back['back_title'])) ? $cfg_back['back_title'] : (!empty($cfg_index['web_title']) ? $cfg_index['web_title'] : '浙江彩狗文化传播有限公司');?></title>
<style type="text/css">
*{ padding:0; margin:0; font-size:12px}
a:link,a:visited{text-decoration:none;color:#0068a6}
a:hover,a:active{color:#ff6600;text-decoration: underline}
.showMsg{border: 1px solid #1e64c8; zoom:1; width:450px; height:172px;position:absolute;top:44%;left:50%;margin:-87px 0 0 -225px}
.showMsg h5{background-image: url(/Public/Common/images/msg.png);background-repeat: no-repeat; color:#fff; padding-left:35px; height:25px; line-height:26px;*line-height:28px; overflow:hidden; font-size:14px; text-align:left}
.showMsg .content{ padding:46px 12px 10px 45px; font-size:14px; height:64px; text-align:left}
.showMsg .bottom{ background:#e4ecf7; margin: 0 1px 1px 1px;line-height:26px; *line-height:30px; height:26px; text-align:center}
.showMsg .tips{display:inline-block;display:-moz-inline-stack;zoom:1;*display:inline;max-width:330px}
.showMsg .error{background: url(/Public/Common/images/msg_bg.png) no-repeat 0px -460px;}
.showMsg .success{background: url(/Public/Common/images/msg_bg.png) no-repeat 0px -560px;}
</style>
</head>
<body>
<div class="showMsg" style="text-align:center">
	<h5>提示信息</h5>
	<?php 
	if(isset($message)) {
		echo "<div class='content tips success'>".$message."</div>";
	}else{
		echo "<div class='content tips error'>".$error."</div>";
	}
	?>
    <div class="bottom">
    	[ <a href="<?php echo($jumpUrl); ?>" id="href" ><b id="wait"><?php echo($waitSecond); ?></b> 秒后将自动跳转,点击立即跳转</a> ]
	</div>
</div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>
