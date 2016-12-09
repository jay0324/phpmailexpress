<?php
	//啟用寄信
	$pm_activeMailer = true;		 					

	//允許的網域
	$pm_allowDomain = array('ds.manufacture.com.tw');		

	//網站網址
	$pm_siteurl = "ds.manufacture.com.tw/api/contact/";  					

	//網站名稱
	$pm_sitename = "CompanyName";  						

	//郵件寄件者信箱
	$pm_sendermail = 'info@ds.manufacture.com.tw'; 				

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

	//附加檔案方式 (附加方式: attachment:附加在信上 server:附加在服務器上)
	$pm_attachType = 'server'; 
	
	//收信人信箱 (陣列對應順序請跟名稱一樣)
	$pm_receiver = array('jay0324@manufacture.com.tw');
	
	//收信人名稱
	$pm_receiver_name = array($pm_sitename.' online-enquiries');
	
	//返回網址
	$pm_returnPage = 'http://ds.manufacture.com.tw/api/contact/contact.html';

	//訊息
	$pm_msg = array(
		'fail1' => 'Send mail unsuccessfully! please try it letter',
		'fail2' => 'Send mail function not enable!',
		'fail3' => 'Upload files not allowed!',
		'success' => 'Mail Send successfully!',
		'auth' => 'You DO NOT have permission to use this program!',
		'no_data' => 'You DO NOT provide correct data',
		'attach' => 'Please see attachements!'
	);

	//重新命名上傳檔案名稱
	$pm_rename_upload_file =  true;


?>