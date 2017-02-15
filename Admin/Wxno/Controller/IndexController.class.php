<?php
namespace Wxno\Controller;
use Think\Controller;

class IndexController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function wxnoList(){
		$noList = D('Wxno')->readNoList();
		$this->catList = D('WxnoCategory')->readCatList();

		//禁用、启用
		$stop_url = 'Wxno/Index/wxnoStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//删除
		$del_url = 'Wxno/Index/wxnoDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}

		$this->assign('row_count',$noList['row_count']);
		$this->assign('no_list',$noList['list']);
		$this->assign('page',$noList['page']);

		$this->display('no_list');
	}
	public function wxnoStop(){
		$params = I('post.');
		$data_wxnoStop = array(
			'met' => $params['met'],
			'wx_id' => $params['wx_id'],
			'data' => array(
				'is_delete' => ($params['met']=='stop') ? 1 : 0,
			),
			'where' => 'wx_id = '.$params['wx_id'],
		);
		$res = D('Wxno')->wxnoSave($data_wxnoStop);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
            //禁用/启用公众号下所有文章
            $res_arc = M('wxarticle','wx_')->data(['is_delete'=>$data_wxnoStop['data']['is_delete']])->where('wx_id = '.$params['wx_id'])->save();
            if ($res_arc===false){
                echo json_encode(array('code'=>-2,'msg'=>'公众号下文章操作失败，请检查！'));
            }else {
                $data_log = array(
                    'log_type' => 2,    //1登录;2操作
                    'user_name' => session('admin.member_name'),
                    'action' => '',
                    'log_desc' => ($params['met'] == 'stop') ? '禁用公众号信息，WXID【' . $params['wx_id'] . '】，WXNAME【' . $params['wx_name'] . '】' : '启用公众号信息，WXID【' . $params['wx_id'] . '】，WXNAME【' . $params['wx_name'] . '】',
                    'ip' => GetIP(),
                    'add_time' => date('Y-m-d H:i:s', time()),
                );
                $this->Init->logRec($data_log);
            }
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function wxnoDel(){
		$params = I('post.');
		$wxnoInfo = D('Wxno')->readNoList('t_n.wx_id = '.$params['wx_id']);
		$this_wxnoInfo = $wxnoInfo['list'][0];
		$data_newsDel = array(
			'met' => 'del',
			'wx_id' => $params['wx_id'],
		);
		$res = D('Wxno')->wxnoSave($data_newsDel);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			//删除logo图片及二维码图片
			$no_logo_0 = C('UP_PATH').'wxno/'.$this_wxnoInfo['wx_logo'];
			if (file_exists($no_logo_0)) unlink($no_logo_0);
			$no_logo = '.'.C('UP_PATH').'wxno/'.$this_wxnoInfo['wx_logo'];
			if (file_exists($no_logo)) unlink($no_logo);
			$no_img_0 = C('UP_PATH').'wxno/'.$this_wxnoInfo['wx_img'];
			if (file_exists($no_img_0)) unlink($no_img_0);
			$no_img = '.'.C('UP_PATH').'wxno/'.$this_wxnoInfo['wx_img'];
			if (file_exists($no_img)) unlink($no_img);

            //删除其下所有文章
            $res_arc = M('wxarticle','wx_')->where('wx_id = '.$params['wx_id'])->delete();
            if ($res_arc===false){
                echo json_encode(array('code'=>-2,'msg'=>'公众号下文章删除失败，请手动删除！'));
            }else {
                $data_log = array(
                    'log_type' => 2,    //1登录;2操作
                    'user_name' => session('admin.member_name'),
                    'action' => '',
                    'log_desc' => '删除公众号信息，WXID【' . $params['wx_id'] . '】，WXNAME【' . $params['wx_name'] . '】',
                    'ip' => GetIP(),
                    'add_time' => date('Y-m-d H:i:s', time()),
                );
                $this->Init->logRec($data_log);
            }
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function wxnoAttrUpd(){
		$params = I('post.');
		$tmp_arr = explode(':', $params['send_data']);
		if (count($tmp_arr)<2){
			echo json_encode(array('code'=>-1,'msg'=>'参数错误！'));
			exit();
		}
		$data_upd = array(
			'met' => 'save',
			'wx_id' => $params['wx_id'],
			'data' => array(
				$tmp_arr[0] => $tmp_arr[1],
			),
			'where' => 'wx_id = '.$params['wx_id'],
		);
		$res = D('Wxno')->wxnoSave($data_upd);
		if (false===$res){
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '公众号特殊属性更改【'.$params['data_desc'].'】，WXID【'.$params['wx_id'].'】，WXNAME【'.$params['wx_name'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function wxnoCheck(){
		$params = I('post.');
		$tmp_arr = explode(':', $params['send_data']);
		if (count($tmp_arr)<2){
			echo json_encode(array('code'=>-1,'msg'=>'参数错误！'));
			exit();
		}
		$data_check = array(
			'met' => 'save',
			'wx_id' => $params['wx_id'],
			'data' => array(
				$tmp_arr[0] => $tmp_arr[1],
			),
			'where' => 'wx_id = '.$params['wx_id'],
		);
		$res = D('Wxno')->wxnoSave($data_check);
		if (false===$res){
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '公众号审核【'.$params['data_desc'].'】，WXID【'.$params['wx_id'].'】，WXNAME【'.$params['wx_name'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function wxnoEdit(){
		$params = I('request.');
		$this->catList = D('WxnoCategory')->readCatList();
		if ($params['wx_id']>0){
			$noInfo = D('Wxno')->readNoList('t_n.wx_id = '.$params['wx_id']);
			$this->noInfo = $noInfo['list'][0];
		}
		$data_wxnoSave = array(
			'data' => array(
				'wx_name' => $params['wx_name'],
				'wx_no' => $params['wx_no'],
				'cat_id' => $params['cat_id'],
				// 'wx_logo' => '',
				// 'wx_logo_thumb' => '',
				'wx_order' => $params['wx_order'],
				// 'wx_img' => '',
				// 'wx_img_thumb' => '',
				'wx_desc' => $params['wx_desc'],
				'is_hot' => $params['is_hot'],
				'is_rec_pic' => $params['is_rec_pic'],
				'is_recommend' => $params['is_recommend'],
				'is_good' => $params['is_good'],
				'is_delete' => $params['is_delete'],
				'seo_title' => $params['seo_title'],
				'seo_keywords' => $params['seo_keywords'],
				'seo_desc' => $params['seo_desc'],
				// 'add_time' => '',
			),
		);
		//接收上传的图片
		if ($params['save']=='1' || $params['add']=='1'){
			$c = new \Org\Tj\ImgUpload();
			$error_logo = $_FILES['file_logo']['error'];
			$error_img = $_FILES['file_img']['error'];
			$arr_logo = $_FILES['file_logo'];
			$arr_img = $_FILES['file_img'];
			$save_path = C('UP_PATH').'wxno/'.date('Y_m_d',time()).'/';
			if( $error_logo == 0 ){
				//缩略图
				$logo_thumb_up = array(
					'file' => $arr_logo['tmp_name'],
					'savePath' => $save_path,
					'newW' => 80,
					'newH' => 80,
				);
				$up_logo_thumb = $c->create_thumb($logo_thumb_up);
				if ($up_logo_thumb == '-1'){
					// $this->error('logo图片宽高必须相等');
				}else if ($up_logo_thumb !== false){
					$data_wxnoSave['data']['wx_logo_thumb'] = date('Y_m_d',time()).'/'.$up_logo_thumb['url'];
				}
				//原图
				$logo_up = array(
					'file' => $_FILES['file_logo']['tmp_name'],
					'savePath' => $save_path,
					'needW' => 100,
					'needH' => 100,
				);
				$up_logo = $c->upload_checkWH($logo_up);
				if (!is_array($up_logo)){
					$this->error($up_logo);
				}else if( $up_logo !== false ){
					$data_wxnoSave['data']['wx_logo'] = date('Y_m_d',time()).'/'.$up_logo['url'];
				}
			}
			if( $error_img == 0 ){
				//缩略图
				$img_thumb_up = array(
					'file' => $arr_img['tmp_name'],
					'savePath' => $save_path,
					'newW' => 80,
					'newH' => 80,
				);
				$up_img_thumb = $c->create_thumb($img_thumb_up);
				if ($up_img_thumb == '-1'){
					// $this->error('二维码图片宽高必须相等');
				}else if ($up_img_thumb !== false){
					$data_wxnoSave['data']['wx_img_thumb'] = date('Y_m_d',time()).'/'.$up_img_thumb['url'];
				}
				//原图
				$img_up = array(
					'file' => $_FILES['file_img']['tmp_name'],
					'savePath' => $save_path,
					'needW' => 200,
					'needH' => 200,
				);
				$up_img = $c->upload_checkWH($img_up);
				if (!is_array($up_img)){
					$this->error($up_img);
				}else if( $up_img !== false ){
					$data_wxnoSave['data']['wx_img'] = date('Y_m_d',time()).'/'.$up_img['url'];
				}
			}
		}
		if ($params['save']=='1'){
			$data_wxnoSave['met'] = 'save';
			$data_wxnoSave['where'] = 'wx_id = '.$params['wx_id'];
			$res_noSave = D('Wxno')->wxnoSave($data_wxnoSave);
			if (false===$res_noSave){
				//有上传图片则删除上传的图片
				if( $error_logo == 0 ){
					//原图
					$up_logo_0 = $save_path.$data_wxnoSave['data']['wx_logo'];
					if (file_exists($up_logo_0)) unlink($up_logo_0);
					$up_logo = '.'.$save_path.$data_wxnoSave['data']['wx_logo'];
					if (file_exists($up_logo)) unlink($up_logo);
					//缩略图
					$up_logo_thumb_0 = $save_path.$data_wxnoSave['data']['wx_logo_thumb'];
					if (file_exists($up_logo_thumb_0)) unlink($up_logo_thumb_0);
					$up_logo_thumb = '.'.$save_path.$data_wxnoSave['data']['wx_logo_thumb'];
					if (file_exists($up_logo_thumb)) unlink($up_logo_thumb);
				}
				if( $error_img == 0 ){
					//原图
					$up_img_0 = $save_path.$data_wxnoSave['data']['wx_img'];
					if (file_exists($up_img_0)) unlink($up_img_0);
					$up_img = '.'.$save_path.$data_wxnoSave['data']['wx_img'];
					if (file_exists($up_img)) unlink($up_img);
					//缩略图
					$up_img_thumb_0 = $save_path.$data_wxnoSave['data']['wx_img_thumb'];
					if (file_exists($up_img_thumb_0)) unlink($up_img_thumb_0);
					$up_img_thumb = '.'.$save_path.$data_wxnoSave['data']['wx_img_thumb'];
					if (file_exists($up_img_thumb)) unlink($up_img_thumb);
				}
				$this->error('公众号信息更改失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更改公众号信息，WXID【'.$params['wx_id'].'】，OLD_WXNAME【'.$params['old_name'].'】，NEW_WXNAME【'.$params['wx_name'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('公众号信息更新成功！',U('Wxno/Index/wxnoList'));
			}
		}else if ($params['add']=='1'){
			$data_wxnoSave['met'] = 'add';
			$data_wxnoSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res_noAdd = D('Wxno')->wxnoSave($data_wxnoSave);
			if ($res_noAdd>0){

				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加公众号信息，WXID【'.$res_noAdd.'】，WXNAME【'.$params['wx_name'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('公众号信息添加成功！',U('Wxno/Index/wxnoList'));
			}else{
				//删除上传的图片
				if( $error_logo == 0 ){
					//原图
					$up_logo_0 = $save_path.$data_wxnoSave['data']['wx_logo'];
					if (file_exists($up_logo_0)) unlink($up_logo_0);
					$up_logo = '.'.$save_path.$data_wxnoSave['data']['wx_logo'];
					if (file_exists($up_logo)) unlink($up_logo);
					//缩略图
					$up_logo_thumb_0 = $save_path.$data_wxnoSave['data']['wx_logo_thumb'];
					if (file_exists($up_logo_thumb_0)) unlink($up_logo_thumb_0);
					$up_logo_thumb = '.'.$save_path.$data_wxnoSave['data']['wx_logo_thumb'];
					if (file_exists($up_logo_thumb)) unlink($up_logo_thumb);
				}
				if( $error_img == 0 ){
					//原图
					$up_img_0 = $save_path.$data_wxnoSave['data']['wx_img'];
					if (file_exists($up_img_0)) unlink($up_img_0);
					$up_img = '.'.$save_path.$data_wxnoSave['data']['wx_img'];
					if (file_exists($up_img)) unlink($up_img);
					//缩略图
					$up_img_thumb_0 = $save_path.$data_wxnoSave['data']['wx_img_thumb'];
					if (file_exists($up_img_thumb_0)) unlink($up_img_thumb_0);
					$up_img_thumb = '.'.$save_path.$data_wxnoSave['data']['wx_img_thumb'];
					if (file_exists($up_img_thumb)) unlink($up_img_thumb);
				}
				$this->error('公众号信息添加失败，请稍后重试！');
			}
		}else{
			$this->display('no_edit');
		}
	}

}
?>