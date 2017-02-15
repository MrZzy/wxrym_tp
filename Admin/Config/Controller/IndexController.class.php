<?php
namespace Config\Controller;
use Think\Controller;

class IndexController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function cfgList(){
		$this->cfgInfo_base = D('Config')->readCfgList(array('where'=>"type = 1 and value_type <> 'img'"));	//基本设置
		$this->cfgInfo_security = D('Config')->readCfgList(array('where'=>"type = 2 and value_type <> 'img'"));	//安全设置
		$this->cfgInfo_other = D('Config')->readCfgList(array('where'=>"type = 3 and value_type <> 'img'"));	//其他设置
		$this->display('cfg_info');
	}
	public function cfgSave(){
		$params = I('post.');
		$arr_tmp = array();
		$res_tmp = 0;
		foreach ($params as $key=>$val){
			if ($key != 'upd_type'){
				$arr_tmp['data'] = array(
					'cfg_value' => $val,
				);
				$arr_tmp['where'] = "cfg_tag = '".$key."'";
				$res = D('Config')->cfgSave($arr_tmp);
				if ($res===false){
					$error_cfg_info = D('Config')->readCfgList(array('where'=>"cfg_tag = '".$key."'"));
					$this->error('《'.$error_cfg_info[0]['cfg_name'].'》及其后设置保存失败，请检查后重试！',U('Config/Index/cfgList'));
					break;
				}
				$res_int = ($res===false) ? 1 : 0;
				$res_tmp += $res_int;
			}
		}
		if ($res_tmp==0){
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '更改站点设置信息 - 【'.$params['upd_type'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);

			$this->success('配置保存成功！',U('Config/Index/cfgList'));
		}
	}
	public function clearCache($dir = "./Runtime"){
		//清空缓存 - Runtime目录下文件
		if ($_POST['c_cache']=='1'){
			//先删除目录下的文件
			$dir = $dir;
			$dh = opendir($dir);
			while ($file = readdir($dh)) {
				if($file!="." && $file!="..") {
					$fullpath = $dir."/".$file;
					if(!is_dir($fullpath)) {
						unlink($fullpath);
					}else {
						$this->clearCache($fullpath);
					}
				}
			}
			closedir($dh);
			//删除当前文件夹
			rmdir($dir);
		}
	}

}
?>