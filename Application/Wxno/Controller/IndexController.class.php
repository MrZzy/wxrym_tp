<?php
namespace Wxno\Controller;
use Think\Controller;
class IndexController extends Controller {
	
	var $Init;
	function _initialize(){
		$this->Init = new \Extend\Init\Init();
		$this->Init->init();
	}
	
    public function wxnoList(){
		$url_cur['wxno'] = 'active';
		$this->assign('url_cur',$url_cur);
		$params = I('request.');
		// $params = I('post.');
		$this->l_type = 'wxno';
		if (isset($params['cat_id']) && $params['cat_id']!='' && !is_numeric($params['cat_id'])){
			// $this->error('您的操作导致任意门出故障了！');
			// $this->redirect('/wxc');
            $this->redirect('/404_not_found.php');
		}
		if ($params['wxno_srch']!='' && $params['wxno_srch']!=='`'){	//用于分页刷新
			$this->wxno_srch = $params['wxno_srch'];
			$this->this_title = $params['wxno_srch'].' _ 公众号搜索结果';
			if (is_numeric($params['cat_id']) && $params['cat_id']>0){
				$wxnoCatModel = new \WxnoCategory\Model\WxnoCategoryModel();
				$cat_list = $wxnoCatModel->readCatList(array('where'=>'cat_id = '.$params['cat_id'].' and is_delete = 0'));
                if (!is_array($cat_list) || count($cat_list)<=0){
                    $this->redirect('/404_not_found.php');
                }
				$this->cat_id = $params['cat_id'];
				$this->cat_name = $cat_list[0]['cat_name'];
				$this->this_title = $params['wxno_srch'].' _ '.$this->cat_name.' _ 公众号列表';
			}
		}else{
			$this->this_title = '';
		}
		
		//公众号列表
		$wxno_page_size = '';
		$wxno_list = D('Wxno')->readWxnoList('','','',$wxno_page_size);
		
		$this->assign('wxno_list',$wxno_list['list']);
		$this->assign('row_count',$wxno_list['row_count']);
		$this->assign('page',$wxno_list['page']);
		$this->assign('page_size',$wxno_list['page_size']);
		$this->display('wxno_list');
    }
	public function wxnoListByCat(){
		$url_cur['wxno'] = 'active';
		$this->assign('url_cur',$url_cur);
		$params = I('request.');
		// $params = I('post.');
		$this->l_type = 'wxcat';
		if (isset($params['cat_id']) && !is_numeric($params['cat_id'])){
			// $this->error('您的操作导致任意门出故障了！');
			// $this->redirect('/wx');
            $this->redirect('/404_not_found.php');
		}
		if (is_numeric($params['cat_id']) && $params['cat_id']>0){
			$catInfo = M('wxno_category','wx_')->where('cat_id = '.$params['cat_id'].' and is_delete = 0')->find();
            if (!is_array($catInfo) || count($catInfo)<=0){
                $this->redirect('/404_not_found.php');
            }
			$this->cat_id = $params['cat_id'];
			$this->cat_name = $catInfo['cat_name'];
			$this->this_title = empty($catInfo['seo_title']) ? $this->cat_name.' _ 公众号列表' : $catInfo['seo_title'];
			$this->this_keywords = empty($catInfo['seo_keywords']) ? '' : $catInfo['seo_keywords'];
			$this->this_desc = empty($catInfo['seo_desc']) ? '' : $catInfo['seo_desc'];
			if ($params['wxno_srch']!='' && $params['wxno_srch']!=='`'){	//用于分页刷新
				$this->wxno_srch = $params['wxno_srch'];
				$this->this_title = $this->cat_name.' _ '.$params['wxno_srch'].' _ 公众号列表';
			}
		}
		
		//公众号列表
		$wxno_page_size = '';
		$wxno_list = D('Wxno')->readWxnoList('','','',$wxno_page_size);
		
		$this->assign('wxno_list',$wxno_list['list']);
		$this->assign('row_count',$wxno_list['row_count']);
		$this->assign('page',$wxno_list['page']);
		$this->assign('page_size',$wxno_list['page_size']);
		$this->display('wxno_list');
    }
	public function wxnoListBySelType(){
		$url_cur['wxno'] = 'active';
		$this->assign('url_cur',$url_cur);
		$params = I('request.');
		$this->sel_type = $params['sel_type'];
		$title = '';
		switch ($params['sel_type']){
			case 'new':
				$title = '任意门最新收录';
				break;
			case 'rec':
				$title = '任意门推荐';
				break;
			case 'rec_i':
				$title = '任意门特别推荐';
				break;
			case 'hot':
				$title = '热门公众号';
				break;
			case 'best':
				$title = '任意门优选';
				break;
			default:
				$title = '微信任意门';
				break;
		}
		$this->this_title = $title.' _ 公众号列表';
		
		//公众号列表
		$wxno_page_size = '';
		$wxno_list = D('Wxno')->readWxnoList('','','',$wxno_page_size);
		
		$this->assign('wxno_list',$wxno_list['list']);
		$this->assign('row_count',$wxno_list['row_count']);
		$this->assign('page',$wxno_list['page']);
		$this->assign('page_size',$wxno_list['page_size']);
		$this->display('wxno_list');
	}
	public function wxnoPageList(){
		$params = I('post.');
		$wxno_page_size = $params['page_size'];
		$wxno_list = D('Wxno')->readWxnoList('','','',$wxno_page_size);
		$append_html = '';
		foreach($wxno_list['list'] as $k=>$v){
			$append_html .= 
			"<div class=\"history-grids wxno_list_div\">
				<div class=\"col-md-12 history-grid\">
					<a href=\"".$do_main."/wxart/".$v['wx_no']."\" title=\"".$v['wx_name']."\">
					<div class=\"history-left\">
						<img src=\"".C('GET_FILE_PATH')."wxno/".$v['wx_logo_thumb']."\" onerror=\"javascript:this.src='".C('FRONT_RES_PATH')."images/no_pic_thumb.png'\" />
					</div>
					</a>
					<div class=\"history-right\">
						<h4>".($v['is_rec_pic']==1 ? '<b title="任意门特别推荐">[特]</b>' : '').($v['is_recommend']==1 ? '<b title="任意门推荐">[荐]</b>' : '').($v['is_good']==1 ? '<b title="任意门优选">[优]</b>' : '').($v['is_hot']==1 ? '<b title="热门公众号">[热]</b>' : '')."<a href=\"".$do_main."/wxart/".$v['wx_no']."\" title=\"".$v['wx_name']."\">".$v['wx_name']."&nbsp;（".$v['wx_no']."）</a></h4>
						<p><a href=\"".$do_main."/wxart/".$v['wx_no']."\">".subtext($v['wx_desc'],45)."</a></p>
					</div>
				</div>
			</div>";
			if ($k%2 != 0){
				$append_html .= "<div class=\"clearfix\"></div>";
			}
		}
		echo $append_html;
	}
	
}