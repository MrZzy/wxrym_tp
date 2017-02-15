<?php
namespace Config\Widget;
use Think\Controller;
class ConfigWidget extends Controller {
    public function menuParent($mid){
        $menuModel = new \Config\Model\MenuModel();
		$res_par = $menuModel->readMenuList('id = '.$mid);
		echo (count($res_par)>0) ? '<span class="label radius">'.$res_par[0]['menu_name'].'</span>' : '<span class="label label-success radius">顶级</span>';
    }
}
?>