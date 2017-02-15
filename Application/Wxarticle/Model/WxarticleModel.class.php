<?php
namespace Wxarticle\Model;
use Think\Model;

class WxarticleModel extends Model{
	public function readWxartList($field = '',$where = ' wa.is_delete = 0 ',$order = '',$pagesize = 10){
		$params = I('request.');
		$field = empty($field) ? 'wa.*,wn.cat_id,wn.wx_no,wn.wx_name,wn.wx_logo,wn.wx_logo_thumb,wn.wx_img,wn.wx_img_thumb,wn.wx_desc,wn.add_time as wn_addtime,wn.seo_title as wn_seo_title,wn.seo_keywords as wn_seo_keywords,wn.seo_desc as wn_seo_desc,wnct.cat_name' : $field;
		$where = empty($where) ? ' wa.is_delete = 0 ' : $where;
		$order = empty($order) ? ' wa.is_best desc,wa.is_hot desc,wa.add_time desc ' : $order;
		$cfg_page_size = C('FRONT_PAGE_SIZE');
		$this_page = (empty($params['p'])||($params['p']<1)) ? 1 : $params['p'];
		$page_size = (empty($pagesize)) ? (empty($cfg_page_size) ? 20 : $cfg_page_size) : $pagesize;
		$info = array(
			'p' => $params['p']
		);
		/* if ($params['srch_text']!=''){
			$info['srch_text'] = $params['srch_text'];
			$where .= " and wa.art_title like '%".$params['srch_text']."%' or wa.art_desc like '%".$params['srch_text']."%'";
		} */
		if ($params['wx_no']!=''){
			$info['wx_no'] = $params['wx_no'];
			$where .= " and wn.wx_no = '".$params['wx_no']."' ";
		}
		if ($params['cat_id']!=''){
			$info['cat_id'] = $params['cat_id'];
			$where .= " and wn.cat_id = ".$params['cat_id']." ";
		}

		$sel_type = $params['sel_type'];
		if ($sel_type=='hot'){
			$where = 'wa.is_hot = 1 and wa.is_delete = 0';
			$order = 'wa.add_time desc';
		}else if ($sel_type=='best'){
			$where = 'wa.is_best = 1 and wa.is_delete = 0';
			$order = 'wa.add_time desc';
		}else{
			$order = $order;
		}

		$row_count = $this->table('wx_wxarticle as wa')
			->join('wx_wxno as wn on wa.wx_id = wn.wx_id')
			->join('wx_wxno_category as wnct on wn.cat_id = wnct.cat_id')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_wxarticle as wa')
			->field($field)
			->join('wx_wxno as wn on wa.wx_id = wn.wx_id')
			->join('wx_wxno_category as wnct on wn.cat_id = wnct.cat_id')
			->where($where)
			->order($order)
			->page($this_page,$page_size)
			->select();
		
		//分页
		unset($this_page);
		foreach ($info as $key=>$val){//拼接 分页条件
			if($val<>""){
				$Pagearr[$key]=$val;
			}
		}
		$Page = new \Think\Page($row_count,$page_size);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->parameter = $Pagearr;
		$show = $Page->show();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性 */
		return array("row_count"=>$row_count,"page"=>$show,"list"=>$row_list,"page_size"=>$page_size);
	}
}
?>