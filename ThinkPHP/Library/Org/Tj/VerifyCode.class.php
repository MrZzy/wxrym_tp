<?php
namespace Org\Tj;
class VerifyCode{
	private $code;//验证码
	private $pk_list;//5张扑克
	private $pk_most;//最多的花色
	private function randCode(){
		$pk_count = array(0,0,0,0);
		$pk_number = array('','','','');
		$pk = array();
		for($j=0;$j<5;$j++){
			$h = -1;
			$off=false;
			do{	
				$h = mt_rand(0,3);	
				$s1=0;
				foreach ($pk as $k=>$val){
					if ($val["h"]==$h){
						$s1++;
						if ($s1==2){
							$j--;
							$off=true;
						}
					}
				}
			}
			while( $pk_count[$h] >= 3);
			if ($off==false){
				$c = mt_rand(10,99);//显示的数字 
				$pk_count[$h]++;
				$pk_number[$h] .= $c;
				$pk[] = array(
					'h' => $h,
					'c' => $c,
				);
			}
		}
		$pk_most = -1;
		$pk_max = max($pk_count);
		$rows=0;
		$rowsx=0;$rowsy=0;
		for($j=0;$j<=5;$j++){//$pk.h
			if ($pk_count[$j]==1){
				$pk_most = $j;
				break;
			}
		}	
		$this->pk_list = $pk;
		$this->pk_most = $pk_most;
		$this->code = $pk_number[$pk_most];
		session_start();
		$_SESSION['vCode'] = $this->code;
	}
	private function createBaseImg($w, $h){
		$im = imagecreatetruecolor($w, $h);
		imagealphablending($im, false);
		imagesavealpha($im, true);
		$alpha = imagecolorallocatealpha($im,255,255,255,127);
		imagefill($im,0,0,$alpha);
		return $im;
	}
	public function buildImg(){
		$this->randCode();
		$arr = array('红心','黑桃','方块','梅花');
		$pkName = '依次输入 '.$arr[$this->pk_most].' 上的数字';
		//50x50
		//250x50
		$hs = imagecreatefromgif('Public/poker/poker.gif');
		//初始化一张图片
		$im = $this->createBaseImg(250,70);
		$white = imagecolorallocate($im,255,255,255);
		$black = imagecolorallocate($im,0,0,0);
		for($j=0;$j<5;$j++){
			$hsx = $this->pk_list[$j]['h']*50;
			$posx = $j*50;
			//echo $xx,'/',$posx,'<br/>';
			imagecopy( $im , $hs , $posx , 0 , $hsx , 0 , 50 , 50 );
			imagestring( $im, 5, $posx+17, 17, $this->pk_list[$j]['c'], $white);
		}
		imagedestroy($hs);
		$fontfile = 'Public/poker/msyh.ttf';
		imagettftext ($im, 14, 0, 33, 65, $black, $fontfile, $pkName);//x=33 
		//输出
		header('Content-Type:image/png');
		imagepng($im);
		imagedestroy($im);
	}
	
	public function simpleImg(){
		//验证图片生成
		$im = $this->createBaseImg(60,20);
		$fontfile = 'Public/poker/msyh.ttf';
		$black = imagecolorallocate($im,0,0,0);
		//验证码序列
		$baseStr = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
		$len = strlen($baseStr);
		$code = '';
		for($i=0;$i<4;$i++){
			$xb = mt_rand(0,$len-1);
			$str = substr($baseStr, $xb, 1);
			$code .= $str;
			imagettftext ($im, 11, 0, $i*14+2, 15, $black, $fontfile, $str);//x=33 
			
		}
		$this->code = strtolower($code);
		//写入sisson
		session_start();
		$_SESSION['vCode'] = $this->code;
		//输出
		header('Content-Type:image/png');
		imagepng($im);
		imagedestroy($im);
	}
}