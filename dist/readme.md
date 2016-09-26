#Email表單處理程式涵信件樣板
=========

#架構
------
```
files (權限可寫入)
|_attachement 		(信件附加文件的實體位置)
|_mail_template 	(信件範本的實體位置)
  |_style1			(信件範本的資料夾)
  |_style2			(信件範本的資料夾)
include
|_lib				
  |_phpmailer		(phpmailler的函式庫)
  |_formsetup		(form設定檔)
|_Scripts			
  |_validate		(表單驗證的JS)
contact.html 		(表單的靜態文件)
process_mail.php 	(伺服端信件處理程序)
```

#使用方式
------
先複製dist資料夾中的檔案到欲使用此套件的網站裡，使用方式分成三個部分

1. 設計表單，置入表單欄位名稱
2. 設定form的設定檔
3. 設計信件範本

============================
1.設計表單:
-----------

驗證表單Script和Css
-----------
```
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="include/Scripts/validate/validate.css" media="all">
<script type="text/javascript" src="include/Scripts/validate/validate.js"></script>
<script type="text/javascript">
    $(function() {
        //驗證表單
        $(".validateform").JFormValidator();
    })
</script>
```

表單格式
-----------
```
<form method="post" action="process_mail.php" class="validateform" enctype="multipart/form-data">
	<input type="hidden" name="formID" value="001"> <!--如果有多個表單一定要設定,value為設定檔檔名-->
    <input type="hidden" name="submit-refer-title" value="Company_field"> <!--表單中的公司或姓名欄位-->
    <input type="hidden" name="submit-refer-mail" value="Email_field"> <!--表單中的信箱欄位-->
    <input type="hidden" name="submit-attachment" value="Attached_file"> <!--表單中的附件欄位,一個以上用","隔開欄位名稱-->
	
	<!-- 自行設計欄位 -->

	<input type="text" name="validate" data-validate="captcha" /> <!-- 驗證碼欄位 -->
    <img class="captcha" src="process_mail.php?v=captcha" /> <!-- 驗證碼圖示 -->

</form>
```

2.設定form的設定檔:
-----------
設定檔在inlcude/lib/formsetup/中，如果表單的formID為001,就按照default.php的設定內容為範本，在此目錄下建立一個001.php的設定檔。
設定檔中的參數有註解說明各個設定值的作用

3.設計信件範本:
-----------
範本檔在files/mail_template/中，每個資料夾為一個信件範本，您可以依照自己的喜好來設計信件內容，其他從表單中取得的資料用{欄位名稱}來定義，如下:

表單欄位為
```
<label>我的欄位:</label> <input name="my_field_name" type="text" />
```
範本則可以設定成
```
我的欄位得到的值是{my_field_name}
```

特別送出給範本用的值:
```
{tmpImgPath} //範本圖片路徑 {tmpImgPath}/my_image.jpg ,然後把my_image.jpg放在範本的images的資料夾底下

{wwwroot} //網站的網址

{attachFile} //上傳檔案的資料連結
```

最後一定要記得把files的資料夾在server上面的權限改成可以寫入，這樣才可以上傳檔案