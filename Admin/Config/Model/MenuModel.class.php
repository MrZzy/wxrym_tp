<?php
namespace Config\Model;
use Think\Model;

class MenuModel extends Model{
	public function readMenuList($where = '1 = 1'){
		$res = $this->table('wx_menu')
			->where($where)
			->order('m_order desc')
			->select();
		return $res;
	}
	public function menuSave($arr){
		if ($arr['met']=='stop' || $arr['met']=='start' || $arr['met']=='save'){
			$res = $this->table('wx_menu')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else if ($arr['met']=='add'){
			$res = $this->table('wx_menu')
				->data($arr['data'])
				->add();
			return $res;
		}else if ($arr['met']=='del'){
			
		}
	}
}
?>