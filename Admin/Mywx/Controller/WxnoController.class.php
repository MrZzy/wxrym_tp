<?php
namespace Mywx\Controller;
use Think\Controller;

class WxnoController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function wxList(){
        $newSession = M('admin_member','wx_')->where('id = '.session('admin.id'))->find();
        session('admin.wxids',$newSession['wxids']);
		if (empty(session('admin.wxids')) || session('admin.wxids')=='0'){
			$this->display('/null_wx');
			exit();
		}
		$noList = D('Wxno')->readNoList();
		$this->catList = D('WxnoCategory')->readCatList();

		$this->assign('row_count',$noList['row_count']);
		$this->assign('no_list',$noList['list']);
		$this->assign('page',$noList['page']);
		
		D('WxnoCategory')->readCatList();

		$this->display('no_list');
	}
	public function wxStop(){
		$params = I('post.');
		$data_wxnoStop = array(
			'met' => $params['met'],
			'wx_id' => $params['wx_id'],
			'data' => array(
				'is_delete' => ($params['met']=='stop') ? 1 : 0,
			),
			'where' => 'wx_id = '.$params['wx_id'],
		);
        //判断该公众号是否属于此人
        $m_wx = M('wxno','wx_')->where('wx_id = '.$params['wx_id'])->find();
        if (!is_array($m_wx)){
            echo json_encode(array('code'=>-99,'msg'=>'公众号不存在！'));
            exit();
        }else if (!in_array($params['wx_id'],explode(',',session('admin.wxids')))){
            echo json_encode(array('code'=>-98,'msg'=>'此公众号不属于您账户下管理！'));
            exit();
        }
		$res = D('Wxno')->wxnoSave($data_wxnoStop);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
            //其下所有文章
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
	public function wxAdd(){
		$params = I('request.');
		$this->catList = D('WxnoCategory')->readAllCatList();
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
				// 'wx_img' => '',
				// 'wx_img_thumb' => '',
				'wx_desc' => $params['wx_desc'],
//				'is_checked' => -1,
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
			//判断该公众号是否属于此人
	        $m_wx = M('wxno','wx_')->where('wx_id = '.$params['wx_id'])->find();
	        if (!is_array($m_wx)){
	            // echo json_encode(array('code'=>-99,'msg'=>'公众号不存在！'));
	            $this->error('公众号不存在！');
	            exit();
	        }else if (!in_array($params['wx_id'],explode(',',session('admin.wxids')))){
	            // echo json_encode(array('code'=>-98,'msg'=>'此公众号不属于您账户下管理！'));
	            $this->error('此公众号不属于您账户下管理！');
	            exit();
	        }
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
				$this->success('公众号信息更新成功！',U('Mywx/Wxno/wxList'));
			}
		}else if ($params['add']=='1'){
			$data_wxnoSave['met'] = 'add';
            $data_wxnoSave['data']['is_checked'] = 0;
			$data_wxnoSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$res_noAdd = D('Wxno')->wxnoSave($data_wxnoSave);
			if ($res_noAdd>0){
                //将此id追加至member表wxids后
//                $sql_upd = 'update wx_admin_member set wxids = concat(wxids,",'.$res_noAdd.'") where id = '.session('admin.id');
//                $res_upd = M()->execute($sql_upd);
                $rsWxids = M('admin_member','wx_')->field('wxids')->where('id = '.session('admin.id'))->find();
                $newWxids = (!is_array($rsWxids) || $rsWxids['wxids']=='' || $rsWxids['wxids']=='0') ? '0' : $rsWxids['wxids'];
                $newWxids .= ','.$res_noAdd;
                $res_upd = M('admin_member','wx_')->data(['wxids'=>$newWxids])->where('id = '.session('admin.id'))->save();
                if ($res_upd===false){
                    $data_log = array(
                        'log_type' => 2,	//1登录;2操作
                        'user_name' => session('admin.member_name'),
                        'action' => '',
                        'log_desc' => '公众号【'.$res_noAdd.'】，WXNAME【'.$params['wx_name'].'】添加成功，但账号分配失败！',
                        'ip' => GetIP(),
                        'add_time' => date('Y-m-d H:i:s',time()),
                    );
                    $this->Init->logRec($data_log);
                    $this->success('公众号【'.$res_noAdd.'】，WXNAME【'.$params['wx_name'].'】提交成功，但账号分配失败，请联系管理员！',U('Mywx/Wxno/wxList'),9);
                }else{
                    $data_log = array(
                        'log_type' => 2,	//1登录;2操作
                        'user_name' => session('admin.member_name'),
                        'action' => '',
                        'log_desc' => '公众号 WXID【'.$res_noAdd.'】，WXNAME【'.$params['wx_name'].'】提交成功！',
                        'ip' => GetIP(),
                        'add_time' => date('Y-m-d H:i:s',time()),
                    );
                    $this->Init->logRec($data_log);
                    $this->success('公众号提交成功，请等待管理审核！',U('Mywx/Wxno/wxList'));
                }
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