<?php
namespace Access\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
	public function mList(){
		$admin_member_list = D('AdminMember')->readAdminMemberList();
		$data_member_group = array(
			'params' => 'grp_id,grp_name',
			'where' => 'is_delete = 0',
			'order' => 'grp_id desc',
		);
		$this->member_group = D('AdminMemberGroup')->readAdminMemberGroupList($data_member_group);
		//禁用、启用
		$stop_url = 'Access/Index/mStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Access/Index/mDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}
		//权限更改
		$access_url = 'Access/Index/mAccess';
		$access_check_access = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$access_url."'")
			->select();
		if (count($access_check_access[0])<1){
			$this->assign('unabled_access',1);
		}
		
		$this->assign('admin_member_list',$admin_member_list['list']);
		$this->assign('row_count',$admin_member_list['row_count']);
		$this->assign('page',$admin_member_list['page']);
		$this->display('admin_list');
	}
	public function mStop(){
		if (I('post.mid')=='1'){
			$this->error('此账户不可禁用！');
		}else{
			$data_stop = array(
				'mid' => I('post.mid'),
				'met' => I('post.met'),
			);
			$res = D('AdminMember')->adminMemberStop($data_stop);
			if (false===$res){
				// $this->error('操作失败');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => ($data_stop['met']=='stop') ? '禁用管理员账户，UID【'.I('post.mid').'】，UNAME【'.I('post.mname').'】' : '启用管理员账户，UID【'.I('post.mid').'】，UNAME【'.I('post.mname').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function mDel(){
		if (I('post.mid')=='1'){
			$this->error('此账户不可删除！');
		}else{
			$res = D('AdminMember')->adminMemberDel(I('post.mid'));
			if (false===$res){
				// $this->error('删除账户失败！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '删除管理员账户，UID【'.I('post.mid').'】，UNAME【'.I('post.mname').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
			}
		}
	}
	public function mInfo(){
		$params = I('get.');
		if ($params['mid']>0){
			$this->mid = $params['mid'];
			$this->member_info = D('AdminMember')->adminMemberDetail($params['mid']);
		}
		if ($params['met']=='access'){
			//所属群组信息
			$data_member_group = array(
				'params' => 'grp_name,grp_menu_access',
				'where' => 'grp_id = '.$this->member_info['group_id'],
			);
			$member_group = D('AdminMemberGroup')->readAdminMemberGroupList($data_member_group);
			//群组权限
			$data_group_access = array(
				'params' => 'menu_id,menu_name,parent_id,is_delete',
				'where' => 'menu_id in ('.$member_group[0]['grp_menu_access'].')',
				'order' => 'order_num desc',
			);
			$group_access = D('AdminMemberGroup')->readMemberGroupAccess($data_group_access);
			
			$admin_access = explode(',',$this->member_info['menu_access']);
			$grp_access_parent = array();
			$grp_access_child = array();
			foreach ($group_access as $key=>$val){
				if (in_array($val['menu_id'],$admin_access)){
					$group_access[$key]['is_checked'] = 'checked';
				}
			}
			foreach ($group_access as $key=>$val){
				if ($val['parent_id']=='0'){
					$grp_access_parent[] = $val;
				}else{
					$grp_access_child[] = $val;
				}
			}
			$this->grp_access_child = $grp_access_child;
			$this->grp_access_parent = $grp_access_parent;
			
			$this->display('admin_access');
		}else{
			$data_member_group = array(
				'params' => 'grp_id,grp_name',
				'where' => 'is_delete = 0',
				'order' => 'grp_id desc',
			);
			$this->member_group = D('AdminMemberGroup')->readAdminMemberGroupList($data_member_group);
						
			$this->display('admin_edit');
		}
	}
	public function mEdit(){
		$params = I('post.');
		$data_member_group = array(
			'params' => 'grp_id,grp_name',
			'where' => 'is_delete = 0',
			'order' => 'grp_id desc',
		);
		$this->member_group = D('AdminMemberGroup')->readAdminMemberGroupList($data_member_group);
		if ($params['member_pwd']!=''){
			$member_pwd = randomkeys(3).MD5($params['member_pwd']);
		}
		$data_edit = array(
			'data' => array(
				'group_id' => $params['grp_id'],
				'member_name' => $params['member_name'],
				// 'member_pwd' => $member_pwd,
				'real_name' => $params['real_name'],
				'gender' => $params['gender'],
				'phone' => $params['phone'],
				'qq' => $params['qq'],
				'email' => $params['email'],
				'address' => $params['address'],
				'desc' => $params['desc'],
				// 'add_time' => date('Y-m-d H:i:s',time()),
			),
		);
		if ($params['save']=='1' && $params['mid']>0){
			$data_edit['met'] = 'save';
			$data_edit['where'] = 'id = '.$params['mid'];
			if ($params['member_pwd']!=''){
				$data_edit['data']['member_pwd'] = $member_pwd;
			}
			$old_grpId = $params['old_grpId'];
			$member_upd = D('AdminMember')->adminMemberSave($data_edit);
			if (false===$member_upd){
				$this->error('管理员信息更新失败，请稍后重试！');
			}else{
				if ($params['grp_id']!=$old_grpId){
					$res_old_grp_mCount = D('AdminMemberGroup')->adminMemberGroupMCountDec($old_grpId);
					if ($res_old_grp_mCount)
						$res_new_grp_mCount = D('AdminMemberGroup')->adminMemberGroupMCountInc($params['grp_id']);
				}
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新管理员账户信息，UID【'.I('post.mid').'】，OLD_NAME【'.I('post.old_name').'】，NEW_UNAME【'.I('post.member_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('管理员信息更新成功！',U('Access/Index/mList'));
			}
		}else if ($params['add']=='1'){
			$data_edit['met'] = 'add';
			$data_edit['data']['member_pwd'] = $member_pwd;
			$data_edit['data']['add_time'] = date('Y-m-d H:i:s',time());
			$member_id = D('AdminMember')->adminMemberSave($data_edit);
			if ($member_id > 0){
				$res_grp_mCount = D('AdminMemberGroup')->adminMemberGroupMCountInc($params['grp_id']);
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加管理员账户，UID【'.$member_id.'】，UNAME【'.I('post.member_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('管理员添加成功，请刷新页面查看！',U('Access/Index/mList'));
			}else{
				$this->error('管理员添加失败，请稍后重试！');
			}
		}else{
			$this->error('参数错误');
		}
	}
	public function mAccess(){
		$params = I('post.');
		$data_access = array(
			'met' => 'save',
			'data' => array(
				'menu_access' => implode(',',$params['admin_access']),
			),
			'where' => 'id = '.$params['mid'],
		);
		$access_upd = D('AdminMember')->adminMemberSave($data_access);
		if (false===$access_upd){
			$this->error('管理员权限信息更新失败，请稍后重试！');
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '更新管理员权限信息，UID【'.I('post.mid').'】，UNAME【'.I('post.mname').'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			// $this->success('管理员权限信息更新成功！',U('Access/Index/mList'));
			$this->success('管理员权限信息更新成功！');
		}
	}
	public function selfChgPwd(){
		$params = I('request.');
		if ($params['chg_pwd']=='1'){
			$msg_chg = array(
				'code' => '0',
				'msg' => '密码更改！',
			);
			$check_old = D('AdminMember')->chechOldPwd($params['old_pwd']);
			if ($check_old != false){
				$res = D('AdminMember')->saveNewPwd($params['new_pwd']);
				if (false===$res){
					$msg_chg = array(
						'code' => '-2',
						'msg' => '系统异常，密码更改失败！',
					);
				}else {
					$msg_chg = array(
						'code' => '1',
						'msg' =>'密码更改成功！',
					);
				}
			}else {
				$msg_chg = array(
					'code' => '-1',
					'msg' => '原密码输入错误！',
				);
			}
			echo json_encode($msg_chg);
		}else{
			$this->display('self_chgPwd');
		}
	}
}
?>