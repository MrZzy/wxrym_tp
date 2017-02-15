<?php
namespace Wxno\Model;
use Think\Model;

class WxarticleModel extends Model{
	public function readArtList($where = '1 = 1'){
		// $res = $this->table('wx_wxarticle')
			// ->where($where)
			// ->order('art_order desc')
			// ->select();
		// return $res;
		$params = I('request.');
		$where = $where;
		$cfg_page_size = C('BACK_PAGE_SIZE');
		$this_page = (empty($params['p'])||($params['p']<1)) ? 1 : $params['p'];
		$page_size = (empty($cfg_page_size)) ? 20 : $cfg_page_size;
		$info = array(
			'p' => $params['p']
		);
		if ($params['date_end']!=''){
			$info['date_end'] = $params['date_end'];
		}
		if ($params['date_start']!=''){
			$info['date_start'] = $params['date_start'];
			$where .= " and t_a.add_time between '".$params['date_start']." 00:00:00' ";
			if ($params['date_end']!=''){
				$where .= "and '".$params['date_end']." 23:59:59'";
			}else{
				$where .= "and '".date('Y-m-d H:i:s',time())."'";
			}
		}
		if ($params['wx_name']!=''){
			$info['wx_name'] = $params['wx_name'];
			$where .= " and t_n.wx_name like '%".$params['wx_name']."%' or t_n.wx_no like '%".$params['wx_name']."%'";
		}
		if ($params['art_title']!=''){
			$info['art_title'] = $params['art_title'];
			$where .= " and t_a.art_title like '%".$params['art_title']."%'";
		}
		if ($params['cat_id']>0){
			$info['cat_id'] = $params['cat_id'];
			$where .= " and t_n.cat_id = ".$params['cat_id'];
		}
		switch ($params['sel_order']) {
		 	case '1':
		 		$order = 't_a.add_time asc';
		 		break;
	 		case '2':
		 		$order = 't_a.is_best desc,t_a.add_time desc';
		 		break;
	 		case '3':
		 		$order = 't_a.is_hot desc,t_a.add_time desc';
		 		break;
		 	default:
		 		$order = 't_a.add_time desc';
		 		break;
		 }
		$row_count = $this->table('wx_wxarticle as t_a')
			->join('wx_wxno as t_n on t_a.wx_id = t_n.wx_id')
			->join('wx_wxno_category as t_nt on t_n.cat_id = t_nt.cat_id')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_wxarticle as t_a')
			->field('t_a.*,t_n.wx_name,t_n.wx_no,t_nt.cat_name')
			->join('wx_wxno as t_n on t_a.wx_id = t_n.wx_id')
			->join('wx_wxno_category as t_nt on t_n.cat_id = t_nt.cat_id')
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
		return array("row_count"=>$row_count,"page"=>$show,"list"=>$row_list);
	}
	public function artSave($arr){
		if ($arr['aid']>0){
			$res = $this->table('wx_wxarticle')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_wxarticle')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function artDel($aid){
		$res = $this->table('wx_wxarticle')
			->where('aid = '.$aid)
			->delete();
		return $res;
	}
}
?>