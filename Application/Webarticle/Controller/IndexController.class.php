<?php
namespace Webarticle\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
	public function about(){
		$url_cur['about'] = 'active';
		$this->assign('url_cur',$url_cur);
		$artInfo = D('Webarticle')->readWebartInfo(1);
		
		$this->this_title = empty($artInfo['seo_title']) ? '关于我们' : $artInfo['seo_title'];
		$this->this_keywords = empty($artInfo['seo_keywords']) ? '' : $artInfo['seo_keywords'];
		$this->this_desc = empty($artInfo['seo_desc']) ? '' : $artInfo['seo_desc'];
		
		$this->display('about_us');
	}
	public function contact(){
		$url_cur['contact'] = 'active';
		$this->assign('url_cur',$url_cur);
		$artInfo = D('Webarticle')->readWebartInfo(3);
		
		$this->this_title = empty($artInfo['seo_title']) ? '联系我们' : $artInfo['seo_title'];
		$this->this_keywords = empty($artInfo['seo_keywords']) ? '' : $artInfo['seo_keywords'];
		$this->this_desc = empty($artInfo['seo_desc']) ? '' : $artInfo['seo_desc'];
		
		$this->display('contact_us');
	}
}