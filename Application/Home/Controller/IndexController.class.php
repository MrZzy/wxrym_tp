<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
    public function index(){
		$url_cur['index'] = 'active';
		$wxartModel = new \Wxarticle\Model\WxarticleModel();	//文章
		$wxnoModel = new \Wxno\Model\WxnoModel();	//公众号
		
		//首页取最新文章
		$new_wxarticle = $wxartModel->readWxartList('','','wa.add_time desc',45);
		
		//首页推荐公众号
			//首页图片推荐
		$rec_pic_where = 'wn.is_rec_pic = 1 and wn.is_delete = 0';
		$rec_pic_order = 'wn.add_time desc';
		$rec_pic_page_size = 20;
		$rec_pic_wxno = $wxnoModel->readWxnoList('',$rec_pic_where,$rec_pic_order,$rec_pic_page_size);
		
		//首页公众号排行
			//首页推荐(非图片)
		$rec_where = 'wn.is_recommend = 1 and wn.is_delete = 0';
		$rec_order = 'wn.add_time desc';
		$rec_page_size = 8;
		$rec_wxno = $wxnoModel->readWxnoList('',$rec_where,$rec_order,$rec_page_size);
			//首页热门
		$hot_where = 'wn.is_hot = 1 and wn.is_delete = 0';
		$hot_order = 'wn.add_time desc';
		$hot_page_size = 8;
		$hot_wxno = $wxnoModel->readWxnoList('',$hot_where,$hot_order,$hot_page_size);
			//首页优秀
		// $yest_where = 'datediff(wn.add_time,now()) = -1 and wn.is_delete = 0';
		$good_where = 'wn.is_good = 1 and wn.is_delete = 0';
		$good_order = 'wn.add_time desc';
		$good_page_size = 8;
		$good_wxno = $wxnoModel->readWxnoList('',$good_where,$good_order,$good_page_size);
			//首页最新收录
		$new_where = 'wn.is_delete = 0';
		$new_order = 'wn.add_time desc';
		$new_page_size = 8;
		$new_wxno = $wxnoModel->readWxnoList('',$new_where,$new_order,$new_page_size);
		
		//首页随机取10条
		$rand_wxno = $wxnoModel->readWxnoList('','','rand()',10);
		
		//首页美文推荐
		$best_wxarticle = $wxartModel->readWxartList('','wa.is_best = 1 and wa.is_delete = 0','wa.add_time desc',21);
		
		$this->assign('url_cur',$url_cur);
		$this->assign('new_wxarticle',$new_wxarticle['list']);
		$this->assign('rec_pic_wxno',$rec_pic_wxno['list']);
		$this->assign('rec_wxno',$rec_wxno['list']);
		$this->assign('hot_wxno',$hot_wxno['list']);
		$this->assign('new_wxno',$new_wxno['list']);
		$this->assign('good_wxno',$good_wxno['list']);
		$this->assign('rand_wxno',$rand_wxno['list']);
		$this->assign('best_wxarticle',$best_wxarticle['list']);
		$this->display('index');
    }
	
}