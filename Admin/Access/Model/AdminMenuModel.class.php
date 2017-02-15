<?php
namespace Access\Model;
use Think\Model;

class AdminMenuModel extends Model{
	public function adminMenuSave($arr){
		if ($arr['me_id']>0){
			$res = $this->table('wx_admin_menu')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_admin_menu')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function adminMenuDel($meid){
		$res = $this->table('wx_admin_menu')
			->where('menu_id = '.$meid)
			->delete();
		return $res;
	}
}