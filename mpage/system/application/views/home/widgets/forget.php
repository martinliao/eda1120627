<!DOCTYPE html>
<html lang="en">
<head>
	<title>
	<?php
		
		echo '忘記密碼';
		
	?>
	</title>
	<script src="<?php echo base_url().asset_new_url('new/js_newest/libs/jquery.min.js') ?>"></script>
</head>
<body>
<noscript>您的瀏覽器不支援JavaScript功能，若網頁功能無法正常使用時，請開啟瀏覽器 JavaScript狀態</noscript>
<noscript>Your browser does not support JavaScript. If the webpage function is not working properly, please open the browser JavaScript status.</noscript>
<form action="<?php echo base_url().'home/forget';?>" method="post" id="sendq">
<div class="corner" style="background-color:#DDD;margin:80px auto;width:360px;border:solid 1px silver" >
	<table cellspacing="0" cellpadding="0" style="line-height:16px;width:100%;border:none;margin:10px auto">
		<tr style="background-color:#547C96">
			<td colspan="3" style="height:2em;color:white;text-align:center">
				<span>
				<?php
					
					echo '<h1>忘記密碼</h1>';
					
				?>
				</span>
			</td>
		</tr>
		
		<tr>
			<td style="height:5em;" width="30px"></td>
			<td>
				<lable><span style="line-height:30px">身分證字號：<?php echo form_error('idno')?><input type="button" id="checkEyeIdno" value="顯示身分證"></span>
					<input type="password" id="idno" name="idno" title="身分證字號" style="width:300px;height:1.5em;margin 2px auto" value=""></input>
				</lable>
			</td>
			<td width="30px"></td>
		</tr>
		<tr>
			<td style="height:2em;"></td>
			<td>
				驗證碼：
				<img id="imgcode" src="/mpage/home/captcha" style="width: 220px;" />
			</td>
			<td></td>
		</tr>
		<tr>
			<td style="height:2em;"></td>
			<td><input type="text" id="captcha" name="captcha" style="width:300px;height:1.5em;margin 2px auto" value="" autocomplete="off"></input></td>
			<td></td>
		</tr>
		
		<tr>
			<td style="height:3em;"></td>
			<td style="padding-top:10px;text-align:center">
				<input type="button" onclick="sendFun()" value="寄送驗證信"> </input>
				<input type="button" onclick="backHome()" value="返回臺北e大首頁"></input>
            </td>
			<td></td>
		</tr>
	</table>
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

		var city = new Array(
		    1, 10, 19, 28, 37, 46, 55, 64, 39, 73, 82, 2, 11,
		    20, 48, 29, 38, 47, 56, 65, 74, 83, 21, 3, 12, 30
		)

		id = id.toUpperCase();

        if (id.search(/^[A-Z](1|2|8|9)\d{8}$/i) == -1) {
		    alert('身分證格式錯誤');
		    return false;
		} else {
		    id = id.split('');

		    var total = city[id[0].charCodeAt(0) - 65];
		    for (var i = 1; i <= 8; i++) {
		        total += parseInt(id[i]) * (9 - i);
		    }

		    total += parseInt(id[9]);

		   	if(total % 10 != 0){
		   		alert('身分證格式錯誤');
		    	return false;
		   	}
		}
        
		obj.submit();
	}
</script>

<?php
	if(isset($error_message)){
		echo '<script>alert("'.$error_message.'")</script>';
	}

?>