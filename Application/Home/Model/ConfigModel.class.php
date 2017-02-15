<?php
namespace Home\Model;
use Think\Model;

class ConfigModel extends Model{
	public function configInfo($arr){
		$res = $this->field($arr['params'])
			->where($arr['where'])
			->select();
		return $res;
	}
}
?>