<?php
namespace Wxno\Model;
use Think\Model;

class WxnoCategoryModel extends Model{
	public function readCatList($where = 'is_delete = 0'){
		$res = $this->table('wx_wxno_category')
			->where($where)
			->order('cat_order desc')
			->select();
		return $res;
	}
	public function catSave($arr){
		if ($arr['cat_id']>0){
			$res = $this->table('wx_wxno_category')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_wxno_category')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function catDel($cat_id){
		$res = $this->table('wx_wxno_category')
			->where('cat_id = '.$cat_id)
			->delete();
		return $res;
	}
}
?>