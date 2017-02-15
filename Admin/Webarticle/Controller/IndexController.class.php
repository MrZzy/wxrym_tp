<?php
namespace Webarticle\Controller;
use Think\Controller;

class IndexController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function artList(){
		$this->artList = D('Webarticle')->readArticleList(null);

		//删除
		$del_url = 'Webarticle/Index/artDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}

		$this->display('art_list');
	}
	public function artEdit(){
		$params = I('request.');
		if ($params['art_id']>0){
			$artInfo = D('Webarticle')->readArticleList(array('where'=>'art_id = '.$params['art_id']));
			$this->artInfo = $artInfo[0];
		}
		$data_artSave = array(
			'data' => array(
				'art_title' => $params['art_title'],
				'art_desc' => $params['art_desc'],
				'seo_title' => $params['seo_title'],
				'seo_keywords' => $params['seo_keywords'],
				'seo_desc' => $params['seo_desc'],
				'art_content' => $_POST['editorValue'],
				'upd_time' => date('Y-m-d H:i:s',time()),
			),
		);
		if ($params['save']=='1'){
			$data_artSave['art_id'] = $params['art_id'];
			$data_artSave['where'] = 'art_id = '.$params['art_id'];
			$res_artSave = D('Webarticle')->articleSave($data_artSave);
			if (false===$res_artSave){
				$this->error('网站文章信息更新失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更新文章信息，AID【'.$params['art_id'].'】，OLD_TITLE【'.$params['old_title'].'】，NEW_TITLE【'.$params['art_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('网站文章信息更改成功！',U('Webarticle/Index/artList'));
			}
		}else if ($params['add']=='1'){
			$data_artSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res_artAdd = D('Webarticle')->articleSave($data_artSave);
			if (false===$res_artAdd){
				$this->error('网站文章添加失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加文章信息，AID【'.$res_artAdd.'】，TITLE【'.$params['art_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('网站文章添加成功！',U('Webarticle/Index/artList'));
			}
		}else{
			$this->display('art_edit');
		}
	}
	public function artDel(){
		$params = I('post.');
		$res_artDel = D('Webarticle')->articleDel($params['art_id']);
		if (false===$res_artDel){
			// $this->error('文章删除失败，请稍后重试！');
			echo json_encode(array('code'=>-1,'msg'=>'删除失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除文章信息，AID【'.$params['art_id'].'】，TITLE【'.$params['atitle'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'删除成功！'));
		}
	}

}
?>