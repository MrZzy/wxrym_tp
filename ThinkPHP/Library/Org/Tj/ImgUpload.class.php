<?php
namespace Org\Tj;

class ImgUpload{
	//基础透明图片
	private function createBaseImg($w, $h){
		$im = imagecreatetruecolor($w, $h);
		imagealphablending($im, false);
		imagesavealpha($im, true);
		$alpha = imagecolorallocatealpha($im,255,255,255,127);
		imagefill($im,0,0,$alpha);
		return $im;
	}
	//随机字符串
	private function randString($len=6){
		$str = '';
		for( $j=1;$j<=$len;$j++ ){
			$asc = mt_rand(97,122);
			$str .= chr($asc);
		}
		return $str;
	}
	//创建目录
	private function create_dirs($dir,  $mode=0777){
		return is_dir($dir) or ( $this->create_dirs(dirname($dir), $mode) and mkdir($dir, $mode) );
	}
	//去黑边
	private function cutBlack($file, $extIndex){
		switch( $extIndex ){
			case 1: //GIF
				$img = imagecreatefromgif($file);
				break;
			case 2: //JPG
				$img = imagecreatefromjpeg($file);
				break;
			case 3: //PNG
				$img = imagecreatefrompng($file);
				break;
			default:
				return FALSE;
		}
		$w = imagesx($img);
		$h = imagesy($img);
		$limit_w = $w * 0.1;
		$limit_h = $h * 0.1;
		$o_x = array($w*0.2, $w*0.3, $w*0.5, $w*0.8);
		$o_y = array($h*0.2, $h*0.3, $h*0.5, $h*0.8);
		$res = array();
		/** y轴，高度(h)方向 **/
		// 从上往下扫描
		$setp = ceil($h * 3/700);
		$p_y = -$setp;
		do{
			$p_y += $setp;
			$rgb = array(
				imagecolorat($img, $o_x[0], $p_y),
				imagecolorat($img, $o_x[1], $p_y),
				imagecolorat($img, $o_x[2], $p_y),
				imagecolorat($img, $o_x[3], $p_y),
			);
		}while($p_y<$limit_h && $rgb[0]==$rgb[1] && $rgb[1]==$rgb[2] && $rgb[2]==$rgb[3]);
		$res['top'] = $p_y;
		//从下往上扫描
		$p_y = $h +$setp;
		do{
			$p_y -= $setp;
			$rgb = array(
				imagecolorat($img, $o_x[0], $p_y),
				imagecolorat($img, $o_x[1], $p_y),
				imagecolorat($img, $o_x[2], $p_y),
				imagecolorat($img, $o_x[3], $p_y),
			);
		}while($p_y>$h-$limit_h && $rgb[0]==$rgb[1] && $rgb[1]==$rgb[2] && $rgb[2]==$rgb[3]);
		$res['bottom'] = $p_y;
		/** x轴，宽度(w)方向 **/
		// 从左往右扫描
		$setp = ceil($w * 3/700);
		$p_x = -$setp;
		do{
			$p_x += $setp;
			$rgb = array(
				imagecolorat($img, $p_x, $o_y[0]),
				imagecolorat($img, $p_x, $o_y[1]),
				imagecolorat($img, $p_x, $o_y[2]),
				imagecolorat($img, $p_x, $o_y[3]),
			);
		}while($p_x<$limit_w && $rgb[0]==$rgb[1] && $rgb[1]==$rgb[2] && $rgb[2]==$rgb[3]);
		$res['left'] = $p_x;
		//从右往左扫描
		$p_x = $w +$setp;
		do{
			$p_x -= $setp;
			$rgb = array(
				imagecolorat($img, $p_x, $o_y[0]),
				imagecolorat($img, $p_x, $o_y[1]),
				imagecolorat($img, $p_x, $o_y[2]),
				imagecolorat($img, $p_x, $o_y[3]),
			);
		}while($p_x>$w-$limit_w && $rgb[0]==$rgb[1] && $rgb[1]==$rgb[2] && $rgb[2]==$rgb[3]);
		$res['right'] = $p_x;
		//print_r($res);//exit;
		unset($img);
		return $res;
	}
	//  生成缩略图
	public function create_thumb($args){
		// 初始化参数
		$file      = $args['file'];
		$savePath  = $args['savePath'];
		$fileName  = $args['fileName']?$args['fileName']:NULL;
		$mode      = $args['mode']?$args['mode']:'zoom';
		$suffix    = $args['suffix']?$args['suffix']:'.thumb';
		$cutBlack  = $args['cutBlack']?$args['cutBlack']:0;
		$newW      = $args['newW']?$args['newW']:120;
		$newH      = $args['newH']?$args['newH']:120;
		$rotate    = $args['rotate']?$args['rotate']:0;
		//$newW = 100;
		//$newH = 100;
		if ($newW != $newH) return -1;
		$imgInfo = @getimagesize( $file );
		if( $imgInfo === false || $imgInfo[0]==0 || $imgInfo[1]==0 ){
			return FALSE;
		}
		$imgW = $imgInfo[0];
		$imgH = $imgInfo[1];
		// 切黑边运算
		$startX = $startY = 0;
		if( $cutBlack ){
			$blackInfo = $this->cutBlack($file, $imgInfo[2]);
			$startX = $blackInfo['left'];
			$startY = $blackInfo['top'];
			$imgW = $blackInfo['right'] - $blackInfo['left'];
			$imgH = $blackInfo['bottom'] - $blackInfo['top'];
		}
		// 反转长宽
		if( $rotate ){
			if( ($imgW < $imgH && $newW > $newH) || ($imgW > $imgH && $newW < $newH) ){
				$temp = $newW;
				$newW = $newH;
				$newH = $temp;
			}
		}
	
		if( $imgW<=$newW && $imgH<=$newH){
			$newW = $imgW;
			$newH = $imgH;
			$W = $imgW;
			$H = $imgH;
			$X = 0;
			$Y = 0;
		}else{
			if( $mode == 'cut' ){
				if( $imgW/$imgH > $newW/$newH ){
					$W = $newW * $imgH/$newH;
					$H = $imgH;
					$X = ($imgW - $W)/2;
					$Y = 0;
				}else{
					$W = $imgW;
					$H = $newH * $imgW/$newW;
					$X = 0;
					$Y = ($imgH - $H)/2;
				}
			}else{
				$W = $imgW;
				$H = $imgH;
				$X = 0;
				$Y = 0;
				
				$setW = $newW;
				$setH = $newH;
				if( $imgW/$imgH > $newW/$newH ){
					$newH = $newW * $imgH/$imgW;
				}else{
					$newW = $newH * $imgW/$imgH;
				}
				if( abs($setW-$newW) <= 2 ) $newW = $setW;
				if( abs($setH-$newH) <= 2 ) $newH = $setH;
			}
		}
		switch( $imgInfo[2] ){
			case 1: //GIF
				$big = imagecreatefromgif($file);
				$ext = 'gif';
				break;
			case 2: //JPG
				$big = imagecreatefromjpeg($file);
				$ext = 'jpg';
				break;
			case 3: //PNG
				$big = imagecreatefrompng($file);
				$ext = 'png';
				break;
			default:
				return FALSE;
		}
		$small = $this->createBaseImg($newW, $newH);
		if( function_exists('ImageCopyResampled') ){
			//ImageCopyResampled
			imagecopyresampled($small, $big, 0,0, $X+$startX, $Y+$startY, $newW,$newH, $W,$H);
		}else{
			//ImageCopyResized
			imagecopyresized($small, $big, 0,0, $X+$startX, $Y+$startY, $newW,$newH, $W,$H);
		}
		imagedestroy($big);
		if( $savePath ){
			/*
			if( !$fileName ){
				list($millisec, $timestamp) = explode(' ', microtime() );
				$fName_Pre = date('His', $timestamp) .'-'. substr($millisec, 2, 6);
				$YMD_DIR = date('Y-m-d/', $timestamp);
				//$ext = preg_replace("/.*?\.(\w+)$/i", "$1", $uploadName);
				$fileName = $YMD_DIR . $fName_Pre .'-'. $this->randString(6) .'.'. $ext;
				if( ! $this->create_dirs( $savePath . $YMD_DIR ) ) return FALSE;
			}
			*/
			if( !$fileName ){
				list($millisec, $timestamp) = explode(' ', microtime() );
				$fName_Pre = date('His', $timestamp) .'-'. substr($millisec, 2, 6);
				$fileName = $fName_Pre .'-'. $this->randString(6) .'.'. $ext;
				if( ! $this->create_dirs( $savePath ) ) return FALSE;
			}
			$fileName = preg_replace('/\.[a-z0-9]+$/i', $suffix.'.jpg', $fileName);
			if( imagepng( $small, $savePath.$fileName ) ){
				return array(
					'url' => $fileName,
					'd' => ($newW >= $newH)?7:8,
				);
			}
			return false;
		}else{
			return $small;
		}
	}
	public function upload_checkWH($args){
		// 初始化参数
		$file      = $args['file'];
		$savePath  = $args['savePath'];
		$needW      = $args['needW'] ? $args['needW'] : 120;
		$needH      = $args['needH'] ? $args['needH'] : 120;
		//$newW = 100;
		//$newH = 100;
		$imgInfo = @getimagesize( $file );
		if( $imgInfo === false || $imgInfo[0]==0 || $imgInfo[1]==0 ){
			return '图片信息获取失败，请检查';
		}
		switch( $imgInfo[2] ){
			case 1: //GIF
				$ext = 'gif';
				break;
			case 2: //JPG
				$ext = 'jpg';
				break;
			case 3: //PNG
				$ext = 'png';
				break;
			case 6: //BMP
				$ext = 'bmp';
				break;
			default:
				return '图片类型错误，仅支持gif、jpg、png、bmp类型';
		}
		$imgW = $imgInfo[0];
		$imgH = $imgInfo[1];
		if( $imgW != $needW || $imgH != $needH ) return '图片尺寸错误，宽度必须为'.$needW.'像素，高度必须为'.$needH.'像素';
		//生成随机值
		list($millisec, $timestamp) = explode(' ', microtime() );
		$fName_Pre = date('His', $timestamp) .'-'. substr($millisec, 2, 6);
		/*
		$YMD_DIR = date('Y-m-d/', $timestamp);
		//$ext = preg_replace("/.*?\.(\w+)$/i", "$1", $uploadName);
		$fileName = $YMD_DIR . $fName_Pre .'-'. $this->randString(6) .'.'. $ext;
		if( ! $this->create_dirs( $savePath . $YMD_DIR ) ) return FALSE;
		*/
		$fileName = $fName_Pre .'-'. $this->randString(6) .'.'. $ext;
		if( ! $this->create_dirs( $savePath ) ) return '文件路径创建失败，请检查';
		if( move_uploaded_file($file, $savePath.$fileName) ){
			return array(
				'url' => $fileName,
				'ext' => $ext,
			);
		}
		return '文件上传失败';
	}
}