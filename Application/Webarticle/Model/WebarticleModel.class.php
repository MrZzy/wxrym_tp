<?php
namespace Webarticle\Model;
use Think\Model;

class WebarticleModel extends Model{
	public function readWebartInfo($waid){
		$res = $this->table('wx_webarticle')
			->where('art_id = '.$waid)
			->find();
		return $res;
	}
}
?>