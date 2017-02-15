<?php
namespace Wxno\Controller;
use Think\Controller;

class WxnoCategoryController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function catList(){
		$this->catList = D('WxnoCategory')->readCatList('1 = 1');

		//禁用、启用
		$stop_url = 'Wxno/WxnoCategory/catStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//编辑
		$edit_url = 'Wxno/WxnoCategory/catEdit';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$edit_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_edit',1);
		}
		//删除
		$del_url = 'Wxno/WxnoCategory/catDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}

		$this->display('cat_list');
	}
	public function catEdit(){
		$params = I('post.');
		if ($params['save']=='1' || $params['add']=='1'){
			$data_catEdit = array(
				'data' => array(
					'cat_name' => $params['cat_name'],
					'cat_tag' => $params['cat_tag'],
					'cat_order' => $params['cat_order'],
					'is_delete' => $params['is_delete'],
					'seo_title' => $params['seo_title'],
					'seo_keywords' => $params['seo_keywords'],
					'seo_desc' => $params['seo_desc'],
				),
			);
			if ($params['cat_id']>0){
				$data_catEdit['cat_id'] = $params['cat_id'];
				$data_catEdit['where'] = 'cat_id = '.$params['cat_id'];
				$res_typeSave = D('WxnoCategory')->catSave($data_catEdit);
				if (false===$res_typeSave){
					$this->error('分类信息更新失败，请稍后重试！');
				}else{
					$data_log = array(
						'log_type' => 2,	//1登录;2操作
						'user_name' => session('admin.member_name'),
						'action' => '',
						'log_desc' => '更新公众号分类信息，TYPEID【'.$params['cat_id'].'】，OLD_TYPENAME【'.$params['old_name'].'】，NEW_TYPENAME【'.$params['typename'].'】',
						'ip' => GetIP(),
						'add_time' => date('Y-m-d H:i:s',time()),
					);
					$this->Init->logRec($data_log);
					$this->success('公众号分类信息更新成功！',U('catList'));
				}
			}else{
				$data_catEdit['data']['add_time'] = date('Y-m-d H:i:s',time());
				$res_typeAdd = D('WxnoCategory')->catSave($data_catEdit);
				if ($res_typeAdd>0){
					$data_log = array(
						'log_type' => 2,	//1登录;2操作
						'user_name' => session('admin.member_name'),
						'action' => '',
						'log_desc' => '添加公众号分类信息，TYPEID【'.$res_typeAdd.'】，TYPENAME【'.$params['typename'].'】',
						'ip' => GetIP(),
						'add_time' => date('Y-m-d H:i:s',time()),
					);
					$this->Init->logRec($data_log);
					$this->success('公众号分类添加成功！');
				}else{
					$this->error('分类添加失败，请稍后重试！');
				}
			}
		}else{
			$par = I('request.');
			if ($par['cid']>0)
				$this->catInfo = M('wxno_category','wx_')->where('cat_id = '.$par['cid'])->find();
			$this->display('cat_edit');
		}
	}
	public function catStop(){
		$params = I('post.');
		$data_catStop = array(
			'met' => $params['met'],
			'cat_id' => $params['cat_id'],
			'data' => array(
				'is_delete' => ($params['met']=='stop') ? 1 : 0,
			),
			'where' => 'cat_id = '.$params['cat_id'],
		);
		$res = D('WxnoCategory')->catSave($data_catStop);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => ($params['met']=='stop') ? '禁用公众号分类信息，CATID【'.$params['cat_id'].'】，CATNAME【'.$params['cat_name'].'】' : '启用公众号分类信息，CATID【'.$params['cat_id'].'】，CATNAME【'.$params['cat_name'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function catDel(){
		$params = I('post.');
		$res_typeDel = D('WxnoCategory')->catDel($params['cat_id']);
		if (false===$res_typeDel){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除公众号分类信息，CATID【'.$params['cat_id'].'】，CATNAME【'.$params['cat_name'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}

}
?>