<?php
	require_once ('lib/common.php');
	/*
		1.將使用者帳號加密
			a.加密方式：
			$a = 帳號先經過每個app的key進行des加密
			$t = 加上時間進行des加密

			b.解密方式：
			解碼後的字串使用app的key進行DES解密 -> $a = account
			解碼後的字串使用app的key進行DES解密 -> $t = time

		2.傳送到app的認證端進行認證作業

	 */

	require_once ('lib/des.php');
	require_once ('lib/lib_epa.php');
	
	define("KEY_ELEARN"  ,"HD3DKFOSD");  //臺北e大       SSO KEY
    define("KEY_MOBILE"  ,"DSK3KOPSD");  //臺北e大行動版  SSO KEY
	define("KEY_REPORT"  ,"DDSFEDF3D");  //臺北e大REPORT  SSO KEY
	define("KEY_FORUM"   ,"DAFDSDF8Z");  //臺北e大論壇  SSO KEY
	define("KEY_EPA"     ,"FSDF34DSF");  //臺北e大 ePa KEY
	define("KEY_PHY"     ,"AAFD3DDSF");  //臺北e大 實體 KEY
	define("KEY_ECPA"    ,"EDS45CSAS");  //ECPA KEY
	define("KEY_AIR"	 ,"AIRK5EYAS");  //空中大學 SSO KEY

	define("TIME_OUT"  ,50000);         //通訊timeout時間(ms)

	// global $USER,$SESSION
	
	
	//加密帳號
	$sitelink = $_GET["sitelink"];
	//$viewId = intval($_GET["v"]);
        $viewId = isset($_GET["v"]) ? intval($_GET["v"]) : ""; 
	//$ccid = $_GET["cid"]; //20210624區別直接看試看課程
        $ccid = isset($_GET["cid"]) ? $_GET["cid"] : "";
	//$search = $_GET["search"];
        $search = isset($_GET["search"]) ? $_GET["search"] : "";
	//$courseview = intval($_GET["courseview"]);
        $courseview = isset($_GET["courseview"]) ? intval($_GET["courseview"]) : "";
	$baseDomain="https://elearning.taipei";
	$url = 'https://elearning.taipei/mpage/home';
	
	//當accountuuid null 導向網路市民
	//進行網路市民驗證程序作業
	if(!isset($_SESSION["accountuuid"])) {
		if('forum'==$sitelink){
			//訪客身分
			$url = "http://welearning.taipei/";
			$sitelink = "others";
		}else if('phyEcpa'==$sitelink){
			//訪客身分
		    //pass 直接SSO 到ecpa
		}else{
			//導到網路市民登入頁
			if($sitelink=="moodle_course_guest"&&$viewId>0) {/*.....*/}
			
			else {
				$url = "https://elearning.taipei/AuthorizationGrant_tpcd.php";
				$sitelink = "others";
			}
			
		}
	}
	else{
		$account = preg_match("/guest/",$_SESSION["accountuuid"])?"":$_SESSION["accountuuid"];
	}
	
	switch ($sitelink) {

		case 'moodle':
			
			$account = DES::encrypt(KEY_ELEARN,$account);
			$time    = DES::encrypt(KEY_ELEARN,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			$isOtherLogin = isset($_SESSION['other_login']) ? $_SESSION['other_login'] : null;
			$isOtherLogin = ($isOtherLogin === true) ? 'Y' : 'N';
			$url = "/elearn/sso/login.php?login=sso&a=$account&t=$time&isOtherLogin=".$isOtherLogin;
			// $url = "/elearn/sso/index_form.php?a=$account&t=$time";
			if($courseview>0) {
				$url .= "&vid=".$courseview;
			}
			if(isset($_GET['tab'])) {
				$url .= "&tab=".$_GET['tab'];
			}
			$url = sprintf($baseDomain."%s", $url);

			break;

		//直接跑到選課中心
		case 'moodle_course':

			$account = DES::encrypt(KEY_ELEARN,$account);
			$time    = DES::encrypt(KEY_ELEARN,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);

			$url = "/elearn/sso/login.php?login=sso&a=$account&t=$time&course_center=y";
			// $url = "/elearn/sso/index_form.php?a=$account&t=$time&course_center=y";
			// die($url);

			if($viewId>0) {//課程介紹
				$url .= "&vid=$viewId";
			}
			$url = sprintf($baseDomain."%s", $url);

			break;

		//直接SSO到選課中心
		case 'moodle_course_guest':
			$account = 'guest001';
			$account = DES::encrypt(KEY_ELEARN,$account);
			$time    = DES::encrypt(KEY_ELEARN,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			//var_dump($_SESSION['courseid']);die(); 
			$url = "/elearn/sso/login.php?login=sso&a=$account&t=$time&course_center=y";
			// $url = "/elearn/sso/index_form.php?a=$account&t=$time&course_center=y";
			if(($viewId>0)&&($ccid=="y")) {//課程介紹
				//var_dump($ccid);die();
				$url = "/elearn/sso/login.php?login=sso&a=$account&t=$time&vid=$viewId";
			}elseif($viewId>0){
				$url .= "&vid=$viewId";
			}

			if($search == '空中大學') {//課程介紹
				$url .= "&search=空中大學";
			}
			$url = sprintf($baseDomain."%s", $url);

			break;


		case 'forum':
			//先查出暱
			$dbhost = '172.25.154.75';
		    $dbuser = 'nelearn';
		    $dbpass = 'L@admin01!';
		    $dbname = 'moodle';
		    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
			mysql_query("SET NAMES 'UTF8'");
		    mysql_select_db($dbname);
			$sql = "
			SELECT b.data FROM
			mdl_user a JOIN mdl_user_info_data b ON b.userid = a.id WHERE b.fieldid = 1 AND a.username ='".$account."';";
			$nickname ='';

		    $result = mysql_query($sql) or die('MySQL query error');
		    while($row = mysql_fetch_array($result)){
		        $nickname = $row['data'];
		    }
			if($nickname==''){
				echo "<h1 style='color: #5e9ca0;'>親愛的學員：</h1>
<h1 style='color: #5e9ca0;'>因為您尚未填報您的論壇匿稱，惠請您於登入帳號後，在<span style='color: #ff0000;'>【主選單】/【個人資料】/【論壇暱稱】</span> 進行匿稱設置，謝謝您。</h1>";
			die();
			}

			$account = $nickname;
			$account = DES::encrypt(KEY_FORUM,$account);
			$time    = DES::encrypt(KEY_FORUM,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			$url = "http://welearning.taipei/sso_login.php?a=$account&t=$time";

			break;


		case 'forum_post':
			//先查出暱
			$dbhost = '172.25.154.75';
		    $dbuser = 'nelearn';
		    $dbpass = 'L@admin01!';
		    $dbname = 'moodle';
		    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
			mysql_query("SET NAMES 'UTF8'");
		    mysql_select_db($dbname);
			$sql = "
			SELECT b.data FROM
			mdl_user a JOIN mdl_user_info_data b ON b.userid = a.id WHERE b.fieldid = 1 AND a.username ='".$account."';";
			$nickname ='';

		    $result = mysql_query($sql) or die('MySQL query error');
		    while($row = mysql_fetch_array($result)){
		        $nickname = $row['data'];
		    }
			if($nickname==''){
				echo "<h1 style='color: #5e9ca0;'>親愛的學員：</h1>
<h1 style='color: #5e9ca0;'>因為您尚未填報您的論壇匿稱，惠請您於登入帳號後，在<span style='color: #ff0000;'>【主選單】/【個人資料】/【論壇暱稱】</span> 進行匿稱設置，謝謝您。</h1>";
			die();
			}
			// 文章ID、頁數。
			$page = 1;
			if(!empty($_GET['tid']) && !empty($_GET['page']) && is_numeric($_GET['tid']))
			{
				$tid = is_numeric($_GET['tid'])?$_GET['tid']:'';
				$page = is_numeric($_GET['page'])?$_GET['page']:1;
			}

			$account = $nickname;
			$account = DES::encrypt(KEY_FORUM,$account);
			$time    = DES::encrypt(KEY_FORUM,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			if(!empty($tid))
			{
				$url = sprintf("http://welearning.taipei/sso_login.php?a=%s&t=%s&tid=%s&page=%s",$account,$time,$tid,$page);
			}
			else
			{
				$url = "http://welearning.taipei/sso_login.php?a=$account&t=$time";	
			}

			break;

		//
		case 'report':

			$account = DES::encrypt(KEY_REPORT,$account);
			$time    = DES::encrypt(KEY_REPORT,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			$url = "http://report.elearning.taipei:8080/report/login/sso_validate?a=$account&t=$time";

			break;

		//行動版
		case 'mobile':
			
			$account = DES::encrypt(KEY_MOBILE,$account);
			$time    = DES::encrypt(KEY_MOBILE,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);
			// $url = "/mobile/login/sso_validate?a=$account&t=$time";
			// header("Location: {$url}");
			//本來走上面隱藏程式碼流程, 後來改成先登入moodle by hao

			if(isset($_GET['mode']) && $_GET['mode'] == '1'){
				$url = "/elearn/sso/login.php?a=$account&t=$time&login=sso&mobile=1&mode=1";//手機版學習記錄
			} else if(isset($_GET['mode']) && $_GET['mode'] == '2'){
				$url = "/elearn/sso/login.php?a=$account&t=$time&login=sso&mobile=1&mode=2&c=".$_GET['c'];//手機版選課中心
			} else {
				$url = "/elearn/sso/login.php?a=$account&t=$time&login=sso&mobile=1";
			}
			if(isset($_GET['tab'])) {
				$url .= "&tab=".$_GET['tab'];
			}

			$url = sprintf($baseDomain."%s", $url);

			break;

		//實體課程
		case 'phy':
			$encode_idno    = addslashes($_GET['a']); //id number
			$encode_time    = addslashes($_GET['t']); //time
			
			$encodeStr = $encode_idno;
		
			// $encode_idno    = DES::base64url_decode($encode_idno);
			// $encode_time    = DES::base64url_decode($encode_time);
			// $personal_id    = DES::decrypt(KEY_PHY, $encode_idno);
			// $login_time     = DES::decrypt(KEY_PHY, $encode_time);
			// $timeout        = time()-$login_time;
			// // $encodeStr = encrypt($personal_id, EPAKEY);
			// $encodeStr = DES::base64url_encode($personal_id);
			
			
			
			if($_GET['mobile'] == '16'){
				$url = "https://dcsdcourse.taipei.gov.tw/elearn_sso.php?login_sid={$encodeStr}&mobile=16";
			} else {
				$url = "https://dcsdcourse.taipei.gov.tw/elearn_sso.php?login_sid={$encodeStr}";
			}

			// 
  				$url = "https://dcsdcourse.taipei.gov.tw/base/admin/welcome/elearn_sso?login_sid={$encodeStr}";
			// } 

			if(isset($_GET['to'])) {
				$to    = addslashes($_GET['to']); //redirect url
				$url .= "&to=".$to;
			}

			break;

		//ECPA
		case 'ecpa':
		
			$encode_idno    = $_GET['a']; //id number
			$encode_time    = $_GET['t']; //time
			$url = "https://elearning.taipei/ap_verify.php?a=$encode_idno&t=$encode_time";
				
			break;


		//ePa
		case 'epa':
			$encode_idno    = $_GET['a']; //id number
			$encode_time    = $_GET['t']; //time
			$encode_idno    = DES::base64url_decode($encode_idno);
			$encode_time    = DES::base64url_decode($encode_time);
			$personal_id    = DES::decrypt(KEY_EPA, $encode_idno);
			$login_time     = DES::decrypt(KEY_EPA, $encode_time);
			$timeout        = time()-$login_time;
			//由於兩邊時間差異過大，暫時關閉time out設定 -> Sam
			// if($timeout < TIME_OUT)
			// {
				$input = $personal_id . ',' . time();

				$encodeStr = DES::encrypt(EPAKEY,$personal_id);
				$encodeStr = DES::base64url_encode($encodeStr);
				
	   			// $encodeStr = encrypt($input, EPAKEY);
				$url = "http://epa.taipei.gov.tw/index.php?option=com_epa&action=relogin&login_sid={$encodeStr}";
			// }else{
				// die("Timeout");
			// }
			break;


		//實體課程從首頁登入
		case 'homephy':
			$userinfo	    = $_SESSION['userData'];
			$encode_idno    = $userinfo['Usrid']; //id number
			if(1) {
				if(!$encode_idno) {
					$dbhost = '172.25.154.75';
				    $dbuser = 'nelearn';
				    $dbpass = 'L@admin01!';
				    $dbname = 'moodle';
				    $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
					mysql_query("SET NAMES 'UTF8'");
				    mysql_select_db($dbname);
					$sql = sprintf("SELECT b.idno FROM mdl_user a JOIN mdl_fet_pid b ON a.id=b.uid WHERE a.username='%s'", $_SESSION['accountuuid']);
				    $result = mysql_query($sql) or die('MySQL query error');
				    while($row = mysql_fetch_array($result)){
				        $encode_idno = $row['idno'];
				    }
				}
			}	
			$idno    = DES::encrypt(KEY_PHY,$encode_idno);
			$time    = DES::encrypt(KEY_PHY,time());
			$idno    = DES::base64url_encode($idno);
			$time    = DES::base64url_encode($time);

			if($_GET['mobile'] == '16'){
				$url = "https://elearning.taipei/sso/to.php?sitelink=phy&a=$idno&t=$time&mobile=16";
			} else {
				$url = "https://elearning.taipei/sso/to.php?sitelink=phy&a=$idno&t=$time";
			}
			break;

		//ePa從首頁登入
		case 'homeepa':
			$userinfo	    = $_SESSION['userData'];
			$encode_idno    = $userinfo['Usrid']; //id number
			$idno           = DES::encrypt(KEY_EPA,$encode_idno);
			$time           = DES::encrypt(KEY_EPA,time());
			$idno           = DES::base64url_encode($idno);
			$time           = DES::base64url_encode($time);

			$url = "https://elearning.taipei/sso/to.php?sitelink=epa&a=$idno&t=$time";
			break;


		//ePa從首頁登入
		case 'phyEcpa':
				
			$encode_idno    = base64url_decode($_GET['a']);
			$encode_idno    = decryptecpa($encode_idno , KEY_ECPA); //id number
	
			$idno           = DES::encrypt(KEY_ECPA,$encode_idno);
			$time           = DES::encrypt(KEY_ECPA,time());
			$idno           = DES::base64url_encode($idno);
			$time           = DES::base64url_encode($time);

			$url = "https://elearning.taipei/ap_verify.php?a=$idno&t=$time";
        	$result = postData($url, $data);
			break;

		case 'airschool':
			$account = DES::encrypt(KEY_AIR,$account);
			$time    = DES::encrypt(KEY_AIR,time());
			$account = DES::base64url_encode($account);
			$time    = DES::base64url_encode($time);

			$url = "https://172.25.154.73/sso/to.php?login=sso&a=$account&t=$time";
			
			break;
		case 'others':
			break;
		default:

			$url = "https://elearning.taipei/mpage/home/auth_taipei";
			break;
	}

	function postData($url, $data){
	        $ch = curl_init();
	        $timeout = 5;
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	        $handles = curl_exec($ch);
	        curl_close($ch);
	        return $handles;
	}
	
	function base64url_decode($data) {
	    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
	}
	
	function decryptecpa($data, $key)
	{
		$key = md5($key);
	    $x = 0;
	    $data = base64_decode($data);
	    $len = strlen($data);
	    $l = strlen($key);
	    for ($i = 0; $i < $len; $i++)
	    {
	        if ($x == $l) 
	        {
	        	$x = 0;
	        }
	        $char .= substr($key, $x, 1);
	        $x++;
	    }
	    for ($i = 0; $i < $len; $i++)
	    {
	        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
	        {
	            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
	        }
	        else
	        {
	            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
	        }
	    }
	    return $str;
	}
	
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <title>臺北e大</title>
</head>
<body>
<?php

redirectto($url);

?>
<h1 hidden>臺北E大</h1>
</body></html>
