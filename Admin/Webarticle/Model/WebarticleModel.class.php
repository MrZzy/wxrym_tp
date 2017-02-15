<?php
namespace Webarticle\Model;
use Think\Model;

class WebarticleModel extends Model{
	public function readArticleList($arr){
		$res = $this->table('wx_webarticle')
			->field($arr['params'])
			->where($arr['where'])
			->select();
		return $res;
	}
	public function articleSave($arr){
		if ($arr['art_id']>0){
			$res = $this->table('wx_webarticle')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$art_id = $this->table('wx_webarticle')
				->data($arr['data'])
				->add();
			return $art_id;
		}
	}
	public function articleDel($art_id){
		$res = $this->table('wx_webarticle')
			->where('art_id = '.$art_id)
			->delete();
		// $res = $this->table('wx_webarticle')
			// ->data(array('is_delete'=>1))
			// ->where('art_id = '.$art_id)
			// ->save();
		return $res;
	}
}
?>