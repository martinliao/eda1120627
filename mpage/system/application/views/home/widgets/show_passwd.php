<!DOCTYPE html>
<html lang="en">
<head>
	<title>會員註冊</title>
</head>
<body>
	<p style="font-size:40px;"><strong style="color:blue;">您的密碼為：<?php echo $passwd; ?></strong> (預設密碼為小寫英文6碼+數字1碼組成)</p>
	<p><strong style="color:red; font-size:40px">【請妥善留存本頁的登入密碼，網頁關閉後無法重新開啟本頁】</strong></p>
	<p>第一次登入密碼由系統指派，如您有修改密碼需求，請於登入後至首頁＞我的課程＞學習紀錄＞個人資料頁面中，自行修改登入密碼。</p>
	<a href="<?php echo base_url().'home/login_other'?>" title="返回登入畫面">返回登入畫面</a>
</body>
</html>