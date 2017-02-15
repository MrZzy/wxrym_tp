<?php
namespace Access\Controller;
use Think\Controller;

class MemberGroupController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
	public function gList(){
		$params = I('post.');
		$data_gList = array();
		if ($params['srch_text']!='')
			$data_gList['where'] = "grp_name like '%".$params['srch_text']."%'";
		$this->member_group_list = D('AdminMemberGroup')->readAdminMemberGroupList($data_gList);
		
		//禁用、启用
		$stop_url = 'Access/MemberGroup/gStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Access/MemberGroup/gDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}
		//权限更改
		$access_url = 'Access/MemberGroup/gAccess';
		$access_check_access = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$access_url."'")
			->select();
		if (count($access_check_access[0])<1){
			$this->assign('unabled_access',1);
		}
		
		$this->display('grp_list');
	}
	public function gEdit(){
		$params = I('post.');
		$data_gEdit = array(
			'data' => array(
				'grp_name' => $params['grp_name'],
				// 'add_time' => date('Y-m-d H:i:s',time()),
				// 'desc' => $params['desc'],
			),
		);
		if ($params['desc']!=''){
			$data_gEdit['data']['desc'] = $params['desc'];
		}
		if ($params['grp_id']>0){
			$data_gEdit['grp_id'] = $params['grp_id'];
			$data_gEdit['where'] = 'grp_id = '.$params['grp_id'];
			$res = D('AdminMemberGroup')->adminMemberGroupSave($data_gEdit);
			if (false===$res){
				$this->error('群组信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新管理员群组信息，GID【'.I('post.grp_id').'】，OLD_GNAME【'.I('post.old_name').'】，NEW_GNAME【'.I('post.grp_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('群组信息更改成功！',U('Access/MemberGroup/gList'));
			}
		}else{
			$data_gEdit['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res = D('AdminMemberGroup')->adminMemberGroupSave($data_gEdit);
			if (false===$res){
				$this->error('群组信息添加失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加管理员群组信息，GID【'.$res.'】，GNAME【'.I('post.grp_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('群组信息添加成功！',U('Access/MemberGroup/gList'));
			}
		}
	}
	public function gStop(){
		if (I('post.mid')=='1'){
			$this->error('本组不可禁用！');
		}else{
			$params = I('post.');
			$data_gStop = array(
				'grp_id' => $params['gid'],
				'data' => array(
					'is_delete' => ($params['met']=='stop') ? 1 : 0,
				),
				'where' => 'grp_id = '.$params['gid'],
			);
			$res = D('AdminMemberGroup')->adminMemberGroupSave($data_gStop);
			if (false===$res){
				$this->error('操作失败');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => ($params['met']=='stop') ? '禁用管理员群组，GID【'.I('post.gid').'】，GNAME【'.I('post.gname').'】' : '启用管理员群组，GID【'.I('post.gid').'】，GNAME【'.I('post.gname').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function gDel(){
		if (I('post.mid')=='1'){
			$this->error('本组不可删除！');
		}else{
			$params = I('request.');
			$res = D('AdminMemberGroup')->adminMemberGroupDel($params['gid']);
			if (false===$res){
				$this->error('操作失败');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '删除管理员群组，GID【'.I('post.gid').'】，GNAME【'.I('post.gname').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function gAccess(){
		$params  = I('post.');
		if ($params['save']=='1'){
			$data_gAccess = array(
				'grp_id' => $params['gid'],
				'data' => array(
					'grp_menu_access' => implode(',',$params['grp_access']),
				),
				'where' => 'grp_id = '.$params['gid'],
			);
			$access_upd = D('AdminMemberGroup')->adminMemberGroupSave($data_gAccess);
			if (false===$access_upd){
				$this->error('管理员群组权限信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新管理员群组权限信息，GID【'.I('post.gid').'】，GNAME【'.I('post.gname').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('管理员群组权限信息更新成功！');
			}
		}else{
			$grp_id = I('get.gid');
			//群组信息
			$grp_info = D('AdminMemberGroup')->readAdminMemberGroupList(array('where'=>'grp_id = '.$grp_id));
			$this->grp_info = $grp_info[0];
			//后台权限
			$data_menu_access = array(
				'params' => 'menu_id,menu_name,parent_id,is_delete',
				'where' => '1 = 1',
				'order' => 'order_num desc',
			);
			$menu_access = D('AdminMemberGroup')->readMemberGroupAccess($data_menu_access);

			$group_access = explode(',',$this->grp_info['grp_menu_access']);
			$menu_access_parent = array();
			$menu_access_child = array();
			foreach ($menu_access as $key=>$val){
				if (in_array($val['menu_id'],$group_access)){
					$menu_access[$key]['is_checked'] = 'checked';
				}
			}
			foreach ($menu_access as $key=>$val){
				if ($val['parent_id']=='0'){
					$menu_access_parent[] = $val;
				}else{
					$menu_access_child[] = $val;
				}
			}
			$this->menu_access_child = $menu_access_child;
			$this->menu_access_parent = $menu_access_parent;

			$this->display('grp_access');
		}
	}
	public function gMembers(){
		$admin_member_list = D('AdminMember')->readAdminMemberList('t_ambg.grp_id = '.I('get.gid'));

		$this->assign('admin_member_list',$admin_member_list['list']);
		$this->assign('row_count',$admin_member_list['row_count']);
		$this->assign('page',$admin_member_list['page']);
		$this->display('grp_members');
	}
}
?>