<?php
return array(	// C('配置项') 取值
	//'配置项'=>'配置值'
	
    'URL_MODEL'	=> 2, //url访问模式 : 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
	
	'DB_TYPE'		=> 'mysql',	//数据库类型
	/*
	'DB_HOST'		=> 'host',	//服务器地址
	'DB_NAME'		=> 'db_name',	//数据库名称
	'DB_USER'		=> 'db_user',	//登录用户名
	'DB_PWD'		=> 'db_pwd',	//密码
	*/
	'DB_PORT'		=> '3306',	//数据库端口
	'DB_PREFIX'		=> 'wx_',	//数据库表前缀
	'DB_CHARSET'	=> 'utf8', // 数据库的编码 默认为utf8
	'DB_DEBUG'  	=>  TRUE, // 数据库调试模式 开启后可以记录SQL日志
	
	//'SHOW_PAGE_TRACE' =>TRUE, // 显示页面Trace信息
	
		//自定义配置信息
	'CACHE_TIME'		=> 300,	//数据缓存时长(秒)
	'FRONT_PAGE_SIZE'	=> 40,	//前台分页中每页显示数量
    'COMMON_RES_PATH'	=> '/Public/Common/',    //公用资源文件路径
    'FRONT_RES_PATH'	=> '/Public/Front/',    //前台资源文件路径
    'BACK_RES_PATH'		=> '/Public/Back/',    //后台资源文件路径
	'UP_PATH'			=> 'Upload/',	//文件上传路径
	'GET_FILE_PATH'			=> '/Upload/',	//已上传文件获取路径
    
    'ERROR_MESSAGE'			=> '页面错误！请稍后再试～',//错误显示信息,非调试模式有效
    'ERROR_PAGE'			=> THINK_PATH.'Tpl/404_not_found.php',	// 错误定向页面
    'TMPL_ACTION_ERROR'		=> THINK_PATH.'Tpl/dispatch_jump.php', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'	=> THINK_PATH.'Tpl/dispatch_jump.php', // 默认成功跳转对应的模板文件
    // 'TMPL_EXCEPTION_FILE'   => THINK_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件
    'TMPL_EXCEPTION_FILE'   => THINK_PATH.'Tpl/404_not_found.php',// 异常页面的模板文件
	
	'URL_HTML_SUFFIX' => '', // URL伪静态后缀设置
	//路由规则
	'URL_ROUTER_ON'   => true, //开启路由
	'URL_ROUTE_RULES' => array( //定义路由规则
		'wx/:wxno_srch/:cat_id' => 'Wxno/Index/wxnoList',	//公众号列表页(关键词+分类查) -> 关键词
		'wx/:wxno_srch'	=> 'Wxno/Index/wxnoList',	//公众号查询传入变量(关键词查) $_GET['wxno_srch']
		'wxc/:cat_id/:wxno_srch' => 'Wxno/Index/wxnoListByCat',	//公众号列表页(关键词+分类查) -> 分类
		'wxc/:cat_id' => 'Wxno/Index/wxnoListByCat',	//公众号列表页(分类查)
		'wx' => 'Wxno/Index/wxnoList',	//全部公众号列表页
		'wxc' => 'Wxno/Index/wxnoList',	//全部公众号列表页
		'wxs/:sel_type' => 'Wxno/Index/wxnoListBySelType',	//最新、推荐公众号列表页
		'wxs' => 'Wxno/Index/wxnoList',	//无类别
		// 'wx/`/p/:p\d' => 'Wxno/Index/wxnoList',	//公众号列表有刷新分页
		'wxno' => 'Wxno/Index/wxnoPageList',	//公众号列表无刷新分页追加
		
		'wxart/:wx_no' => 'Wxarticle/Index/wxartList',	//公众号详情页(公众号文章)
		'wxart' => 'Wxarticle/Index/allartList',	//所有文章列表
		'wart/:sel_type' => 'Wxarticle/Index/wxartListBySelType',
		'wart' => 'Wxarticle/Index/wxartList',
		'wxartp' => 'Wxarticle/Index/wxartPageList',	//公众号列表无刷新分页追加
		
		'a/:aid' => 'Wxarticle/Index/artContent',	//公众号文章详情

		'about' => 'Webarticle/Index/about',
		'contact' => 'Webarticle/Index/contact',
	),
);