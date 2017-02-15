<?php
namespace Wxno\Model;
use Think\Model;

class WxnoModel extends Model{
	public function readNoList($where = ' 1 = 1 '){
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
			$where .= " and t_n.add_time between '".$params['date_start']." 00:00:00' ";
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
		if ($params['wx_no']!=''){
			$info['wx_no'] = $params['wx_no'];
			$where .= " and t_n.wx_no like '%".$params['wx_no']."%'";
		}
		if ($params['cat_id']>0){
			$info['cat_id'] = $params['cat_id'];
			$where .= " and t_n.cat_id = ".$params['cat_id'];
		}
		switch ($params['sel_order']) {
		 	case '1':
		 		$order = 't_n.add_time asc';
		 		break;
	 		case '2':
		 		$order = 't_n.is_checked asc,t_n.add_time desc';
		 		break;
	 		case '3':
		 		$order = 't_n.is_rec_pic desc,t_n.add_time desc';
		 		break;
	 		case '4':
		 		$order = 't_n.is_recommend desc,t_n.add_time desc';
		 		break;
	 		case '5':
		 		$order = 't_n.is_good desc,t_n.add_time desc';
		 		break;
	 		case '6':
		 		$order = 't_n.is_hot desc,t_n.add_time desc';
		 		break;
		 	default:
		 		$order = 't_n.add_time desc,t_n.is_checked asc';
		 		break;
		 }
		$row_count = $this->table('wx_wxno as t_n')
			->join('wx_wxno_category as t_nt on t_n.cat_id = t_nt.cat_id')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_wxno as t_n')
			->field('t_n.*,t_nt.cat_name')
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
	public function readNoAll($where = 't_n.is_checked = 1 and t_n.is_delete = 0',$order = 't_n.add_time desc'){
		return $this->table('wx_wxno as t_n')
			->field('t_n.*,t_nt.cat_name')
			->join('wx_wxno_category as t_nt on t_n.cat_id = t_nt.cat_id')
			->where($where)
			->order($order)
			->select();
	}
	public function wxnoSave($arr){
		if ($arr['met']=='stop' || $arr['met']=='start' || $arr['met']=='save'){
			$res = $this->table('wx_wxno')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else if ($arr['met']=='del'){
			$res = $this->table('wx_wxno')
				->where('wx_id = '.$arr['wx_id'])
				->delete();
			return $res;
		}else if ($arr['met']=='add'){
			$res = $this->table('wx_wxno')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
}
?>