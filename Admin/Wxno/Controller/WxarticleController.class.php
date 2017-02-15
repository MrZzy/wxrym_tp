<?php
namespace Wxno\Controller;
use Think\Controller;

class WxarticleController extends Controller{
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}

	public function artList(){
		$artList = D('Wxarticle')->readartList();
		$this->catList = D('WxnoCategory')->readCatList();

		//禁用、启用
		$stop_url = 'Wxno/Wxarticle/artStop';
		$access_check_stop = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$stop_url."'")
			->select();
		if (count($access_check_stop[0])<1){
			$this->assign('unabled_stop',1);
		}
		//更新文章内容
		$rush_url = 'Wxno/Wxarticle/artRush';
		$access_check_rush = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$rush_url."'")
			->select();
		if (count($access_check_rush[0])<1){
			$this->assign('unabled_rush',1);
		}
		//批量更新文章内容
		$rushM_url = 'Wxno/Wxarticle/artRushM';
		$access_check_rushM = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$rushM_url."'")
			->select();
		if (count($access_check_rushM[0])<1){
			$this->assign('unabled_rushM',1);
		}
		//删除
		$del_url = 'Wxno/Wxarticle/artDel';
		$access_check_del = D('AdminMember')->table('wx_admin_menu')
			->where("menu_id in (".session('admin.menu_access').") and action = '".$del_url."'")
			->select();
		if (count($access_check_del[0])<1){
			$this->assign('unabled_del',1);
		}

		$this->assign('row_count',$artList['row_count']);
		$this->assign('art_list',$artList['list']);
		$this->assign('page',$artList['page']);

		$this->display('art_list');
	}
	public function artSet(){
		$params = I('post.');
		$data_save = array();
		$log_desc = '';
		switch ($params['met']){
			case 'not_hot':
				$data_save = array('data'=>array('is_hot' => 0));
				$log_desc = '撤销热门';
				break;
			case 'hot':
				$data_save = array('data'=>array('is_hot' => 1));
				$log_desc = '设置热门';
				break;
			case 'not_best':
				$data_save = array('data'=>array('is_best' => 0));
				$log_desc = '撤销精选';
				break;
			case 'best':
				$data_save = array('data'=>array('is_best' => 1));
				$log_desc = '设置精选';
				break;
			default :
				echo json_encode(array('code'=>-99,'msg'=>'参数错误！'));
				break;
		}
		$data_save['aid'] = $params['aid'];
		$data_save['where'] = 'aid = '.$params['aid'];
		$res = D('Wxarticle')->artSave($data_save);
		if (false===$res){
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '成功'.$log_desc.'，AID【'.$params['aid'].'】，ATITLE【'.$params['art_title'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function artStop(){
		$params = I('post.');
		$data_wxnoStop = array(
			'met' => $params['met'],
			'aid' => $params['aid'],
			'data' => array(
				'is_delete' => ($params['met']=='stop') ? 1 : 0,
			),
			'where' => 'aid = '.$params['aid'],
		);
		$res = D('Wxarticle')->artSave($data_wxnoStop);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => ($params['met']=='stop') ? '禁用公众号文章信息，AID【'.$params['aid'].'】，ATITLE【'.$params['art_title'].'】' : '启用公众号文章信息，AID【'.$params['aid'].'】，ATITLE【'.$params['art_title'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	public function artDel(){
		$params = I('post.');
		$artInfo = D('Wxarticle')->readartList('t_a.aid = '.$params['aid']);
		$this_artInfo = $artInfo['list'][0];
		$res = D('Wxarticle')->artDel($params['aid']);
		if (false===$res){
			// $this->error('操作失败！');
			echo json_encode(array('code'=>-1,'msg'=>'操作失败！'));
		}else{
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '删除公众号文章信息，AID【'.$params['aid'].'】，ATITLE【'.$params['art_title'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'操作成功！'));
		}
	}
	
	function getContentByUrl($url){
		// $url = "http://mp.weixin.qq.com/s?__biz=MjM5NjAwMjEwMA==&mid=405934464&idx=1&sn=5e210854049951c5322cde7d77a7fbe2&3rd=MzA3MDU4NTYzMw==&scene=6";
		// $contents = file_get_contents(htmlspecialchars_decode($url));
		/* $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, htmlspecialchars_decode($url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $contents = curl_exec($ch);
        curl_close($ch);
		$contents = explode('js_article', $contents);
		$contents = $contents[1];
		$contents = explode('<script>window.moon_map', $contents);
		$contents = $contents[0];
		$contents = '<div id="js_article'.$contents; */
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, htmlspecialchars_decode($url));
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$contents = curl_exec($ch);
		curl_close($ch);
		$contents = explode('js_article', $contents);
		$contents = $contents[1];
		$contents = explode('var first_sceen__time', $contents);
		$contents = $contents[0];
		$contents = '<div id="js_article'.$contents;
		$contents = preg_replace('/<div class="profile_inner">.*?<\/div>/is','',$contents);
		$contents = str_replace('https://mmbiz.qlogo.cn/mmbiz/','http://mmbiz.qpic.cn/mmbiz/',$contents);
		$contents = str_replace('http://mmbiz.qlogo.cn/mmbiz/','http://mmbiz.qpic.cn/mmbiz/',$contents);
		//清除js内容
		$contents = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $contents);
		$contents = preg_replace('/<script[^>]*?>.*?/is', '', $contents);
		$contents .= '</div></div></div></div>';
		//随机取内容中一张png图片作为封面图
		$art_pic = '';
		$art_desc = '';
		$root_path = C('UP_PATH').'wxarticle/';
		$img_path = date('Y_m_d',time()).'/';
		$date_now = date('YmdHis',time());
		$rndkey = randomkeys(6);
		if ($this->create_dirs($root_path.$img_path)){
			$pattern ='<img.*?data-src="(.*?)">';
			preg_match_all($pattern,$contents,$matches);
			$tmp_pics = $matches[1];
			$rand_pic = $this->get_img($tmp_pics);
			if ($rand_pic!=''){
				$img_ext = substr($rand_pic,strpos($rand_pic,'wx_fmt=')+7);
				$img_get = $root_path.$img_path.$date_now.'.'.$img_ext;
				// $img_url = file_get_contents($rand_pic);
				$ch_img = curl_init();
		        curl_setopt($ch_img, CURLOPT_URL, htmlspecialchars_decode($rand_pic));
		        curl_setopt($ch_img, CURLOPT_TIMEOUT, 30);
		        curl_setopt($ch_img, CURLOPT_RETURNTRANSFER, TRUE);
		        $img_url = curl_exec($ch_img);
		        curl_close($ch_img);
				file_put_contents($img_get,$img_url);

				$img_new = $root_path.$img_path.$date_now.'_'.$rndkey.'.'.$img_ext;
				$met_img = 'ImageCreateFrom'.$img_ext;
				$src = $met_img($img_get);
				$width = ImageSx($src);
				$height = ImageSy($src);
				$x = 100;
				$y = 100;
				$dst = ImageCreateTrueColor($x,$y);
				ImageCopyResampled($dst,$src,0,0,0,0,$x,$y,$width,$height);
				$met_make = 'Image'.$img_ext;
				if ($met_make($dst,$img_new)){
					$art_pic = $img_path.$date_now.'_'.$rndkey.'.'.$img_ext;
					if (file_exists($img_get))
						unlink($img_get);
					imagedestroy($dst);
				}
			}
		}
		//替换图片路径
		$contents = str_replace('http://mmbiz.qpic.cn/mmbiz/','http://read.html5.qq.com/image?src=forum&q=5&r=0&imgflag=7&imageUrl=http://mmbiz.qpic.cn/mmbiz/',$contents);

		$title_content = explode('id="activity-name">', $contents);
		$title_content = $title_content[1];
		$title_content = explode('<div class="rich_media_meta_list">', $title_content);
		$title_content = $title_content[0];
		$title_content = trim(str_replace('</h2>', '', $title_content));
		$title_content = str_replace('"', "'", $title_content);

		$desc_content = explode('id="js_content">', $contents);
		$desc_content = $desc_content[1];
		$desc_content = explode('<link rel="stylesheet"', $desc_content);
		$desc_content = $desc_content[0];
		$art_desc = trim(subtext(trim(strip_tags(trim($desc_content))),170));
		$art_desc = A('Emoji')->emoji_to_string($art_desc);
		
		// $title_content = mb_convert_encoding($title_content, 'UTF-8');
		$title_content = A('Emoji')->emoji_to_string($title_content);
		
		$art_content = str_replace('data-src="','src="',$contents);
		$art_content = A('Emoji')->emoji_to_string($art_content);
		return array(
			'art_title' => (strlen($title_content)>100) ? subtext($title_content,30) : $title_content,
			'art_pic' => $art_pic,
			'art_desc' => $art_desc,
			'art_content' => $art_content,
		);
	}
	function create_dirs($dir,  $mode=777){
		return is_dir($dir) or ( $this->create_dirs(dirname($dir), $mode) and mkdir($dir, $mode) );
	}
	function get_img($tmp_pics){
		$k = 0;
		for ($i=0;$i<count($tmp_pics);$i++){
			$ext = strtolower(substr($tmp_pics[$i],strpos($tmp_pics[$i],'wx_fmt=')+7));
			if ($ext=='png' || $ext=='jpeg')//png、jpeg、bmp、gif
				$k ++;
		}
		if ($k==0){//没有png/jpeg图片
			return '';
		}else{
			$rnd = mt_rand(0,count($tmp_pics)-1);
			$art_pic = $tmp_pics[$rnd];
			$img_ext = strtolower(substr($art_pic,strpos($art_pic,'wx_fmt=')+7));
			if ($img_ext!='png' && $img_ext!='jpeg'){
				$art_pic = $this->get_img($tmp_pics);
			}
			return $art_pic;
		}
	}
	/*function getContentByUrl($url){
		// $url = "http://mp.weixin.qq.com/s?__biz=MjM5NjAwMjEwMA==&mid=405934464&idx=1&sn=5e210854049951c5322cde7d77a7fbe2&3rd=MzA3MDU4NTYzMw==&scene=6";
		$contents = file_get_contents($url);
		$contents = explode('js_article', $contents);
		$contents = $contents[1];
		$contents = explode('<script>window.moon_map', $contents);
		$contents = $contents[0];
		$contents = '<div id="js_article'.$contents;
		$pattern ='<img.*?data-src="(.*?)">';
		preg_match_all($pattern,$contents,$matches);
		$tmp_arr = $matches[1];
		//创建图片保存目录
		$img_path = C('UP_PATH').'wxarticle/'.date('Y-m-d',time()).'/';
		if (!file_exists($img_path)) createFolder($img_path);
		foreach ($tmp_arr as $k=>$v){
			$img_name = substr($v,strpos($v,'mmbiz/')+6,strpos($v,'/0?')-(strpos($v,'mmbiz/')+6));
			$img_ext = substr($v,strpos($v,'wx_fmt=')+7);
			$img = $img_path.$img_name.'.'.$img_ext;
			$img_url = file_get_contents($v);
			file_put_contents($img,$img_url);
			$contents = str_replace('src="http://mmbiz.qpic.cn/mmbiz/'.$img_name.'/0?wx_fmt='.$img_ext.'"','src="/'.$img.'"',$contents)."<br />";
		}
		$contents = preg_replace('/<script[^>]*?>.*?<\/script>/is', '', $contents);
		$contents = str_replace('data-src="','src="',$contents);
		return $contents;
	}*/
	//更新文章内容
	public function artRush(){
		$params = I('post.');
		$arr_get = $this->getContentByUrl($params['alink']);
		$data_wxartSave = array(
			'aid' => $params['aid'],
			'where' => 'aid = '.$params['aid'],
			'data' => array(
				'art_title' => str_replace('"', "'", $arr_get['art_title']),
				'art_pic' => $arr_get['art_pic'],
				'art_desc' => $arr_get['art_desc'],
				'art_content' => $arr_get['art_content'],
				'upd_time' => date('Y-m-d H:i:s',time()),
			),
		);
		$res_rush = D('Wxarticle')->artSave($data_wxartSave);
		if ($res_rush!==false){
			$data_log = array(
				'log_type' => 2,	//1登录;2操作
				'user_name' => session('admin.member_name'),
				'action' => '',
				'log_desc' => '更新公众号文章内容，AID【'.$params['aid'].'】,ATITLE【'.$params['atitle'].'】',
				'ip' => GetIP(),
				'add_time' => date('Y-m-d H:i:s',time()),
			);
			$this->Init->logRec($data_log);
			echo json_encode(array('code'=>1,'msg'=>'更新成功'));
		}else{
			echo json_encode(array('code'=>-1,'msg'=>'更新失败'));
		}
	}
	public function artRushM(){
		$params = I('post.');
		$aids = $params['aids'];
		$count_rush = 0;
		$msg_rush = '';
		for ($i=0; $i < count($aids); $i++) { 
			if ($aids[$i]>0){
				//取原文链接
				$art_real_link = M('wxarticle','wx_')->field('art_real_link')->where('aid = '.$aids[$i])->find();
				if (is_array($art_real_link)){
					$arr_get = $this->getContentByUrl(htmlspecialchars_decode($art_real_link['art_real_link']));
					$data_wxartSave = array(
						'aid' => $aids[$i],
						'where' => 'aid = '.$aids[$i],
						'data' => array(
							'art_title' => str_replace('"', "'", $arr_get['art_title']),
							'art_pic' => $arr_get['art_pic'],
							'art_desc' => $arr_get['art_desc'],
							'art_content' => $arr_get['art_content'],
							'upd_time' => date('Y-m-d H:i:s',time()),
						),
					);
					$res_rush = D('Wxarticle')->artSave($data_wxartSave);
					if ($res_rush!==false || $res_rush>0){
						$count_rush ++;
					}
				}else{
					$msg_rush .= 'AID【'.$aids[$i].'】原文链接为空。';
				}
			}
		}
		if ($count_rush==0){
			echo json_encode(array('code'=>-1,'msg'=>'更新失败'));
		}else{
			echo json_encode(array('code'=>1,'msg'=>'批量更新【'.count($aids).'】篇，其中成功更新【'.$count_rush.'】篇'));
		}
	}
	public function artEdit(){
		$params = I('request.');
		$this->wxnoList = D('Wxno')->readNoAll();;
		if ($params['aid']>0){
			$artInfo = D('Wxarticle')->readartList('t_a.aid = '.$params['aid']);
			$this->artInfo = $artInfo['list'][0];
		}
		$data_wxartSave = array(
			'data' => array(
				'art_title' => str_replace('"', "'", $params['art_title']),
				'wx_id' => $params['wx_id'],
				'art_real_link' => htmlspecialchars($params['art_real_link']),
				'art_order' => $params['art_order'],
				'art_desc' => str_replace('"', "'", $params['art_desc']),
				'is_hot' => $params['is_hot'],
				'is_best' => $params['is_best'],
				'is_delete' => $params['is_delete'],
				'seo_title' => $params['seo_title'],
				'seo_keywords' => $params['seo_keywords'],
				'seo_desc' => $params['seo_desc'],
				// 'art_content' => $this->getContentByUrl($params['art_real_link']),
				// 'add_time' => '',
				'upd_time' => date('Y-m-d H:i:s',time()),
			),
		);
		//接收上传的图片
		if ($params['save']=='1' || $params['add']=='1'){
			$c = new \Org\Tj\ImgUpload();
			$error_logo = $_FILES['file_logo']['error'];
			$arr_logo = $_FILES['file_logo'];
			$save_path = C('UP_PATH').'wxarticle/'.date('Y_m_d',time()).'/';
			if( $error_logo == 0 ){
				//封面图
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
					$data_wxartSave['data']['art_pic'] = date('Y_m_d',time()).'/'.$up_logo['url'];
				}
			}
		}
		if ($params['save']=='1'){
			$data_wxartSave['aid'] = $params['aid'];
			$data_wxartSave['where'] = 'aid = '.$params['aid'];
			if (in_array('upd_title', $params['upd_info'])){
				$arr_get = $this->getContentByUrl($params['art_real_link']);
				$data_wxartSave['data']['art_title'] = $arr_get['art_title'];
			}
			if (in_array('upd_content', $params['upd_info'])){
				$arr_get = $this->getContentByUrl($params['art_real_link']);
				$data_wxartSave['data']['art_content'] = $arr_get['art_content'];
			}
			if (in_array('upd_pic', $params['upd_info'])){
				$arr_get = $this->getContentByUrl($params['art_real_link']);
				$data_wxartSave['data']['art_pic'] = $arr_get['art_pic'];
			}
			if (in_array('upd_desc', $params['upd_info'])){
				$arr_get = $this->getContentByUrl($params['art_real_link']);
				$data_wxartSave['data']['art_desc'] = $arr_get['art_desc'];
			}
			$res_artSave = D('Wxarticle')->artSave($data_wxartSave);
			if (false===$res_artSave){
				$this->error('公众号信息更改失败，请稍后重试！');
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '更改公众号文章信息，AID【'.$params['aid'].'】，OLD_TITLE【'.$params['old_title'].'】，NEW_TITLE【'.$params['art_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('公众号文章信息更新成功！',U('Wxno/Wxarticle/artList'));
			}
		}else if ($params['add']=='1'){
			$data_wxartSave['data']['add_time'] = date('Y-m-d H:i:s',time());
			$arr_get = $this->getContentByUrl($params['art_real_link']);
			if (in_array('upd_title', $params['upd_info'])){
				$data_wxartSave['data']['art_title'] = $arr_get['art_title'];
			}
			if (in_array('upd_pic', $params['upd_info'])){
				$data_wxartSave['data']['art_pic'] = $arr_get['art_pic'];
			}
			if (in_array('upd_desc', $params['upd_info'])){
				$data_wxartSave['data']['art_desc'] = $arr_get['art_desc'];
			}
			$data_wxartSave['data']['art_content'] = $arr_get['art_content'];
			$res_artAdd = D('Wxarticle')->artSave($data_wxartSave);
			if ($res_artAdd>0){
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '添加公众号文章信息，AID【'.$res_artAdd.'】，ATITLE【'.$params['art_title'].'】',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('公众号文章信息添加成功！',U('Wxno/Wxarticle/artList'));
			}else{
				$this->error('公众号文章信息添加失败，请稍后重试！');
			}
		}else{
			$this->display('art_edit');
		}
	}

	public function artAddM(){
		//批量添加文章 - 仅为同一公众号下
		$params = I('post.');
		if ($params['save']=='1'){
			$arr_art = explode('|', trim($params['artAddM']));
			$art_count = 0;
			$res_count = 0;
			for ($i=0; $i < count($arr_art); $i++) { 
				if ($arr_art[$i]!=''){
					$arr_get = $this->getContentByUrl($arr_art[$i]);
					$data_wxartSave = array(
						'data' => array(
							'wx_id' => $params['wx_id'],
							'art_title' => str_replace('"', "'", $arr_get['art_title']),
							'art_real_link' => htmlspecialchars($arr_art[$i]),
							'art_pic' => $arr_get['art_pic'],
							'art_desc' => $arr_get['art_desc'],
							'art_content' => $arr_get['art_content'],
							'add_time' => date('Y-m-d H:i:s',time()),
							'upd_time' => date('Y-m-d H:i:s',time()),
						),
					);
					$res_artSave = D('Wxarticle')->artSave($data_wxartSave);
					if ($res_artSave){
						$res_count ++;
					}
					$art_count ++;
				}
			}
			/*$arr_art = explode('&&', htmlspecialchars_decode(trim($params['artAddM'])));
			$art_count = 0;
			$res_count = 0;
			for ($i=0; $i < count($arr_art); $i++) { 
				if ($arr_art[$i]!=''){
					$tmp_arr = explode('|', $arr_art[$i]);
					if ($tmp_arr[0]=='' || $tmp_arr[1]==''){
						$this->error('第<'.($i+1).'>条（即第'.$i.'个"&&"后）格式错误，请检查！');
					}
					$data_wxartSave = array(
						'data' => array(
							'wx_id' => $params['wx_id'],
							'art_title' => str_replace('"', "'", $tmp_arr[0]),
							'art_real_link' => $tmp_arr[1],
							'art_content' => $this->getContentByUrl($tmp_arr[1]),
							'add_time' => date('Y-m-d H:i:s',time()),
							'upd_time' => date('Y-m-d H:i:s',time()),
						),
					);
					$res_artSave = D('Wxarticle')->artSave($data_wxartSave);
					if ($res_artSave){
						$res_count ++;
					}
					$art_count ++;
				}
			}*/
			if ($res_count==0){
				$this->error('文章批量添加失败！');
			}else if ($res_count < $art_count){
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '为公众号WXID【'.$params["wx_id"].'】共添加<'.$art_count.'>篇文章，其中有<'.($art_count-$res_count).'>篇添加失败！',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('共添加<'.$art_count.'>篇文章，其中有<'.($art_count-$res_count).'>篇添加失败！',U('Wxarticle/artList'));
			}else{
				$data_log = array(
					'log_type' => 2,	//1登录;2操作
					'user_name' => session('admin.member_name'),
					'action' => '',
					'log_desc' => '公众号WXID【'.$params["wx_id"].'】文章批量添加成功，共添加<'.$art_count.'>篇文章！',
					'ip' => GetIP(),
					'add_time' => date('Y-m-d H:i:s',time()),
				);
				$this->Init->logRec($data_log);
				$this->success('文章批量添加成功，共添加<'.$art_count.'>篇文章！',U('Wxarticle/artList'));
			}
		}else{
			$this->wxnoList = D('Wxno')->readNoAll();
			$this->display('art_add_m');
		}
	}

}
?>