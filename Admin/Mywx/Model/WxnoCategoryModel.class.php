<?php
namespace Mywx\Model;
use Think\Model;

class WxnoCategoryModel extends Model{
    public function readAllCatList($where = 'is_delete = 0'){
        $res = $this->table('wx_wxno_category')
            ->where($where)
            ->order('cat_order desc')
            ->select();
        return $res;
    }
	public function readMyCat(){
		$sql_mycat = "select * from wx_wxno_category where cat_id in (select cat_id from wx_wxno where wx_id in(".session('admin.wxids')."))";
		return M()->query($sql_mycat);
	}
	public function readCatList($where = 'is_delete = 0'){
		$my_cat = $this->readMyCat();
		if (empty($my_cat) || !is_array($my_cat)){
			return '';
			exit();
		}
		$mycat = '';
		foreach ($my_cat as $key => $val) {
			$mycat .= $val['cat_id'].',';
		}
		$mycat = substr($mycat, 0, strlen($mycat)-1);
		$sql_cat = "select * from wx_wxno_category where cat_id in (".$mycat.")";
		return M()->query($sql_cat);
	}
	public function catSave($arr){
		if ($arr['cat_id']>0){
			$res = $this->table('wx_wxno_category')
				->data($arr['data'])
				->where($arr['where'])
				->save();
			return $res;
		}else{
			$res = $this->table('wx_wxno_category')
				->data($arr['data'])
				->add();
			return $res;
		}
	}
	public function catDel($cat_id){
		$res = $this->table('wx_wxno_category')
			->where('cat_id = '.$cat_id)
			->delete();
		return $res;
	}
}
?>