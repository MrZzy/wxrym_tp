<?php
namespace Config\Controller;
use Think\Controller;

class BannerController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function bannerList(){
		$data_bannerIndex = array(
			'where' => 'banner_type = 1'
		);
		$this->bannerIndex_list = D('Banner')->readBannerList($data_bannerIndex);
		$data_bannerSingle = array(
			'where' => 'banner_type = 2'
		);
		$this->bannerSingle_list = D('Banner')->readBannerList($data_bannerSingle);

		//禁用、启用
		$stop_url = 'Config/Banner/bannerStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Config/Banner/bannerDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}

		$this->display('banner_list');
	}
	public function bannerStop(){
		$params = I('post.');
		$data_bannerStop = array(
			'met' => $params['met'],
			'data' => array(
				'is_delete' => $params['met']=='stop' ? 1 : 0,
			),
			'where' => 'id = '.$params['bid'],
		);
		$res_bannerStop = D('Banner')->bannerSave($data_bannerStop);
		if (false===$res_bannerStop){
			$this->error('操作失败！');
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => ($params['met']=='stop') ? '禁用轮播广告信息，BID【'.$params['bid'].'】，BTITLE【'.$params['bname'].'】' : '启用轮播广告信息，BID【'.$params['bid'].'】，BTITLE【'.$params['bname'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
		}
	}
	public function bannerDel(){
		$params = I('post.');
		$data_bannerDel = array(
			'met' => 'del',
			'where' => 'id = '.$params['bid'],
		);
		$res_bannerDel = D('Banner')->bannerSave($data_bannerDel);
		if (false===$res_bannerDel){
			$this->error('操作失败！');
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除轮播广告信息，BID【'.$params['bid'].'】，BTITLE【'.$params['bname'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
		}
	}
	public function bannerEdit(){
		$params = I('request.');
		if ($params['bid']>0){
			$bannerInfo = D('Banner')->readBannerList(array('where'=>'id = '.$params['bid']));
			$this->bannerInfo = $bannerInfo[0];
		}
		$data_bannerSave = array(
			'data' => array(
				'banner_type' => $params['banner_type'],
				'banner_title' => $params['banner_title'],
				'banner_url' => $params['banner_url'],
				// 'banner_img' => '',
				'open_met' => $params['open_met'],
				'banner_order' => $params['banner_order'],
				// 'add_time' => '',
				'is_delete' => $params['is_delete'],
			),
		);
		//接收上传的图片
		if ($params['save']=='1' || $params['add']=='1'){
			$error = $_FILES['file']['error'];
			if( $error == 0 ){
				$img_up = array(
					'file' => $_FILES['file']['tmp_name'],
					'savePath' => C('UP_PATH').'banner/',
				);
				$c = new \Org\Tj\ImgUpload();
				$up = $c->upload_checkWH($img_up);
				if( $up !== false ){
					$data_bannerSave['data']['banner_img'] = $up['url'];
				}
			}
		}
		if ($params['save']=='1'){
			$data_bannerSave['met'] = 'save';
			$data_bannerSave['where'] = 'id = '.$params['bid'];
			$res_bannerSave = D('Banner')->bannerSave($data_bannerSave);
			if (false===$res_bannerSave){
				//有上传图片则删除上传的图片
				if( $error == 0 ){
					$up_pic_0 = C('UP_PATH').'banner/'.$data_bannerSave['data']['banner_img'];
					if (file_exists($up_pic_0)) unlink($up_pic_0);
					$up_pic = '.'.C('UP_PATH').'banner/'.$data_bannerSave['data']['banner_img'];
					if (file_exists($up_pic)) unlink($up_pic);
				}

				$this->error('轮播广告信息更新失败！');
			}else{
				//更改了图片则删除原图
				if( $error == 0 ){
					$up_pic_0 = C('UP_PATH').'banner/'.$params['old_pic'];
					if (file_exists($up_pic_0)) unlink($up_pic_0);
					$up_pic = '.'.C('UP_PATH').'banner/'.$params['old_pic'];
					if (file_exists($up_pic)) unlink($up_pic);
				}

				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更改轮播广告信息，BID【'.$params['bid'].'】，OLD_BTITLE【'.$params['old_title'].'】，NEW_BTITLE【'.$params['banner_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('轮播广告信息更新成功！',U('Config/Banner/bannerList'));
			}
		}else if ($params['add']=='1'){
			$data_bannerSave['met'] = 'add';
			$data_bannerSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res_bannerAdd = D('Banner')->bannerSave($data_bannerSave);
			if ($res_bannerAdd>0){
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加轮播广告信息，BID【'.$res_bannerAdd.'】，BTITLE【'.$params['banner_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('轮播广告信息添加成功！',U('Config/Banner/bannerList'));
			}else{
				//删除上传的图片
				if( $error == 0 ){
					$up_pic_0 = C('UP_PATH').'banner/'.$data_bannerSave['data']['banner_img'];
					if (file_exists($up_pic_0)) unlink($up_pic_0);
					$up_pic = '.'.C('UP_PATH').'banner/'.$data_bannerSave['data']['banner_img'];
					if (file_exists($up_pic)) unlink($up_pic);
				}

				$this->error('添加轮播广告信息失败！');
			}
		}else{
			$this->display('banner_edit');
		}
	}

}
?>