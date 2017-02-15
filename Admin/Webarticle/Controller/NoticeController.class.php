<?php
namespace Webarticle\Controller;
use Think\Controller;

class NoticeController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function ntList(){
		$noticeList = D('Webnotice')->readNoticeList();
		
		$this->assign('row_count',$noticeList['row_count']);
		$this->assign('notice_list',$noticeList['list']);
		$this->assign('page',$noticeList['page']);
		
		//禁用、启用
		$stop_url = 'Webarticle/Notice/ntStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Webarticle/Notice/ntDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}
		
		$this->display('notice_list');
	}
	public function ntEdit(){
		$params = I('request.');
		if ($params['nid']>0){
			$this->ntInfo = D('Webnotice')->readNoticeInfo($params['nid']);
		}
		$data_ntSave = array(
			'data' => array(
				'n_title' => $params['n_title'],
				'n_content' => $_POST['editorValue'],
			),
		);
		if ($params['save']=='1'){
			$data_ntSave['nid'] = $params['nid'];
			$data_ntSave['where'] = 'nid = '.$params['nid'];
			$res_ntSave = D('Webnotice')->noticeSave($data_ntSave);
			if (false===$res_artSave){
				$this->error('网站文章信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新公告信息，NID【'.$params['nid'].'】，OLD_TITLE【'.$params['old_title'].'】，NEW_TITLE【'.$params['n_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('网站公告信息更改成功！',U('Webarticle/Notice/ntList'));
			}
		}else if ($params['add']=='1'){
			$data_ntSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$data_ntSave['data']['n_author'] = session('admin.member_name');
			$res_ntSave = D('Webnotice')->noticeSave($data_ntSave);
			if (false===$res_ntSave){
				$this->error('网站公告信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新网站公告信息，NID【'.$params['n_id'].'】，OLD_TITLE【'.$params['old_title'].'】，NEW_TITLE【'.$params['n_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('网站公告信息更新成功！',U('Article/Service/serList'));
			}
		}else{
			$this->display('notice_edit');
		}
	}
	public function ntStop(){
		$data_stop = array(
			'nid' => I('post.nid'),
			'met' => I('post.met'),
		);
		$res = D('Webnotice')->noticeStop($data_stop);
		if (false===$res){
			// $this->error('操作失败');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => ($data_stop['met']=='stop') ? '禁用公告信息，NID【'.I('post.nid').'】，NTITLE【'.I('post.n_title').'】' : '启用公告信息，NID【'.I('post.nid').'】，NTITLE【'.I('post.n_title').'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function ntDel(){
		$res = D('Webnotice')->noticeDel(I('post.nid'));
		if (false===$res){
			// $this->error('删除账户失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除公告信息，NID【'.I('post.nid').'】，NTITLE【'.I('post.n_title').'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}

}
?>