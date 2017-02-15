<?php
namespace Extend\Init;
use Think\Controller;

class Init extends Controller{
	//判断登录、用户权限等
	public function init(){
		$this->do_main = 'http://'.$_SERVER['HTTP_HOST'];
		
		$model = new \Think\Model();
		
		$admin_id = session('admin.id');
		if (empty($admin_id)){
			//$this->error('登录超时或尚未登录，请重新登录！',U('Home/Index/index'));
			$show_content = "
				<script type=\"text/javascript\" src=\"".C('COMMON_RES_PATH')."lib/jquery/1.9.1/jquery.min.js\"></script>
				<script type=\"text/javascript\" src=\"".C('COMMON_RES_PATH')."lib/layer/2.1/layer.js\"></script>
				<script type=\"text/javascript\">
				layer.config({
					skin:'layer-ext-espresso',
					extend:'skin/espresso/style.css'
				});
				layer.msg(
					\"登录超时或尚未登录，请重新登录！\",
					{icon:0,time:3000},
					function(){
						parent.location.href = \"/admin.php/Home/Index/index\";
					}
				);
				</script>
				";
			$this->show("登录超时或尚未登录，请重新登录！<br />".$show_content);
			exit;
		}
		
		//取后台配置信息
		$read_cfg_back = S('cfg_back');
		if (!empty($read_cfg_back)){
			$arr_cfg = S('cfg_back');
		}else{
			$data_cfg_back = array(
				'params' => 'cfg_tag,cfg_value',
				'where' => "1 = 1",
			);
			$arr_cfg = $model->table('wx_config')->field($data_cfg_back['params'])->where($data_cfg_back['where'])->select();
			S('cfg_back',$arr_cfg,C('CACHE_TIME'));
		}
		$cfg_back = array();
		foreach ($arr_cfg as $tag=>$val){
			$cfg_back[$arr_cfg[$tag]['cfg_tag']] = $arr_cfg[$tag]['cfg_value'];
		}
		$this->assign('cfg_back',$cfg_back);
		
		//判断权限
		$this_url = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		$this_mc = MODULE_NAME.'/'.CONTROLLER_NAME;
		$this_m = MODULE_NAME;
		$admin_access = session('admin.menu_access');
		if ($this_mc!='Home/Index' && $this_url!='Access/Index/selfChgPwd' && $this_m!='Mywx'){
			$access_check = $model->table('wx_admin_menu')
				->where("menu_id in (".$admin_access.") and action = '".$this_url."'")
				->select();
			if (count($access_check[0])<=0)
				$this->error('没有权限！');
		}
	}
	
	//记录日志信息
	public function logRec($arr){
		$model = new \Think\Model();
		$arr['action'] = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		$model->table('wx_admin_log')->add($arr);
	}
}
?>