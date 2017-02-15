<?php
namespace Org\Tj;

class Graph{
	//init Image
	protected $MaxVal_Y = 0;
	protected $Count_X = 0;
	protected $Num_Group = 0;
	protected $DATA = array();
	protected $Field = array();
	protected $xAxis = array();
	function loadData($arr,$name){
		if( $this->Num_Group >= 5 ) return FALSE;
		$maxval = max($arr);
		$count0 = count($arr);
		if( $maxval > $this->MaxVal_Y ) $this->MaxVal_Y = $maxval;
		if( $count0 > $this->Count_X ) $this->Count_X = $count0;
		$this->DATA[] = $arr;
		$this->Field[] = $name;
		$this->Num_Group ++;
		return true;
	}
	function loadxAxis($arr){
		$this->xAxis = $arr;
	}
	function buildImg(){
		//echo $this->MaxVal_Y;exit;
		$fontSize = 8;
		$fontH = $fontSize*1.5;
		$g = 15 + 15 * $this->Num_Group;
		$e = 80;
		$h = 380;
		$w = max(380, $this->Count_X* $g + 25);
		$im = imagecreatetruecolor($w,$h);
		imagealphablending($im, false);
		imagesavealpha($im, true);
		$alpha = imagecolorallocatealpha($im,255,255,255,127);
		imagefill($im,0,0,$alpha);
		
		$black = imagecolorallocate($im,0,0,0);
		$color = array(
			imagecolorallocate($im, 38, 113, 178),
			imagecolorallocate($im, 227, 35, 34),
			imagecolorallocate($im, 140, 187, 38),
			imagecolorallocate($im, 241, 145, 1),
			imagecolorallocate($im, 109, 56, 137),
		);
		$fontfile = 'Public/poker/msyh.ttf';
		//循环数据
		foreach($this->DATA as $c => $arr){
			//图例
			imagefilledrectangle( $im , 10 , $c*20+4 , 20 , $c*20+14 , $color[$c] );
			imagettftext( $im, 12, 0, 25, $c*20+15, $black, $fontfile, $this->Field[$c]);
			foreach($arr as $k => $v){
				$h_val = $h - $e - round( ($h-$e-$fontH) * $v / $this->MaxVal_Y );
				$posx = $k*$g + $c*7 + 5 + 60;
				//echo $c.','.$v.','.$this->MaxVal_Y.','.$h_val.'<br/>';
				//柱形
				imagefilledrectangle( $im , $posx , $h_val , $posx+10 , $h-$e , $color[$c] );
				//柱形的值
				imagettftext( $im, $fontSize, 0, $posx-3+$c*10, $h_val-3, $color[$c], $fontfile, $v);
				if( $c == 0){
					//柱形的值
					//imagettftext( $im, $fontSize, 0, $posx-3, $h_val-3, $color[$c], $fontfile, $v);
					//横坐标文字
					imagettftext( $im, $fontSize+2, 60, $posx-18, $h-5, $black, $fontfile, $this->xAxis[$k]);
					//imagestringup( $im, 3 , $posx-3, $h, $this->xAxis[$k], $black);
				}
			}
		}
		header('Content-Type:image/png');
		imagepng($im);
		imagedestroy($im);
	}
}