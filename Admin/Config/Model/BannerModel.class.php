<?php
namespace Config\Model;
use Think\Model;

class BannerModel extends Model{
	public function readBannerList($arr){
		$res = $this->table('wx_banner')
			->field($arr['params'])
			->where($arr['where'])
			->order('banner_order desc,add_time desc')
			->select();
		return $res;
	}
	public function bannerSave($arr){
		if ($arr['met']=='stop' || $arr['met']=='start' || $arr['met']=='save'){
			$res = $this->table('wx_banner')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else if ($arr['met']=='del'){
			$res = $this->table('wx_banner')
				->where($arr['where'])
				->delete();
			return $res;
		}else if ($arr['met']=='add'){
			$res = $this->table('wx_banner')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
}
?>