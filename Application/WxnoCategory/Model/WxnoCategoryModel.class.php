<?php
namespace WxnoCategory\Model;
use Think\Model;

class WxnoCategoryModel extends Model{
	public function readCatList($arr){
		$res = $this->field($arr['params'])
			->where($arr['where'])
			->order('cat_order desc,add_time desc')
			->select();
		return $res;
	}
}
?>