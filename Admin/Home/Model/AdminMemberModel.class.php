<?php
namespace Home\Model;
use Think\Model;

class AdminMemberModel extends Model{
	//后台登录
	public function adminLogin($arr){
		//密码加密后前三位随机字符混淆
		$res = $this->field('am.*,amg.grp_name')
			->table('wx_admin_member as am')
			->join('wx_admin_member_group as amg on am.group_id = amg.grp_id')
			->where("am.member_name = '".$arr['uname']."' and RIGHT(member_pwd,LENGTH(member_pwd) - 3) = '".md5($arr['upwd'])."' and am.is_delete = 0")
			->select();
		return $res[0];
	}
	
	//登录信息更新
	public function updLoginInfo($arr){
		//保存数据
		$res = $this->table('wx_admin_member')
			->data($arr['data'])
			->where($arr['where'])
			->save();
		//更新登录次数
		$res2 = $this->table('wx_admin_member')
			->where($arr['where'])
			->setInc('login_count',1);
		// return $res;
	}
	
	//后台菜单及权限信息
	public function readAdminMenuAccess($arr){
		$res = $this->field('menu_id,menu_name,action,url,parent_id,order_num,is_menu')
			->table('wx_admin_menu')
			->where($arr['where'])
			->order('order_num desc')
			->select();
		return $res;
	}
	
	//取新闻、产品、招聘、管理员   总数 - 今日新增 - 昨日添加 - 本周 - 本月
	public function getWelcomInfoCount($arr){
		$res_count = M($arr['table'],$arr['pref'])->where($arr['where'])->count();
		return $res_count;
	}
}
?>