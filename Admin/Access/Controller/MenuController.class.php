<?php
namespace Access\Controller;
use Think\Controller;

class MenuController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function meList(){
		$data_menu_parent = array(
			'where' => 'parent_id = 0',
			'order' => 'order_num desc',
		);
		$this->menu_parent = D('AdminMemberGroup')->readMemberGroupAccess($data_menu_parent);
		$data_menu_child = array(
			'where' => 'parent_id > 0',
			'order' => 'order_num desc',
		);
		$this->menu_child = D('AdminMemberGroup')->readMemberGroupAccess($data_menu_child);
		
		//禁用、启用
		$stop_url = 'Access/Menu/meStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Access/Menu/meDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}
		
		$this->display('menu_list');
	}
	public function meStop(){
		$params = I('post.');
		//判断是否为菜单
		$meInfo = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'menu_id = '.$params['meid']));
		if ($meInfo[0]['is_menu']!='1'){
			$this->error('权限无法禁用！');
		}else{
			$is_delete = ($params['met']=='stop') ? 1 : 0;
			$data_meStop = array(
				'me_id' => $params['meid'],
				'data' => array(
					'is_delete' => $is_delete,
				),
				'where' => 'menu_id = '.$params['meid'],
			);
			$res_stop = D('AdminMenu')->adminMenuSave($data_meStop);
			if (false===$res_stop){
				$this->error('操作失败！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => ($params['met']=='stop') ? '禁用权限菜单，MEID【'.I('post.meid').'】，MENAME【'.I('post.mename').'】' : '启用权限菜单，MEID【'.I('post.meid').'】，MENAME【'.I('post.mename').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function meDel(){
		$params = I('post.');
		//判断是否为父级菜单
		$meInfo = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'menu_id = '.$params['meid']));
		if ($meInfo[0]['parent_id']=='0'){
			$this->error('无权操作！');
		}else{
			$res_del = D('AdminMenu')->adminMenuDel($params['meid']);
			if (false===$res_del){
				$this->error('操作失败！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '删除权限菜单，MEID【'.I('post.meid').'】，MENAME【'.I('post.mename').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function meEdit(){
		$params = I('request.');
		//取父级菜单
		$this->parent_menu = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'parent_id = 0 and is_delete = 0'));
		if ($params['meid']>0){
			//取该权限菜单信息
			$meInfo = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'menu_id = '.$params['meid']));
			$this->meInfo = $meInfo[0];
		}
		$data_meEdit = array(
			'data' => array(
				'menu_name' => $params['menu_name'],
				'action' => $params['action'],
				'url' => $params['url'],
				'parent_id' => $params['parent_id'],
				'order_num' => $params['order_num'],
				'is_menu' => $params['is_menu'],
				'is_delete' => $params['is_delete'],
			),
		);
		if ($params['save']=='1'){
			//保存
			$data_meEdit['me_id'] = I('post.meid');
			$data_meEdit['where'] = 'menu_id = '.$params['meid'];
			$res_meEdit = D('AdminMenu')->adminMenuSave($data_meEdit);
			$old_parent_info = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'menu_id = '.I('post.old_parent')));
			$new_parent_info = D('AdminMemberGroup')->readMemberGroupAccess(array('where'=>'menu_id = '.I('post.parent_id')));
			if (false===$res_meEdit){
				$this->error('权限菜单信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新权限菜单信息，MEID【'.I('post.meid').'】，OLD_MENAME【'.I('post.old_name').'】，NEW_MENAME【'.I('post.menu_name').'】，OLD_PARENT【'.(!empty($old_parent_info[0]['menu_name']) ? $old_parent_info[0]['menu_name'] : '顶级菜单').'】，NEW_PARENT【'.(!empty($new_parent_info[0]['menu_name']) ? $new_parent_info[0]['menu_name'] : '顶级菜单').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('权限菜单信息更新成功，请刷新页面查看！',U('Access/Menu/meList'));
			}
		}else if ($params['add']=='1'){
			//添加
			$res_meEdit = D('AdminMenu')->adminMenuSave($data_meEdit);
			if ($res_meEdit>0){
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加权限菜单，MEID【'.$res_meEdit.'】，MENAME【'.I('post.menu_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('权限菜单添加成功，请刷新页面查看！',U('Access/Menu/meList'));
			}else{
				$this->error('权限菜单添加失败，请稍后重试！');
			}
		}else{
			$this->display('menu_edit');
		}
	}
}