<!DOCTYPE html>
<html lang="en">
<head>
	<title>登入實體系統</title>
	</head>
<body><h1>前往e等公務員</h1></body></html>
<?php
		//
		//Moodle SSO		
		require_once('../config.php');
		require_once ('lib/des.php');
		require_once ('../functions.php');

		define("KEY_PHY"   ,"AAFD3DDSF");  //臺北e大 實體 KEY
		global $USER;
		$idno = getIdno($USER->id);
		if(''==$idno || ('1'==$idno)){ //1是guest
			die('異常錯誤，身分證字號為空');
		}
		

		$idno    = DES::encrypt(KEY_PHY,getIdno($USER->id));
		$time    = DES::encrypt(KEY_PHY,time());
		$idno    = DES::base64url_encode($idno);
		$time    = DES::base64url_encode($time);

		$turnto  = DES::base64url_encode(addslashes($_GET['to']));

		$url = "https://elearning.taipei/sso/to.php?sitelink=phy&a=$idno&t=$time&to=$turnto";
		header("Location: {$url}");
		exit;

?>
