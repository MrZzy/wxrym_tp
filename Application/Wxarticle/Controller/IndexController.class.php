<?php
namespace Wxarticle\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
	public function allartList(){
		$url_cur['wxart'] = 'active';
		$this->assign('url_cur',$url_cur);
		
		// $this->this_title = '公众号微文导读';
		$menuInfo = M('menu','wx_')->where('menu_tag = "wxart"')->find();
		$this->this_title = empty($menuInfo['seo_title']) ? '公众号微文导读' : $menuInfo['seo_title'];
		$this->this_keywords = empty($menuInfo['seo_keywords']) ? '' : $menuInfo['seo_keywords'];
		$this->this_desc = empty($menuInfo['seo_desc']) ? '' : $menuInfo['seo_desc'];
		
		$page_size = '';
		$wxart_list = D('Wxarticle')->readWxartList('','','wa.add_time desc',$page_size);
		$this->assign('wxart_list',$wxart_list['list']);
		$this->assign('row_count',$wxart_list['row_count']);
		$this->assign('page',$wxart_list['page']);
		$this->assign('page_size',$wxart_list['page_size']);
		$this->display('wxart_list');
	}
	
    public function wxartList(){
		$url_cur['wxart'] = 'active';
		$this->assign('url_cur',$url_cur);
		
		$params = I('request.');
		// $params = I('post.');
		if ($params['wx_no']!='' && $params['wx_no']!=='`'){
			//取公众号信息详情
			$wxnoModel = new \Wxno\Model\WxnoModel();
			$wxno_list = $wxnoModel->readWxnoList('','wn.wx_no = "'.$params['wx_no'].'" and wn.is_delete = 0','','');
            if (!is_array($wxno_list['list'][0]) || count($wxno_list['list'][0])<=0){
                $this->redirect('/404_not_found.php');
            }
			$this->wxnoInfo = $wxno_list['list'][0];
			
			$this->wx_no = $params['wx_no'];
			$this->wx_name = $this->wxnoInfo['wx_name'];
			$this->this_title = empty($this->wxnoInfo['seo_title']) ? $this->wxnoInfo['wx_name'].'（'.$params['wx_no'].'）'.' _ 公众号详情' : $this->wxnoInfo['seo_title'];
			$this->this_keywords = empty($this->wxnoInfo['seo_keywords']) ? '' : $this->wxnoInfo['seo_keywords'];
			$this->this_desc = empty($this->wxnoInfo['seo_desc']) ? (empty($this->wxnoInfo['wx_desc']) ? '' : $this->wxnoInfo['wx_desc']) : $this->wxnoInfo['seo_desc'];
		}else{
			// $this->this_title = '公众号文章列表';
			$this->redirect('/wxart');
		}
		
		//公众号文章列表
		$page_size = '';
		$wxart_list = D('Wxarticle')->readWxartList('','','wa.add_time desc',$page_size);
		$this->assign('wxart_list',$wxart_list['list']);
		$this->assign('row_count',$wxart_list['row_count']);
		$this->assign('page',$wxart_list['page']);
		$this->assign('page_size',$wxart_list['page_size']);
		$this->display('art_list');
    }
    public function wxartListBySelType(){
		$url_cur['wxart'] = 'active';
		$this->assign('url_cur',$url_cur);
		$params = I('request.');
		$this->sel_type = $params['sel_type'];
		$title = '';
		switch ($params['sel_type']){
			case 'hot':
				$title = '热门文章';
				break;
			case 'best':
				$title = '任意门精选美文';
				break;
			default:
				$title = '微信任意门';
				break;
		}
		// $this->this_title = $title.' _ 公众号微文列表';
		$menuInfo = M('menu','wx_')->where('menu_tag = "wxart"')->find();
		$this->this_title = $title.'_'.(empty($menuInfo['seo_title']) ? '公众号微文列表' : $menuInfo['seo_title']);
		$this->this_keywords = empty($menuInfo['seo_keywords']) ? '' : $menuInfo['seo_keywords'];
		$this->this_desc = empty($menuInfo['seo_desc']) ? '' : $menuInfo['seo_desc'];

		$wxart_list = D('Wxarticle')->readWxartList('','','wa.add_time desc',$page_size);
		$this->assign('wxart_list',$wxart_list['list']);
		$this->assign('row_count',$wxart_list['row_count']);
		$this->assign('page',$wxart_list['page']);
		$this->assign('page_size',$wxart_list['page_size']);
		$this->display('wxart_list');
	}
	public function wxartPageList(){
		$params = I('post.');
		$wxart_page_size = $params['page_size'];
		$wxart_list = D('Wxarticle')->readWxartList('','','wa.add_time desc',$wxart_page_size);
		$append_html = '';
		foreach($wxart_list['list'] as $k=>$v){
			$append_html .= 
			"<div class=\"history-grids-allart art_list_div\">
				<div class=\"col-md-12 history-grid\">
					<a href=\"{$do_main}/a/".$v['aid']."\" title=\"".$v['art_title']."\">
					<div class=\"history-left\">
			";
			if ($v['wx_logo_thumb']!=''){
				$append_html .= "<img src=\"".C('GET_FILE_PATH')."wxarticle/".$v['art_pic']."\" onerror=\"javascript:this.src='".C('GET_FILE_PATH')."wxno/".$v['wx_logo_thumb']."'\" />";
			}else{
				$append_html .= "<img src=\"".C('GET_FILE_PATH')."wxarticle/".$v['art_pic']."\" onerror=\"javascript:this.src='".C('FRONT_RES_PATH')."images/no_pic_thumb.png'\" />";
			}
			$append_html .= 
			"</div>
					</a>
					<div class=\"history-right\">
						<h4>".($v['is_best']=='1' ? '<font color="red">[优]</font>' : '').($v['is_hot']=='1' ? '<font color="red">[热]</font>' : '')."<a href=\"{$do_main}/a/".$v['aid']."\" title=\"".$v['art_title']."\">".$v['art_title']."&nbsp;[".time_tran($v['add_time'])."]</a></h4>
						<p><a href=\"{$do_main}/a/".$v['aid']."\">".subtext($v['art_desc'],120)."</a></p>
					</div>
				</div>
				<div class=\"clearfix\"></div>
			</div>";
		}
		echo $append_html;
	}
	
	public function artContent(){
		$url_cur['wxart'] = 'active';
		$this->assign('url_cur',$url_cur);
		
		$params = I('request.');
		if (!$params['aid']>0){
			$this->redirect('/wxart');
		}else{
			//取文章详情信息
			$wxart_list = D('Wxarticle')->readWxartList('','wa.aid = '.$params['aid'].' and wa.is_delete = 0 and wn.is_delete = 0','',1);
            if (!is_array($wxart_list['list']) || count($wxart_list['list'])<=0){
                $this->redirect('/404_not_found.php');
            }
			$this->artInfo = $wxart_list['list'][0];
			
			$this->this_title = empty($this->artInfo['seo_title']) ? $this->artInfo['art_title'].'（'.$this->artInfo['wx_name'].'['.$this->artInfo['wx_no'].']）'.'_微文详情' : $this->artInfo['seo_title'];
			$this->this_keywords = empty($this->artInfo['seo_keywords']) ? '' : $this->artInfo['seo_keywords'];
			$this->this_desc = empty($this->artInfo['seo_desc']) ? (empty($this->artInfo['art_desc']) ? '' : $this->artInfo['art_desc']) : $this->artInfo['seo_desc'];
			
			//取十篇该公众号下文章
			$newArt_list = D('Wxarticle')->readWxartList('','wa.is_delete = 0 and wa.wx_id = '.$this->artInfo['wx_id'],'wa.add_time desc',10);
			$this->newArt_list = $newArt_list['list'];
			
			$this->display('art_info');
		}
	}
	
}