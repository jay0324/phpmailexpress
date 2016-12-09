<?php
//validate code
//date: 20160908
//coder: JH

	session_start();
	mb_internal_encoding('UTF-8');//設定為utf8編碼

	//函式
	//更新驗證碼
	function fnRenewCaptcha(){
		$_SESSION['captcha'] = fnGenerateCODE(4);
	}

	//產生字串
	function fnGenerateCODE($set_length){
	    $alphabet = '1234567890'; //生成字串的字元
	    $length = $set_length;
	    $pass = array(); //remember to declare $pass as an array
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < $length; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); //turn the array into a string
	}

	//動作
	switch($_POST['v']) {
		case 'fcaptcha':
			if (isset($_POST['g']) && $_POST['g'] == $_SESSION['captcha']){
				echo 1; //驗證成功
			}else{
				echo 0; //驗證失敗
			}
		break;
		default:
			//重新抓取驗證碼
			fnRenewCaptcha();

			// Set the content-type
			header('Content-Type: image/png');

			// Create the image
			$im = imagecreatetruecolor(90, 35);

			// Create some colors
			$white = imagecolorallocate($im, 255, 255, 255);
			$grey = imagecolorallocate($im, 128, 128, 128);
			$black = imagecolorallocate($im, 0, 0, 0);
			imagefilledrectangle($im, 0, 0, 90, 35, $white);

			// The text to draw
			$text = $_SESSION['captcha'];
			// Replace path by your own font path
			$font = 'font.ttf';

			// Add the text
			imagettftext($im, 20, 0, 10, 30, $black, $font, $text);

			// Using imagepng() results in clearer text compared with imagejpeg()
			imagepng($im);
			imagedestroy($im);
		break;
	}

?>