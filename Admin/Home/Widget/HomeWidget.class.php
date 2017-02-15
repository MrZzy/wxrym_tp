<?php
namespace Home\Widget;
use Think\Controller;
class HomeWidget extends Controller {
    /** 欢迎页取统计数量
     * @param $table - 表名
     * @param string $pref - 表前缀
     * @param $where - where条件
     */
    public function welCountInfo($table, $where){
        echo M($table,'wx_')->where($where)->count();
    }

    public  function  visitCount($met, $where='1=1'){
        $vcount = 0;
        if ($met=='ip'){
            $ip_list = M()->query('select distinct visit_ip from wx_visitinfo where '.$where);
            $vcount = (is_array($ip_list)) ? count($ip_list) : 0;
        }else if ($met=='pv'){
            $pv_list = M()->query('select sum(visit_count) as pv from wx_visitinfo where '.$where);
            $vcount = (is_array($pv_list)) ? $pv_list[0]['pv'] : 0;
        }
        echo $vcount;
    }
}
?>