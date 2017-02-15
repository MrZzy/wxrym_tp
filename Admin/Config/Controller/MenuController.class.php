<?php
namespace Config\Controller;
use Think\Controller;

class MenuController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function meList(){
		$this->menuList = D('Menu')->readMenuList();

		//禁用、启用
		$stop_url = 'Config/Menu/meStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}

		$this->display('menu_list');
	}
	public function meStop(){
		$params = I('post.');
		$data_meStop = array(
			'met' => $params['met'],
			'data' => array(
				'is_delete' => $params['met']=='stop' ? 1 : 0,
			),
			'where' => 'id = '.$params['meid']
		);
		$res_meStop = D('Menu')->menuSave($data_meStop);
		if (false===$res_meStop){
			$this->error('操作失败！');
		}else{
			$desc_met = $params['met']=='stop' ? '禁用' : '启用';
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => $desc_met.'站点导航信息，MEID【'.$params['meid'].'】，MENAME【'.$params['mename'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
		}
	}
	public function meEdit(){
		$params = I('request.');
		if ($params['meid']>0){
			$menu_info = D('Menu')->readMenuList('id = '.$params['meid']);
			$this->menuInfo = $menu_info[0];
		}
		$this->parMenu = D('Menu')->readMenuList('parent_id = 0');
		$data_meSave = array(
			'data' => array(
				'menu_name' => $params['menu_name'],
				'menu_tag' => $params['menu_tag'],
				'menu_url' => $params['menu_url'],
				'parent_id' => $params['parent_id'],
				'm_order' => $params['m_order'],
				'm_type' => $params['m_type'],
				'open_met' => $params['open_met'],
				'is_delete' => $params['is_delete'],
				'seo_title' => $params['seo_title'],
				'seo_keywords' => $params['seo_keywords'],
				'seo_desc' => $params['seo_desc']
			),
		);
		if ($params['save']=='1'){
			$data_meSave['met'] = 'save';
			$data_meSave['where'] = 'id = '.$params['meid'];
			$res_meSave = D('Menu')->menuSave($data_meSave);
			if (false===$res_meSave){
				$this->error('导航信息更新失败！');
			}else {
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新站点导航信息，MEID【'.$params['meid'].'】，OLD_MENAME【'.$params['old_name'].'】，NEW_MENAME【'.$params['menu_name'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);

				$this->success('站点导航信息更新成功！',U('Config/Menu/meList'));
			}
		}else if ($params['add']=='1'){
			$data_meSave['met'] = 'add';
			$data_meSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res_meSave = D('Menu')->menuSave($data_meSave);
			if (false===$res_meSave){
				$this->error('导航信息添加失败！');
			}else {
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加站点导航信息，MENAME【'.$params['menu_name'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);

				$this->success('站点导航信息添加成功！',U('Config/Menu/meList'));
			}
		}else{
			$this->display('menu_edit');
		}
	}
}
?>