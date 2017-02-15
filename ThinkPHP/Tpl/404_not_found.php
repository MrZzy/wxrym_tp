<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="Shortcut Icon" href="/favicon.ico" />
<link href="<?php echo C('COMMON_RES_PATH');?>css/404.css" rel="stylesheet" type="text/css" media="all" />

<style type="text/css">
#wait{
	color:red;
	font-weight:bold;
}
</style>
<title>404 - 微信任意门</title>

</head>
<body>
	<div class="wrap">
		<div class="content">
			<div class="logo">
				<h1><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>" title="返回首页" id="href"><img src="<?php echo C('COMMON_RES_PATH');?>images/404/logo.png" /></a></h1>
				<span><img src="<?php echo C('COMMON_RES_PATH');?>images/404/signal.png" />Oops! The Page you requested was not found！<font id="wait">10</font>秒后跳转至首页...</span>
			</div>
			<div class="buttom">
				<div class="seach_bar">
					<p>you can go to <span><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>">home page</a></span> or search here</p>
					<div class="search_box">
					<form action="http://<?php echo $_SERVER['HTTP_HOST'];?>/wx" method="post">
					   <input type="text" value="Search" name="wxno_srch" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'Search';}" /><input type="submit" value="" />
				    </form>
					 </div>
				</div>
			</div>
		</div>
		<p class="copy_right">&#169; <?php echo date('Y',time());?> Powered by<a href="http://<?php echo $_SERVER['HTTP_HOST'];?>" target="_blank">&nbsp;微信任意门</a> </p>
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