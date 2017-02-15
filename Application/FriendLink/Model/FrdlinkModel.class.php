<?php
namespace FriendLink\Model;
use Think\Model;

class FrdlinkModel extends Model{
	public function readfrdLinkList($arr){
		$res = $this->field($arr['params'])
			->where($arr['where'])
			->order('f_order desc,add_time asc')
			->select();
		return $res;
	}
}
?>