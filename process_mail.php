<?php
session_start();
ob_start();
ignore_user_abort(true); //允許背景處理機制
set_time_limit(0);	//不限制處理時間

/*require*/
//使用phpmailer
require_once("include/lib/phpmailer/PHPMailerAutoload.php");

//引用外部參數列表
include("include/lib/setup.php");

//set encoding
mb_internal_encoding('UTF-8');//設定為utf8編碼

//參數設定開始===============================================================================================================================
	
	/*application var*/
	class object {};
	$CFG = new object;
	$CFG->website    = $pm_siteurl;  //網站網址
	$CFG->sitename = $pm_sitename; //網站名稱
	$CFG->wwwroot     = "http://".$CFG->website; //網站完整路徑
	$CFG->dirroot     = dirname(__FILE__);
	$CFG->active_mail_send = $pm_activeMailer; 	//啟用寄信
	$CFG->sender_mail_address = $pm_sendermail; //寄件信箱
	$CFG->sender_mail_name = $CFG->sitename; //寄件名稱
	$CFG->sender_mail_subject = $pm_adminMailTitle; //寄給管理者的信件標題
	$CFG->sender_mail_template = $pm_adminMailTmp; //寄給管理者的信件範本
	$CFG->receiver_mail_subject = $pm_userMailTitle; //寄給使用者的信件標題
	$CFG->receiver_mail_template = $pm_userMailTmp; //寄給使用者的信件範本

	//語言版本
	$allowDomain = $pm_allowDomain; //允許的網域
	$defaultlang = 'en';
	$lang = (isset($_POST['lang']) && !empty($_POST['lang']))?$_POST['lang']:$defaultlang;
	$frm = $HTTP_POST_VARS; //表單送出的值
	
	$field_name = array();
	foreach($_POST as $name => $content) {
	   array_push($field_name, $name);
	}

	$field_attachArry = (!empty($frm['submit-attachment']) && substr($frm['submit-attachment'],-1) != ',') ? $frm['submit-attachment'].',' : $frm['submit-attachment'];
	$field_attached = explode(',',$field_attachArry); //欲處理的附件欄位名稱
	$refer_title = $frm[$frm['submit-refer-title']]; //資料庫用的名稱欄位
	$refer_mail = $frm[$frm['submit-refer-mail']]; //資料庫用的信箱欄位
	$refer_tel = $frm[$frm['submit-refer-tel']]; //資料庫用的電話欄位
	$upload_path = $CFG->dirroot."/files/attachment/"; //附加文件上傳自server的路徑
	$upload_path_http = $CFG->wwwroot."/files/attachment/"; //附加文件上傳自server的路徑
	
	//排除檔案格式
	$allowDocType = $pm_allowDocType;

	$rename = false;

	$active_mail_send = $CFG->active_mail_send; //啟用寄信設定

	/*無資料庫的參數*/
	$db = false; //是否有資料庫
	//針對語系提供送出的前端訊息
	switch($lang) {
		default:
			$receiver = $pm_receiver;
			$receiver_name = $pm_receiver_name;
			$successPage = $pm_returnPage;
			$fail1 = $pm_msg['fail1'];
			$fail2 = $pm_msg['fail2'];
			$fail3 = $pm_msg['fail3'];
			$success = $pm_msg['success'];
		break;
	}

	//check server
	if ($_SERVER['HTTP_HOST']=='www.'.$allowDomain || $_SERVER['HTTP_HOST']==$allowDomain){
	}else{
		fnAlert($pm_msg['auth'],$_SERVER['HTTP_REFERER']);
		die();
	}
//參數設定結束==================================================================================================================================
	
//自訂函式==================================================================================================================================	

	//更新驗證碼
	function fnRenewCaptcha(){
		$_SESSION['captcha'] = fnGenerateCODE(4);
	}

	//simple html template	
	function useTmp($tmpPath, $replaceString){
		$output = file_get_contents($tmpPath);
		foreach ($replaceString as $key => $value) {
			$output = str_replace('{'.$key.'}',$value,$output);
		}
		return $output;
	}

	//simple html template for mail
	function useTmpContent($tmpContent, $replaceString){
		$output = $tmpContent;
		foreach ($replaceString as $key => $value) {
			$output = str_replace('{'.$key.'}',$value,$output);
		}
		return $output;
	}

	//訊息
	function fnAlert($str,$returnStr){
		return '<script>alert(\''.$str.'\');window.location=\''.$returnStr.'\';</script>';
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
	
//動作===============================================================================================================================================
	switch($_REQUEST['v']) {
		case 'fcaptcha':
			if (isset($_REQUEST['g']) && $_REQUEST['g'] == $_SESSION['captcha']){
				echo 1; //驗證成功
			}else{
				echo 0; //驗證失敗
			}
		break;
		case 'captcha':
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
			$font = $CFG->dirroot.'/include/Scripts/validate/font.ttf';

			// Add the text
			imagettftext($im, 20, 0, 10, 30, $black, $font, $text);

			// Using imagepng() results in clearer text compared with imagejpeg()
			imagepng($im);
			imagedestroy($im);
		break;
		default:
			//var_dump($field_name);die();

			//送出前先更新驗證碼,可以防止返回寄信
			fnRenewCaptcha();

			//1. 信件內容引用信件範本
				//取得收件人
				if ($db) {
					$receiver_sql = "SELECT * FROM contact_group WHERE verify = -1 AND lang = '$lang'";
					$receiver_query = db_query($receiver_sql);
					$receiver = array();
					$receiver_name = array();
					while ($receiver_list = mysql_fetch_array($receiver_query)){
						array_push($receiver, $receiver_list['email']);
						array_push($receiver_name, $receiver_list['name']);
						$target .= $receiver_list['id'].",";
						if ($receiver_list['sender'] == 1) {
							$main_name = $receiver_list['name'];
							$main_sender = $receiver_list['email'];
						}
					}
				}else{
					$main_name = $CFG->sender_mail_name;
					$main_sender = $CFG->sender_mail_address;
				}

				//取得範本及相關信件設定值
				if ($db) {
					$query = db_query("SELECT * FROM contact_setting WHERE lang = '$lang' AND id = 'setting'");
					$res = mysql_fetch_array($query);
					$manager_subject = $res['manager_subject'];
					$manager_template = $res['manager_template'];
					$customer_subject = $res['customer_subject'];
					$customer_template = $res['customer_template'];
				}else{
					$manager_subject = $CFG->sender_mail_subject;
					$manager_template = useTmp($CFG->sender_mail_template, $CFG);
					$customer_subject = $CFG->receiver_mail_subject;
					$customer_template = useTmp($CFG->receiver_mail_template, $CFG);
				}

				//檢查附件
				$upload_attach = '';
				$upload_attach_amt = count($field_attached)-1;
				if ($upload_attach_amt >= 1) {
					for ($j=0;$j<$upload_attach_amt;$j++) {
						if ($_FILES[$field_attached[$j]] > 0) {
							$file_get_upload_name=$_FILES[$field_attached[$j]]["name"];
							$path_parts = pathinfo($file_get_upload_name);
							$extension = ($path_parts['extension'] == null) ? end((explode(".", $file_get_upload_name))) : $path_parts['extension'];
							if (!in_array(strtolower($path_parts['extension']),$allowDocType)) {
								$upload_file_name = ($rename) ? time().'.'.$extension : $file_get_upload_name;
								$upload_file = $upload_path.$upload_file_name;
								if(copy($_FILES[$field_attached[$j]]["tmp_name"], $upload_file)){
									//var_dump($field_attached[$j]);die();
									$upload_attach .= $upload_file_name.',';
									$msg_mail_attached .= 'Attached: <a href="'.$upload_path_http.$upload_file_name.'">'.$upload_file_name.'</a><br>';
								}
							}else{
								echo fnAlert($fail3,$_SERVER['HTTP_REFERER']);
							}
						}
					}
				}

				//var_dump($msg_mail_attached);die();

				//a. 寄給系統管理員					
				//套用內容至範本
				$msg_mail = new object();

				for($k=0;$k < count($field_name)-1;$k++) {
					if (is_array($frm[$field_name[$k]])) {
						for($i=0;$i<count($frm[$field_name[$k]]);$i++){
							$msg_mail->$field_name[$k] .= $frm[$field_name[$k]][$i].",";
						}
					}else{
						$msg_mail->$field_name[$k] .= nl2br($frm[$field_name[$k]]);
					}
				}
				$msg_mail->mailTime = date("Y-m-d H:i:s");
				$msg_mail->wwwroot = $CFG->wwwroot;
				$msg_mail->attachFile = $msg_mail_attached;
				$mail_content = useTmpContent($manager_template, $msg_mail);

				//var_dump($mail_content);die();
					
				//b. 寄給詢問人
				//套用內容至範本
				$msg2_mail = new object();
				$msg2_mail->customerName = $refer_title;
				$msg2_mail->mailTime = date("Y-m-d H:i:s");
				$msg2_mail->wwwroot = $CFG->wwwroot;
				$mail2_content = useTmpContent($customer_template, $msg2_mail);

			//2. 執行寄送信件前先記錄到資料庫,可以避免漏收信件
			if ($db) {
				$sql = "INSERT INTO contact (
									target,
									name,
									email,
									tel,
									content,
									attachment,
									date
									) VALUES (
									'".$target."'
									,'".$refer_title."'
									,'".$refer_mail."'
									,'".$refer_tel."'
									,'".$mail_content."'
									,'".$upload_attach."'
									,'".date('Y-m-d h:i:s')."'
									)";
				$qid = db_query($sql);
			}
				
			//3. 如果允許的話則寄信通知客戶
				if ($active_mail_send) {
					//用phpmailer來寄信 (寄給系統管理員)
						$mail = new PHPMailer;
						$mail->isSMTP(); // telling the class to use SMTP
						$mail->Timeout = 10; // set the timeout (seconds)
						$mail->CharSet = 'UTF-8';
						$mail->setFrom($main_sender, "=?UTF-8?B?".base64_encode($main_name)."?="); //mail,name
						
						//收件人
						for ($i = 0; $i < count($receiver);$i++){
							$mail->addAddress($receiver[$i], "=?UTF-8?B?".base64_encode($receiver_name[$i])."?=");//mail,name
						}

						$mail->Subject = "=?UTF-8?B?".base64_encode($manager_subject)."?=";
						$mail->isHTML(true);
						$mail->Body = eregi_replace("[\]",'',$mail_content);
						$mail->Priority = 1;
						$mail->AddCustomHeader("X-MSMail-Priority: High");
						$mail->AddCustomHeader("Importance: High");

						//附加文件
						/*if (count($field_attached) > 1) {
							for ($j=0;$j<count($field_attached)-1;$j++) {
								$mail->AddAttachment($_FILES[$field_attached[$j]]['tmp_name'][0]);
							}
						}*/

						$deliveryState = $mail->send();
						$error_msg = $mail->ErrorInfo;
						
						//用phpmailer來寄信 (寄給使用者)
						$mail2 = new PHPMailer;
						$mail2->isSMTP(); // telling the class to use SMTP
						$mail2->Timeout = 10; // set the timeout (seconds)
						$mail2->CharSet = 'UTF-8';
						$mail2->setFrom($main_sender, "=?UTF-8?B?".base64_encode($main_name)."?=");//mail,name
						$mail2->addAddress($refer_mail, "=?UTF-8?B?".base64_encode($refer_title)."?="); //mail,name
						$mail2->Subject = "=?UTF-8?B?".base64_encode($customer_subject)."?=";
						$mail2->isHTML(true);
						$mail2->Body = eregi_replace("[\]",'',$mail2_content);
						$deliveryState2 = $mail2->send();
						$error_msg2 = $mail2->ErrorInfo;

						if ($deliveryState){
							if ($db) {
								header("location: contact_success.php?lang=".$lang);
							}else{
								echo fnAlert($success,$successPage);
							}
						}else{
							if ($db) {
								system_log($error_msg); //紀錄log
								system_log($error_msg2); //紀錄log
								header("location: contact_fail.php?lang=".$lang);
							}else{
								echo fnAlert($fail1,$_SERVER['HTTP_REFERER']);
							}
						}
				}else{
					if ($db) {
						system_log('信件寄信功能未啟用!'); //紀錄log
						header("location: contact_fail.php?lang=".$lang);
					}else{
						echo fnAlert($fail2,$_SERVER['HTTP_REFERER']);
					}
				}
		break;
	}

?>