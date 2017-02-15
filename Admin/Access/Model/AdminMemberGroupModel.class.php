<?php
namespace Access\Model;
use Think\Model;

class AdminMemberGroupModel extends Model{
	public function readAdminMemberGroupList($arr){
		$res = $this->table('wx_admin_member_group')
			->field($arr['params'])
			->where($arr['where'])
			->order($arr['order'])
			->select();
		return $res;
	}
	//群组成员数量加减
	public function adminMemberGroupMCountInc($grp_id){
		$res = $this->where('grp_id = '.$grp_id)
			->setInc('grp_member_count',1);
		return $res;
	}
	public function adminMemberGroupMCountDec($grp_id){
		$res = $this->where('grp_id = '.$grp_id)
			->setDec('grp_member_count',1);
		return $res;
	}
	
	public function adminMemberGroupSave($arr){
		if ($arr['grp_id']>0){
			$res = $this->table('wx_admin_member_group')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_admin_member_group')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function adminMemberGroupDel($grp_id){
		$res = $this->table('wx_admin_member_group')
			->where('grp_id = '.$grp_id)
			->delete();
		return $res;
	}
	
	public function readMemberGroupAccess($arr){
		$res = $this->table('wx_admin_menu')
			->field($arr['params'])
			->where($arr['where'])
			->order($arr['order'])
			->select();
		return $res;
	}
}
?>