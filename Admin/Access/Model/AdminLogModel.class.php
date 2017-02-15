<?php
namespace Access\Model;
use Think\Model;

class AdminLogModel extends Model{
	public function readAdminLogList(){
		$params = I('request.');
		$where = ' 1 = 1 ';
		$cfg_page_size = C('BACK_PAGE_SIZE');
		$this_page = (empty($params['p'])||($params['p']<1)) ? 1 : $params['p'];
		$page_size = (empty($cfg_page_size)) ? 20 : $cfg_page_size;
		$info = array(
			'p' => $params['p']
		);
		if ($params['log_type']>0){
			$info['log_type'] = $params['log_type'];
			$where .= " and log_type = ".$params['log_type'];
		}
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
		if ($params['srch_text']!=''){
			$info['srch_text'] = $params['srch_text'];
			$where .= " and user_name like '%".$params['srch_text']."%'";
		}
		if ($params['srch_ip']!=''){
			$info['srch_ip'] = $params['srch_ip'];
			$where .= " and ip like '%".$params['srch_ip']."%'";
		}
		$row_count = $this->table('wx_admin_log')
			->where($where)
			->count();
			
		$row_list = $this->table('wx_admin_log')
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
}
?>