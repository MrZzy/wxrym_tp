<?php
namespace Operation\Controller;
use Think\Controller;
class FrdlinkController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
	public function linkList(){
		$link_list = D('Frdlink')->readLinkList();
		//禁用、启用
		$stop_url = 'Operation/Frdlink/linkStop';
		$access_check_stop = D('Frdlink')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Operation/Frdlink/linkDel';
		$access_check_del = D('Frdlink')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}
		
		$this->assign('link_list',$link_list['list']);
		$this->assign('row_count',$link_list['row_count']);
		$this->assign('page',$link_list['page']);
		$this->display('link_list');
	}
	public function linkStop(){
		$data_stop = array(
			'fid' => I('post.fid'),
			'met' => I('post.met'),
		);
		$res = D('Frdlink')->linkStop($data_stop);
		if (false===$res){
			// $this->error('操作失败');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => ($data_stop['met']=='stop') ? '禁用友情链接，FID【'.I('post.fid').'】，FNAME【'.I('post.fname').'】' : '启用友情链接，FID【'.I('post.fid').'】，FNAME【'.I('post.fname').'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功'));
		}
	}
	public function linkDel(){
		$res = D('Frdlink')->linkDel(I('post.fid'));
		if (false===$res){
			// $this->error('删除账户失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除友情链接，FID【'.I('post.fid').'】，FNAME【'.I('post.fname').'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功'));
		}
	}
	public function linkEdit(){
		$params = I('post.');
		$data_edit = array(
			'data' => array(
				'f_name' => $params['f_name'],
				'f_link' => $params['f_link'],
				'f_order' => $params['f_order'],
				'is_delete' => $params['is_delete'],
				'f_desc' => $params['f_desc'],
				// 'add_time' => date('Y-m-d H:i:s',time()),
			),
		);
		if ($params['save']=='1' && $params['fid']>0){
			$data_edit['met'] = 'save';
			$data_edit['fid'] = $params['fid'];
			$data_edit['where'] = 'fid = '.$params['fid'];
			$link_upd = D('Frdlink')->linkSave($data_edit);
			if (false===$link_upd){
				$this->error('友情链接信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新友情链接账户信息，FID【'.I('post.fid').'】，OLD_FAME【'.I('post.old_name').'】，NEW_FNAME【'.I('post.f_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('友情链接信息更新成功！',U('Operation/Frdlink/linkList'));
			}
		}else if ($params['add']=='1'){
			$data_edit['met'] = 'add';
			$data_edit['data']['add_time'] = date('Y-m-d H:i:s',time());
			$link_id = D('Frdlink')->linkSave($data_edit);
			if ($link_id > 0){
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加友情链接，FID【'.$link_id.'】，FNAME【'.I('post.f_name').'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('友情链接添加成功，请刷新页面查看！',U('Operation/Frdlink/linkList'));
			}else{
				$this->error('友情链接添加失败，请稍后重试！');
			}
		}else{
			if (I('request.fid')>0){
				$this->fid = I('request.fid');
				$link_info = D('Frdlink')->readLinkList('fid = '.I('request.fid'));
				$this->link_info = $link_info['list'][0];
			}
			$this->display('link_edit');
		}
	}
}
?>