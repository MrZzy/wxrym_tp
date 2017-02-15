<?php
namespace Home\Widget;
use Think\Controller;
class HomeWidget extends Controller {
    public function menuChild($mid){
        $menuModel = new \Home\Model\MenuModel();
		$res_child = $menuModel->menuList(array('where'=>'parent_id = '.$mid));
		if (count($res_child)>0){
			echo 'dropdown';
		}else{
			echo '';
		}
    }
}
?>