<?php
namespace Config\Model;
use Think\Model;

class ConfigModel extends Model{
	public function readCfgList($arr = null){
		$res = $this->table('wx_config')
			->where($arr['where'])
			->select();
		return $res;
	}
	public function cfgSave($arr){
		$res = $this->table('wx_config')
			->data($arr['data'])
			->where($arr['where'])
			->save();
		return $res;
	}
}
?>