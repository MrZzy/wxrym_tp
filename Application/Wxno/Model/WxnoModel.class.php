<?php
namespace Wxno\Model;
use Think\Model;

class WxnoModel extends Model{
	public function readWxnoList($field = '',$where = ' wn.is_delete = 0 ',$order = '',$pagesize = 10){
		$params = I('request.');
		$field = empty($field) ? 'wn.*,wnct.cat_name' : $field;
		$where = empty($where) ? ' wn.is_delete = 0 ' : $where;
		$order = empty($order) ? ' wn.is_rec_pic desc,wn.is_recommend desc,wn.is_hot desc,wn.is_good desc,wn.wx_order desc,wn.add_time desc ' : $order;
		$cfg_page_size = C('FRONT_PAGE_SIZE');
		$this_page = (empty($params['p'])||($params['p']<1)) ? 1 : $params['p'];
		$page_size = (empty($pagesize)) ? (empty($cfg_page_size) ? 20 : $cfg_page_size) : $pagesize;
		$info = array(
			'p' => $params['p']
		);
		$wxno_srch = empty($params['wxno_srch']) ? '`' : $params['wxno_srch'];
		if ($params['wxno_srch']!='' && $params['wxno_srch']!=='`'){
			$info['wxno_srch'] = $params['wxno_srch'];
			$where .= " and wn.wx_name like '%".$params['wxno_srch']."%' or wn.wx_no like '%".$params['wxno_srch']."%'";
		}
		if ($params['cat_id']!=''){
			$info['cat_id'] = $params['cat_id'];
			$where .= " and wn.cat_id = ".$params['cat_id']." ";
		}
		$sel_type = $params['sel_type'];
		if ($sel_type=='new'){
			$where = 'wn.is_delete = 0';
			$order = "wn.add_time desc";
		}else if ($sel_type=='rec'){
			$where = 'wn.is_rec_pic = 1 or wn.is_recommend = 1 and wn.is_delete = 0';
			$order = "wn.is_rec_pic desc,wn.is_recommend desc,wn.add_time desc";
		}else if ($sel_type=='rec_i'){
			$where = 'wn.is_rec_pic = 1 and wn.is_delete = 0';
			$order = "wn.add_time desc";
		}else if ($sel_type=='hot'){
			$where = 'wn.is_hot = 1 and wn.is_delete = 0';
			$order = 'wn.add_time desc';
		}else if ($sel_type=='best'){
			$where = 'wn.is_good = 1 and wn.is_delete = 0';
			$order = 'wn.add_time desc';
		}else{
			$order = $order;
		}
		$where .= ' and wn.is_checked = 1 ';
		$row_count = $this->table('wx_wxno as wn')
			->join('wx_wxno_category as wnct on wn.cat_id = wnct.cat_id')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_wxno as wn')
			->field($field)
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
		// $Page->url = 'wx/'.$wxno_srch.'/p';
		$show = $Page->show();// 分页显示输出// 进行分页数据查询 注意limit方法的参数要使用Page类的属性 */
		return array("row_count"=>$row_count,"page"=>$show,"list"=>$row_list,"page_size"=>$page_size);
	}
}
?>