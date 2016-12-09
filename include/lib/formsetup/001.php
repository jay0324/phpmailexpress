<?php
	//啟用寄信
	$pm_activeMailer = true;		 					

	//允許的網域
	$pm_allowDomain = array('localhost:8080');			

	//網站網址
	$pm_siteurl = "localhost:8080";  					

	//網站名稱
	$pm_sitename = "CompanyName";  						

	//郵件寄件者信箱
	$pm_sendermail = 'info@webmaster.com'; 				

	//寄給管理者的信件標題
	$pm_adminMailTitle = $pm_sitename.' online-quote'; 	

	//寄給管理者的信件範本
	$pm_adminMailTmp = "/files/mail_template/style1";
	
	//寄給使用者的信件標題
	$pm_userMailTitle = $pm_sitename.' online-quote';

	//寄給使用者的信件範本
	$pm_userMailTmp = "/files/mail_template/style2"; 

	//排除上傳格式
	$pm_allowDocType = array('exe','dat','inc','php','js','html','xml','ade','adp','bas','bat',
		'chm','cmd','com','cpl','crt','hlp','hta','inf','ins','isp','jse','lnk','wsh');

	
	//收信人信箱 (陣列對應順序請跟名稱一樣)
	$pm_receiver = array('stanley@msj.com.tw');
	
	//收信人名稱
	$pm_receiver_name = array($pm_sitename.' online-enquiries');
	
	//返回網址
	$pm_returnPage = 'http://localhost:8080/';

	//訊息
	$pm_msg = array(
		'fail1' => 'Send mail unsuccessfully! please try it letter',
		'fail2' => 'Send mail function not enable!',
		'fail3' => 'Upload files not allowed!',
		'success' => 'Mail Send successfully!',
		'auth' => 'You DO NOT have permission to use this program!',
		'no_data' => 'You DO NOT provide correct data'
	);


?>