<?php
namespace Access\Model;
use Think\Model;

class AdminMemberModel extends Model{
	
	public function readAdminMemberList($where = ' 1 = 1 '){
		$params = I('request.');
		$where = $where;
		$cfg_page_size = C('BACK_PAGE_SIZE');
		$this_page = (empty($params['p'])||($params['p']<1)) ? 1 : $params['p'];
		$page_size = (empty($cfg_page_size)) ? 20 : $cfg_page_size;
		$info = array(
			'p' => $params['p']
		);
		if ($params['date_end']!=''){
			$info['date_end'] = $params['date_end'];
		}
		if ($params['date_start']!=''){
			$info['date_start'] = $params['date_start'];
			$where .= " and t_amb.add_time between '".$params['date_start']." 00:00:00' ";
			if ($params['date_end']!=''){
				$where .= "and '".$params['date_end']." 23:59:59'";
			}else{
				$where .= "and '".date('Y-m-d H:i:s',time())."'";
			}
		}
		if ($params['srch_text']!=''){
			$info['srch_text'] = $params['srch_text'];
			$where .= " and t_amb.member_name like '%".$params['srch_text']."%'";
		}
		if ($params['grp_id']>0){
			$info['grp_id'] = $params['grp_id'];
			$where .= " and t_amb.group_id = ".$params['grp_id'];
		}
		$row_count = $this->table('wx_admin_member as t_amb')
			->join('wx_admin_member_group as t_ambg on t_amb.group_id = t_ambg.grp_id')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_admin_member as t_amb')
			->field('t_amb.*,t_ambg.grp_name')
			->join('wx_admin_member_group as t_ambg on t_amb.group_id = t_ambg.grp_id')
			->where($where)
			->order('add_time asc')
			->page($this_page,$page_size)
			->select();
		
		//分页
		unset($this_page);
		foreach ($info as $key=>$val){//拼接 分页条件
			if($val<>""){
				$Pagearr[$key]=$val;
			}
		}
		$Page = new \Think\Page($row_count,$page_size);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->parameter = $Pagearr;
		$show = $Page->show();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性 */
		return array("row_count"=>$row_count,"page"=>$show,"list"=>$row_list);
	}
	public function adminMemberStop($arr){
		$is_delete = ($arr['met']=='stop') ? 1 : 0;
		$res = $this->table('wx_admin_member')
			->data(array('is_delete'=>$is_delete))
			->where('id = '.$arr['mid'])
			->save();
		return $res;
	}
	public function adminMemberDel($mid){
		$res = $this->table('wx_admin_member')
			->where('id = '.$mid)
			->delete();
		return $res;
	}
	public function adminMemberDetail($mid){
		$res = $this->table('wx_admin_member as t_amb')
			->field('t_amb.*,t_ambg.grp_name')
			->join('wx_admin_member_group as t_ambg on t_amb.group_id = t_ambg.grp_id')
			->where('t_amb.id = '.$mid)
			->select();
		return $res[0];
	}
	public function adminMemberSave($arr){
		if ($arr['met']=='add'){
			$res = $this->table('wx_admin_member')
				->data($arr['data'])
				->add();
			return $res;
		}else if ($arr['met']=='save'){
			$res = $this->table('wx_admin_member')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			return false;
		}
	}
	public function chechOldPwd($old_pwd){
		$uid = session('admin.id');
		$res = $this->table('wx_admin_member')
			->where("id = ".$uid." and RIGHT(member_pwd,LENGTH(member_pwd) - 3) = '".md5($old_pwd)."'")
			->select();
		return $res[0];
	}
	public function saveNewPwd($new_pwd){
		$uid = session('admin.id');
		$res = $this->table('wx_admin_member')
			->data(array('member_pwd' => randomkeys(3).MD5($new_pwd)))
			->where('id = '.$uid)
			->save();
		return $res;
	}
	
}
?>