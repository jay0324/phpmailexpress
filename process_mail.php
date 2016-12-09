<?php
session_start();
ob_start();
ignore_user_abort(true); //允許背景處理機制
set_time_limit(0);	//不限制處理時間

/*require*/
//使用phpmailer
require_once("include/lib/phpmailer/PHPMailerAutoload.php");

//引用外部參數列表
$formID = (isset($_POST["formID"]))?$_POST["formID"]:"default";
include("include/lib/formsetup/".$formID.".php");

//set encoding
mb_internal_encoding('UTF-8');//設定為utf8編碼

//參數設定開始===============================================================================================================================
	
	/*application var*/
	class object {};
	$CFG = new object;
	$CFG->website    = $pm_siteurl;  //網站網址
	$CFG->sitename = $pm_sitename; //網站名稱
	$CFG->wwwroot     = "http://".$CFG->website; //網站完整路徑
	$CFG->dirroot     = dirname(__FILE__); //var_dump($CFG->dirroot);die();
	$CFG->active_mail_send = $pm_activeMailer; 	//啟用寄信
	$CFG->sender_mail_address = $pm_sendermail; //寄件信箱
	$CFG->sender_mail_name = $CFG->sitename; //寄件名稱
	$CFG->sender_mail_subject = $pm_adminMailTitle; //寄給管理者的信件標題
	$CFG->sender_mail_template = $CFG->dirroot.$pm_adminMailTmp."/index.html"; //寄給管理者的信件範本
	$CFG->sender_mail_template_image = $CFG->wwwroot.$pm_adminMailTmp."/images"; //寄給管理者的圖片URL
	$CFG->receiver_mail_subject = $pm_userMailTitle; //寄給使用者的信件標題
	$CFG->receiver_mail_template = $CFG->dirroot.$pm_userMailTmp."/index.html"; //寄給使用者的信件範本
	$CFG->receiver_mail_template_image = $CFG->wwwroot.$pm_userMailTmp."/images"; //寄給使用者的圖片URL
	$CFG->attachType = $pm_attachType; //附加檔案模式

	//語言版本
	$allowDomain = $pm_allowDomain; //允許的網域
	//$defaultlang = 'en';
	//$lang = (isset($_POST['lang']) && !empty($_POST['lang']))?$_POST['lang']:$defaultlang;
	$frm = $_POST; //表單送出的值
	
	$field_name = array();
	if (isset($_POST) && !empty($_POST)){
		foreach($_POST as $name => $content) {
		   array_push($field_name, $name);
		}
	}else{
		echo $pm_msg['no_data'];
		die();
	}

	$field_attachArry = (!empty($frm['submit-attachment']) && substr($frm['submit-attachment'],-1) != ',') ? $frm['submit-attachment'].',' : $frm['submit-attachment'];
	$field_attached = explode(',',$field_attachArry); //欲處理的附件欄位名稱
	$refer_title = (isset($frm[$frm['submit-refer-title']]))?$frm[$frm['submit-refer-title']]:$frm['submit-refer-title']; //資料庫用的名稱欄位
	$refer_mail = (isset($frm[$frm['submit-refer-mail']]))?$frm[$frm['submit-refer-mail']]:$frm['submit-refer-mail']; //資料庫用的信箱欄位
	//$refer_tel = $frm[$frm['submit-refer-tel']]; //資料庫用的電話欄位
	$upload_path = $CFG->dirroot."/files/attachment/"; //附加文件上傳自server的路徑
	$upload_path_http = $CFG->wwwroot."files/attachment/"; //附加文件上傳自server的路徑
	
	//排除檔案格式
	$allowDocType = $pm_allowDocType;

	$rename = $pm_rename_upload_file;

	$active_mail_send = $CFG->active_mail_send; //啟用寄信設定

	/*無資料庫的參數*/
	//$db = false; //是否有資料庫
	//針對語系提供送出的前端訊息
	$receiver = $pm_receiver;
	$receiver_name = $pm_receiver_name;
	$successPage = $pm_returnPage;
	$fail1 = $pm_msg['fail1'];
	$fail2 = $pm_msg['fail2'];
	$fail3 = $pm_msg['fail3'];
	$attach_msg = $pm_msg['attach'];
	$success = $pm_msg['success'];

	//check server
	if (in_array($_SERVER['HTTP_HOST'],$allowDomain)) {
		
	}else{
		fnAlert($pm_msg['auth'],$_SERVER['HTTP_REFERER']);
		die();
	}
//參數設定結束==================================================================================================================================
	
//自訂函式==================================================================================================================================	

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
	
//動作===============================================================================================================================================


			//1. 信件內容引用信件範本
				//取得收件人
				$main_name = $CFG->sender_mail_name;
				$main_sender = $CFG->sender_mail_address;

				//取得範本及相關信件設定值
				$manager_subject = $CFG->sender_mail_subject;
				$manager_template = useTmp($CFG->sender_mail_template, $CFG);
				$customer_subject = $CFG->receiver_mail_subject;
				$customer_template = useTmp($CFG->receiver_mail_template, $CFG);

			//2. 如果允許的話則寄信通知客戶
				if ($active_mail_send) {
					//用phpmailer來寄信 (寄給系統管理員)
						$mail = new PHPMailer;
						$mail->isSMTP(); // telling the class to use SMTP

						//gmail smtp
						// $mail->Host = 'smtp.gmail.com';
						// $mail->Port = 587;
						// $mail->SMTPSecure = 'tls';
						// $mail->SMTPAuth = true;
						// $mail->Username = "full mail address";
						// $mail->Password = "password";

						// set the timeout (seconds)
						$mail->Timeout = 10; 
						$mail->CharSet = 'UTF-8';
						$mail->setFrom($main_sender, "=?UTF-8?B?".base64_encode($main_name)."?="); //mail,name
						
						//收件人
						for ($i = 0; $i < count($receiver);$i++){
							$mail->addAddress($receiver[$i], "=?UTF-8?B?".base64_encode($receiver_name[$i])."?=");//mail,name
						}

						//檢查附件
						$upload_attach = '';
						$removeUpload = array();
						//var_dump($field_attachArry);die();
						$upload_attach_amt = count($field_attached)-1;
						if ($upload_attach_amt >= 1) {
							for ($j=0;$j<$upload_attach_amt;$j++) {
								if ($_FILES[$field_attached[$j]]['size'] > 0 && $_FILES[$field_attached[$j]]['error'] === UPLOAD_ERR_OK) {
									$file_get_upload_name=$_FILES[$field_attached[$j]]["name"];
									$path_parts = pathinfo($file_get_upload_name);
									$extension = ($path_parts['extension'] == null) ? end((explode(".", $file_get_upload_name))) : $path_parts['extension'];
									if (!in_array(strtolower($path_parts['extension']),$allowDocType)) {
										$upload_file_name = ($rename) ? time().'.'.$extension : $file_get_upload_name;
										$upload_file = $upload_path.$upload_file_name;
										if(move_uploaded_file($_FILES[$field_attached[$j]]["tmp_name"], $upload_file)){
											//附加文件
											switch($CFG->attachType){
												case 'server':
													$upload_attach .= $upload_file_name.',';
													$msg_mail_attached .= 'Attached: <a href="'.$upload_path_http.$upload_file_name.'">'.$upload_file_name.'</a><br>';
												break;
												default:
													$msg_mail_attached = $attach_msg;
													$mail->addAttachment($upload_file,$upload_file_name);
													array_push($removeUpload,$upload_file);
												break;
											}
										}else{
											echo fnAlert($fail3,$_SERVER['HTTP_REFERER']);
										}
									}else{
										echo fnAlert($fail3,$_SERVER['HTTP_REFERER']);
									}
								}
							}
						}

						$mail->Subject = "=?UTF-8?B?".base64_encode($manager_subject)."?=";
						$mail->isHTML(true);

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
						$msg_mail->tmpImgPath = $CFG->sender_mail_template_image;
						$msg_mail->attachFile = $msg_mail_attached;
						$mail_content = useTmpContent($manager_template, $msg_mail);

						//add mail content to body
						$mail->Body = eregi_replace("[\]",'',$mail_content);
						$mail->Priority = 1;
						$mail->AddCustomHeader("X-MSMail-Priority: High");
						$mail->AddCustomHeader("Importance: High");
						
						$deliveryState = $mail->send();
						$error_msg = $mail->ErrorInfo;

						//附加文件 (後續處理動作)
						if ($CFG->attachType == 'attachment') {
							//預設在附加到信件後就把server上資料刪除
							for ($h=0;$h<count($removeUpload);$h++){
								unlink($removeUpload[$h]);
							}
						}
						
						//用phpmailer來寄信 (寄給使用者)
						$mail2 = new PHPMailer;
						$mail2->isSMTP(); // telling the class to use SMTP
						$mail2->Timeout = 10; // set the timeout (seconds)
						$mail2->CharSet = 'UTF-8';
						$mail2->setFrom($main_sender, "=?UTF-8?B?".base64_encode($main_name)."?=");//mail,name
						$mail2->addAddress($refer_mail, "=?UTF-8?B?".base64_encode($refer_title)."?="); //mail,name
						$mail2->Subject = "=?UTF-8?B?".base64_encode($customer_subject)."?=";
						$mail2->isHTML(true);

						//b. 寄給詢問人
						//套用內容至範本
						$msg2_mail = new object();
						$msg2_mail->customerName = $refer_title;
						$msg2_mail->mailTime = date("Y-m-d H:i:s");
						$msg2_mail->wwwroot = $CFG->wwwroot;
						$msg_mail->tmpImgPath = $CFG->receiver_mail_template_image;
						$mail2_content = useTmpContent($customer_template, $msg2_mail);

						//add mail content to body
						$mail2->Body = eregi_replace("[\]",'',$mail2_content);
						$deliveryState2 = $mail2->send();
						$error_msg2 = $mail2->ErrorInfo;

						if ($deliveryState){
							echo fnAlert($success,$successPage);
						}else{
							echo fnAlert($fail1.'\n'.$error_msg.'\n'.$error_msg2,$_SERVER['HTTP_REFERER']);
						}
	 			}else{
					echo fnAlert($fail2,$_SERVER['HTTP_REFERER']);
	 			}

?>
