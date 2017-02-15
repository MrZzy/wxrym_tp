<?php
namespace Banner\Model;
use Think\Model;

class BannerModel extends Model{
	public function readBannerList($arr){
		$res = $this->field($arr['params'])
			->where($arr['where'])
			->order('banner_order desc,add_time desc')
			->select();
		return $res;
	}
}
?>