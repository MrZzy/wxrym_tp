<?php
namespace Extend\Init;
use Think\Controller;

class Init extends Controller{

	public function init(){
		$this->do_main = 'http://'.$_SERVER['HTTP_HOST'];
		//保存用户访问信息
        $visit_url = $this->do_main . $_SERVER["REQUEST_URI"];
        $refer_url = $_SERVER['HTTP_REFERER'];
        $visit_ip = GetIP();
        $visit_agent = getOS().' -> '.getBrowse();
        $visit_time = date('Y-m-d H:i:s',time());
            //判断是否为同一天、同一ip，若相同，则累计次数
        $is_same = M('visitinfo','wx_')->where('visit_url = "'.$visit_url.'" and agent = "'.$visit_agent.'" and visit_ip = "'.$visit_ip.'" and datediff(add_time,"'.$visit_time.'") = 0')->select();
        if (is_array($is_same) and count($is_same)>0){
            M('visitinfo','wx_')->where('visit_url = "'.$visit_url.'" and agent = "'.$visit_agent.'" and visit_ip = "'.$visit_ip.'" and datediff(add_time,"'.$visit_time.'") = 0')->setInc('visit_count');
        }else {
            $data_visit = array(
                'visit_url' => $visit_url,
                'refer_url' => $refer_url,
                'visit_ip' => $visit_ip,
                'agent' => $visit_agent,
                'add_time' => $visit_time,
            );
            $res = M('visitinfo', 'wx_')->data($data_visit)->add();
        }
		// 读取数据存入缓存
		$cache_menu = S('menu_top');
		if (!empty($cache_menu)){
			$menu_parent_top = $cache_menu['menu_parent_top'];
			// $menu_child_top = $cache_menu['menu_child_top'];
		}else{
			/* 顶部导航(二级导航)及底部导航 start */
			$menuModel = new \Home\Model\MenuModel();
			$data_parent_top = array(
				'params' => '*',
				'where' => 'm_type = 1 and parent_id = 0 and is_delete = 0',
				'order' => 'm_order desc',
				'limit_length' => '8',
			);
			$data_child_top = array(
				'params' => '*',
				'where' => 'm_type = 1 and parent_id <> 0 and is_delete = 0',
				'order' => 'm_order desc',
				'limit_length' => '8',
			);
			$menu_parent_top = $menuModel->menuList($data_parent_top);	//顶部一级导航
			// $menu_child_top = $menuModel->menuList($data_child_top);	//顶部二级导航
			
			foreach ($menu_parent_top as $key=>$val){
				//是否为外部链接 - 顶部父级导航
				if (($menu_parent_top[$key]['m_kind'] == '1') || (strpos($menu_parent_top[$key]['menu_url'],'http://')>-1))
					$menu_parent_top[$key]['is_out'] = '1';
				if (strpos($menu_parent_top[$key]['menu_url'],'http://')<=-1)
					$menu_parent_top[$key]['menu_url'] = $this->do_main.$menu_parent_top[$key]['menu_url'];
			}
			/* foreach ($menu_child_top as $key=>$val){
				//是否为外部链接 - 顶部子级导航
				if (($menu_child_top[$key]['m_kind'] == '1') || (strpos($menu_child_top[$key]['menu_url'],'http://')>-1))
					$menu_child_top[$key]['is_out'] = '1';
			} */
			$menu_top = array(
				'menu_parent_top' => $menu_parent_top,
				// 'menu_child_top' => $menu_child_top,
			);
			S('menu_top',$menu_top,C('CACHE_TIME'));
			// $this->assign('menu_parent_top',$menu_parent_top);
			// $this->assign('menu_child_top',$menu_child_top);
		}
		$this->assign('menu_parent_top',$menu_parent_top);
		// $this->assign('menu_child_top',$menu_child_top);
		
		/* 顶部导航(二级导航)及底部导航 end */
		
		/* 轮播广告 start */
		$bannerIndex_list = S('bannerIndex_list');
		$bannerSingle_list = S('bannerSingle_list');
		if (!empty($bannerIndex_list)){
			$this->bannerIndex_list = $bannerIndex_list;
		}else{
			$bannerModel = new \Banner\Model\BannerModel();
			$data_bannerIndex = array(
				'where' => 'is_delete = 0 and banner_type = 1',
			);
			$this->bannerIndex_list = $bannerModel->readBannerList($data_bannerIndex);
			S('bannerIndex_list',$this->bannerIndex_list,C('CACHE_TIME'));
		}
		if (!empty($bannerSingle_list)){
			$this->bannerSingle_list = $bannerSingle_list;
		}else{
			$bannerModel = new \Banner\Model\BannerModel();
			$data_bannerSingle = array(
				'where' => 'is_delete = 0 and banner_type = 2',
			);
			$this->bannerSingle_list = $bannerModel->readBannerList($data_bannerSingle);
			S('bannerSingle_list',$this->bannerSingle_list,C('CACHE_TIME'));
		}
		/* 轮播广告 end */
		
		/* 友情链接 start */
		$frdLink_list = S('frdLink_list');
		if (!empty($frdLink_list)){
			$this->frdLink_list = $frdLink_list;
		}else{
			$frdModel = new \FriendLink\Model\FrdlinkModel();
			$data_frdLink = array(
				'where' => 'is_delete = 0',
			);
			$this->frdLink_list = $frdModel->readfrdLinkList($data_frdLink);
			S('frdLink_list',$this->frdLink_list,C('CACHE_TIME'));
		}
		/* 友情链接 end */
		
		/* 公众号分类 start */
		$wxno_cat_list = S('wxno_cat_list');
		if (!empty($wxno_cat_list)){
			$this->wxno_cat_list = $wxno_cat_list;
		}else{
			$frdModel = new \WxnoCategory\Model\WxnoCategoryModel();
			$data_cat = array(
				'where' => 'is_delete = 0',
			);
			$this->wxno_cat_list = $frdModel->readCatList($data_cat);
			S('wxno_cat_list',$this->wxno_cat_list,C('CACHE_TIME'));
		}
		/* 公众号分类 end */
		
		/* 配置信息 */
		$cache_cfg = S('cfg_info');
		if (!empty($cache_cfg)){
			$arr_cfg = S('cfg_info');
		}else{
			$cfgModel = new \Home\Model\ConfigModel();
			$data_cfg = array(
				'params' => 'cfg_tag,cfg_value',
				'where' => "1 = 1",
			);
			$arr_cfg = $cfgModel->configInfo($data_cfg);	//顶部、底部配置信息
			S('cfg_info',$arr_cfg,C('CACHE_TIME'));
		}
		
		$cfg_index = array();
		foreach ($arr_cfg as $tag=>$val){
			$cfg_index[$arr_cfg[$tag]['cfg_tag']] = $arr_cfg[$tag]['cfg_value'];
		}
		$this->cfg_index = $cfg_index;
		$this->assign('cfg_index',$cfg_index);
		if ($cfg_index['web_close'] == '1'){	//网站关闭则跳转至相应页面
			$this->assign('close_reason',$cfg_index['web_close_reason']);
			$show_content = "
				<title>网站建设中 - 微信任意门</title>
				<link rel=\"Shortcut Icon\" href=\"/favicon.ico\" />
				<body style=\"margin:0px;padding:0px;background:url('".C('COMMON_RES_PATH')."images/404/bg.jpg') no-repeat 100%;background-size:100%;\">
					<table style=\"width:100%;height:100%;vertical-align:middle;text-align:center;font-size:60px;color:#ff4832;\">
						<tr>
							<td>".$cfg_index['web_close_reason']."<br />《 给您带来不便，敬请谅解 》</td>
						</tr>
					</table>
					<script type=\"text/javascript\" src=\"".C('COMMON_RES_PATH')."lib/jquery/1.9.1/jquery.min.js\"></script>
					<script type=\"text/javascript\" src=\"".C('COMMON_RES_PATH')."lib/layer/2.1/layer.js\"></script>
					<script type=\"text/javascript\">
					layer.config({
						skin:'layer-ext-espresso',
						extend:'skin/espresso/style.css'
					});
					layer.confirm(
						\"".$cfg_index['web_close_reason']."<br />您还可以前往官方论坛参与互动。\",
						{btn:['去论坛逛逛','为攻城狮给予鼓励']},
						function(){
							//window.location.href = \"http://www.7czx.com\";
							window.open(\"http://bbs.7czx.com/\");
						},
						function(){
							layer.msg(\"灰常感谢您的支持，我们的攻城狮一定会尽快攻下城池！\",{icon:8,time:3000});
						}
					);
					</script>
				</body>
				";
			$this->show($show_content);
			// throw new \Think\Exception($cfg_index['web_close_reason']);
			exit;
		}
	}
}
?>