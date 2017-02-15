<?php
namespace Webarticle\Model;
use Think\Model;

class WebnoticeModel extends Model{
	public function readNoticeList(){
		$params = I('request.');
		$where = ' 1 = 1 ';
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
			$where .= " and add_time between '".$params['date_start']." 00:00:00' ";
			if ($params['date_end']!=''){
				$where .= "and '".$params['date_end']." 23:59:59'";
			}else{
				$where .= "and '".date('Y-m-d H:i:s',time())."'";
			}
		}
		if ($params['n_author']!=''){
			$info['n_author'] = $params['n_author'];
			$where .= " and n_author like '%".$params['n_author']."%'";
		}
		if ($params['n_title']!=''){
			$info['n_title'] = $params['n_title'];
			$where .= " and n_title like '%".$params['n_title']."%'";
		}
		$row_count = $this->table('wx_webnotice')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_webnotice')
			->where($where)
			->order('add_time desc')
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
	public function readNoticeInfo($nid){
		$res = $this->table('wx_webnotice')
			->where('nid = '.$nid)
			->select();
		return $res[0];
	}
	public function noticeSave($arr){
		if ($arr['nid']>0){
			$res = $this->table('wx_webnotice')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_webnotice')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function noticeStop($arr){
		$is_delete = ($arr['met']=='stop') ? 1 : 0;
		$res = $this->table('wx_webnotice')
			->data(array('is_delete'=>$is_delete))
			->where('nid = '.$arr['nid'])
			->save();
		return $res;
	}
	public function noticeDel($nid){
		$res = $this->table('wx_webnotice')
			->where('nid = '.$nid)
			->delete();
		return $res;
	}
}
?>