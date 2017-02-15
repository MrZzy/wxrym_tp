<?php
namespace Access\Controller;
use Think\Controller;

class AdminLogController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function logList(){
		$log_list = D('AdminLog')->readAdminLogList();
		
		$this->assign('row_count',$log_list['row_count']);
		$this->assign('log_list',$log_list['list']);
		$this->assign('page',$log_list['page']);

		$this->display('log_list');
	}

}
?>