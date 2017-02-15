<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	var $verify_config;
	var $verify;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		// $this->Init->init();
		$this->verify_config =	array(
			'seKey'     =>  'ThinkPHP.CN',   // 验证码加密密钥
			'codeSet'   =>  '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',             // 验证码字符集合
			'expire'    =>  1800,            // 验证码过期时间（s）
			'useImgBg'  =>  false,           // 使用背景图片 
			'fontSize'  =>  18,              // 验证码字体大小(px)
			'useCurve'  =>  false,            // 是否画混淆曲线
			'useNoise'  =>  true,            // 是否添加杂点	
			'imageH'    =>  41,               // 验证码图片高度
			'imageW'    =>  200,               // 验证码图片宽度
			'length'    =>  5,               // 验证码位数
			'fontttf'   =>  '',              // 验证码字体，不设置随机获取
			'bg'        =>  array(243, 251, 254),  // 背景颜色
			'reset'     =>  true,           // 验证成功后是否重置
		);
		$this->verify = new \Think\Verify($this->verify_config);
	}
	
    public function index(){
		$adminid = session('admin.id');
		if (empty($adminid)){
			// $this->redirect('Index/login');
			$this->redirect('login');
		}else{
			$this->redirect('Index/main');
		}
    }
	
	public function main(){
		$this->Init->init();
		$data_menu_parent = array(
			'where' => 'menu_id in ('.session('admin.menu_access').') and is_menu = 1 and parent_id = 0 and is_delete = 0',
		);
		$data_menu_child = array(
			'where' => 'menu_id in ('.session('admin.menu_access').') and is_menu = 1 and parent_id > 0 and is_delete = 0',
		);
		$menu_parent = D('AdminMember')->readAdminMenuAccess($data_menu_parent);
		$menu_child = D('AdminMember')->readAdminMenuAccess($data_menu_child);
		
		$this->assign('menu_parent',$menu_parent);
		$this->assign('menu_child',$menu_child);

		//清空缓存
		$c_cache_url = 'Config/Index/clearCache';
		$access_check_cache = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$c_cache_url."'")
			->select();
		if (count($access_check_cache[0])<1){
			$this->assign('unabled_c_cache',1);
		}
		
		$this->display('main');
	}
	
	public function verifyCode(){
		$this->verify->entry();
	}
	
	public function login(){
		$params = I('post.');
		//取后台配置信息
		$model = new \Think\Model();
		$data_cfg_back = array(
			'params' => 'cfg_tag,cfg_value',
			'where' => "1 = 1",
		);
		$arr_cfg = $model->table('wx_config')->field($data_cfg_back['params'])->where($data_cfg_back['where'])->select();
		$cfg_back = array();
		foreach ($arr_cfg as $tag=>$val){
			$cfg_back[$arr_cfg[$tag]['cfg_tag']] = $arr_cfg[$tag]['cfg_value'];
		}
		$this->assign('cfg_back',$cfg_back);
		if (empty($params)){
			$this->display('login');
		}else{
			$vright = true;
			if ($cfg_back['back_login_vertify']=='1'){	//后台登录开启验证码
				$verify_code = $params['verifyCode'];
				$verify_check = $this->verify->check($verify_code);
				$vright = $verify_check;
				if (false===$verify_check){
					/*$this->error('验证码错误！','',1);
					return;*/
					echo json_encode(array('code'=>-1,'msg'=>'验证码错误！'));
					exit();
				}
			}
			if ($vright){
				$res = D('AdminMember')->adminLogin($params);
				if (count($res)>0){
					session('admin',$res);
					
					//记录登录信息及登录日志
					$data_loginInfo = array(
						'data' => array(
							'last_login_time' => date('Y-m-d H:i:s',time()),
							'last_login_ip' => GetIP(),
						),
						'where' => 'id = '.session('admin.id'),
					);
					D('AdminMember')->updLoginInfo($data_loginInfo);
					$data_log = array(
						'log_type' => 1,	//1登录;2操作
						'user_name' => session('admin.member_name'),
						'action' => '',
						'log_desc' => '登录成功',
						'ip' => GetIP(),
						'add_time' => date('Y-m-d H:i:s',time()),
					);
					$this->Init->logRec($data_log);
					
					// $this->redirect('Index/main');
					echo json_encode(array('code'=>1,'msg'=>'登录成功'));
				}else{
					// $this->error('用户名或密码错误，请重新登录！');
					echo json_encode(array('code'=>-2,'msg'=>'用户名或密码错误，请检查！'));
					exit();
				}
			}
		}
	}
	
	public function welcome(){
		$this->Init->init();
		if (session('admin.group_id')==1){
			$this->display('welcome');
		}else{
			$this->display('welcome2');
		}
	}
	
	public function logout(){
		$this->Init->init();
		// session('admin',null);	//清空单个session ,也可 -> session('admin.id',null);
		session(null);	//清空当前session
		$this->success('您已成功退出，请重新登录！',U('index'),2);
	}
	
	/*function getCount($table,$met){
		$data_count = array(
			'table' => $table,
            'pref' => 'wx_',
			'where' => '',
		);
		switch ($met){
			case 'today':
				$data_count['where'] = "date(add_time) = curdate()";
				break;
			case 'yesterday':
				$data_count['where'] = "date(add_time) = date_sub(curdate(),interval 1 day)";
				break;
			case 'week':
				$data_count['where'] = "YEARWEEK(date_format(add_time,'%Y-%m-%d')) = YEARWEEK(now())";
				break;
			case 'month':
				$data_count['where'] = "DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT(now(),'%Y-%m')";
				break;
			case 'all':
				$data_count['where'] = "";
				break;
			default:
				$data_count['where'] = "";
				break;
		}
		$count_info = D('AdminMember')->getWelcomInfoCount($data_count);
		return $count_info;
	}*/
}