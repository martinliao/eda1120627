<!DOCTYPE html>
<html lang="en">
<head>
	<title>
	<?php
		
		echo 'e大會員註冊';
		
	?>
	</title>
	<script src="<?php echo base_url().asset_new_url('new/js_newest/libs/jquery.min.js') ?>"></script>
</head>
<body>
<noscript>您的瀏覽器不支援JavaScript功能，若網頁功能無法正常使用時，請開啟瀏覽器 JavaScript狀態</noscript>
<noscript>Your browser does not support JavaScript. If the webpage function is not working properly, please open the browser JavaScript status.</noscript>
<form action="<?php echo base_url().'home/registered';?>" method="post" id="sendq">
<div class="corner" style="background-color:#DDD;margin:80px auto;width:360px;border:solid 1px silver" >
	<table cellspacing="0" cellpadding="0" style="line-height:16px;width:100%;border:none;margin:10px auto">
		<tr style="background-color:#547C96">
			<td colspan="3" style="height:2em;color:white;text-align:center"><h1>填寫註冊資訊</h1></td>
		</tr>
		
		<tr>
			<td style="height:5em;" width="30px"></td>
			<td>
				<lable>
					<span style="line-height:30px">身分證字號：<?php echo form_error('idno')?><input type="button" id="checkEyeIdno" value="顯示身分證"></span>
					<input type="password" id="idno" name="idno" title="身份證字號" style="width:300px;height:1.5em;margin 2px auto" value=""></input>
				</lable>
			</td>
			<td width="30px"></td>
		</tr>
		<tr>
			<td style="height:5em;"></td>
			<td>
				<lable>
					<span style="line-height:30px">姓名：<?php echo form_error('name')?></span>
					<input type="text" id="name" name="name" style="width:300px;height:1.5em;margin 2px auto" value=""></input>
				</lable>
			</td>
			<td></td>
		</tr>
		<tr>
			<td style="height:5em;"></td>
			<td>
				<lable>
					<span style="line-height:30px">E-mail(請填寫正確的mail，可以透過「忘記密碼」功能修改密碼)：<?php echo form_error('email')?></span>
					<input type="text" id="email" name="email" style="width:300px;height:1.5em;margin 2px auto" value=""></input>
				</lable>
			</td>
			<td></td>
		</tr>
		<tr>
			<td style="height:3em;"></td>
			<td style="padding-top:10px;text-align:center"><input type="button" id="reg" onclick="sendFun()" value="註冊"></input><input type="button" onclick="backHome()" value="返回臺北e大首頁"></input></td>
			<td></td>
		</tr>
	</table>
</div>
<div style="width: 360px;margin:80px auto">
	<?php
		
		echo '您好，如果您需要申請e大帳號，請花1至2分鐘閱讀以下事項：<br><br/>';
		echo '1.e大帳號無使用期限，可持續使用臺北e大服務。<br>';
		echo '2.當您成為台北通金質會員後，亦可取得e大帳號完成的學習紀錄。<br/>';
		echo '3.第一次登入密碼由系統指派，請妥善留存，學員第一次登入後可自行修改密碼。<br/>';
		echo '4.修改密碼路徑：登入後進入首頁＞我的課程＞學習紀錄＞主選單個人資料＞修改個人資料。<br>';
		echo '5.如果您忘記密碼，可點選e大帳號登入頁面的「忘記密碼」，系統會發送驗證信到您註冊時填寫的Email信箱。如您註冊時未填寫Email信箱無法收信，請洽客服專線。<br>';
		echo '6.有關註冊問題，歡迎洽客服專線協助，客服電話：02-29320212轉分機341 週一至週五 8:30至17:30，非上班時段請以網站留言。<a href="https://elearning.taipei/mpage/home/feedback/11"><前往網站留言></a>';			

	?>
</div>

</form>

</body>
</html>
<script type="text/javascript">
	$("#checkEyeIdno").click(function () {
        if($(this).val() == '顯示身分證'){
            $("#idno").attr('type', 'text');
            $(this).attr('value', '隱藏身分證');
        }else{
            $("#idno").attr('type', 'password');
            $(this).attr('value', '顯示身分證');
        }
    });

	function backHome(){
		location.href="https://elearning.taipei";
	}

	function sendFun(){
		var obj = document.getElementById('sendq');
		var id = document.getElementById('idno').value;
		var username = document.getElementById('name').value;
		var email = document.getElementById('email').value;

		if (checkIdno(id) == false){
			alert('身分證格式錯誤');
			return false;
		}

		if(username == ''){
			alert('姓名不能為空');
			return false;
		}
		
		if(email==''){
			alert('請輸入E-mail');
			return false;
		}else{
			var emailRegxp = /[\w-]+@([\w-]+\.)+[\w-]+/;
			if (emailRegxp.test(email) != true){
				alert('E-mail格式錯誤');
				return false;
			}
		}
		
		$('#reg').prop('disabled', true);
		obj.submit();
	}

	function checkIdno(id){
		var city = new Array(
		    1, 10, 19, 28, 37, 46, 55, 64, 39, 73, 82, 2, 11,
		    20, 48, 29, 38, 47, 56, 65, 74, 83, 21, 3, 12, 30
		)
		
		var city_number = new Array(
		   10, 11, 12, 13, 14, 15, 16, 17, 34, 18, 19, 20, 21,
		   22, 35, 23, 24, 25, 26, 27, 28, 29, 32, 30, 31, 33
		)

		id = id.toUpperCase();

		if (id.search(/^[A-Z](1|2|8|9|A|B|C|D)\d{8}$/i) == -1) {
		    return false;
		} else {
		    id = id.split('');

			if (['A', 'B', 'C', 'D'].indexOf(id[1]) != -1){
				id[1] = city_number[id[1].charCodeAt()-65] % 10;
				id[1] = id[1].toString();
			}

		    var total = city[id[0].charCodeAt(0) - 65];
		    for (var i = 1; i <= 8; i++) {
		        total += parseInt(id[i]) * (9 - i);
		    }

		    total += parseInt(id[9]);

		   	if(total % 10 != 0){
		    	return false;
		   	}

			return true;
		}		
	}
</script>