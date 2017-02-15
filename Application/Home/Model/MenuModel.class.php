<?php
namespace Home\Model;
use Think\Model;

class MenuModel extends Model{
	public function menuList($arr){
		$res = $this->field($arr['params'])
			->where($arr['where'])
			->order($arr['order'])
			->limit($arr['limit_length'])
			->select();
		return $res;
	}
	
	public function menuInfo($menu_tag){
		$res = $this->where("menu_tag = '".$menu_tag."'")
			->select();
		return $res[0];
	}
}
?>