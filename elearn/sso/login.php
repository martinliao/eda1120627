<?php
        //
        //Moodle SSO
        require_once('../config.php');
	require_once ('lib/des.php');
        define("KEY_ELEARN","HD3DKFOSD"); //臺北e大 SSO KEY
        define("KEY_MOBILE","DSK3KOPSD");  //臺北e大行動版  SSO KEY
        define("TIME_OUT"  ,50000);         //time out時間(ms)

        // $encode_account = $_GET['a']; //account
        // $encode_time    = $_GET['t']; //time

        // 若經由轉頁面傳送值過來的， GET login 為 sso
        if($_GET['login'] == 'sso')
        {
            if($_GET['mobile']==1) {//新增登入流程(手機板)
             
                if(isset($_GET['mode']) && $_GET['mode'] == '1'){
                    fromMobi($_GET['a'], $_GET['t'], $_GET['mode']);

                } else if(isset($_GET['mode']) && $_GET['mode'] == '2'){

                    fromMobi($_GET['a'], $_GET['t'], $_GET['mode'], $_GET['c']);
                } else {
                    fromMobi($_GET['a'], $_GET['t']);
                }
                
                exit;
            }


            // 改送POST
            $vid    = intval($_GET['vid']); //view.php?id
            $encode_account = $_GET['a']; //account
            $encode_time    = $_GET['t']; //time
            $search         = $_GET['search'];
            $encode_account = DES::base64url_decode($encode_account);
            $encode_time    = DES::base64url_decode($encode_time);
            $login_account  = DES::decrypt(KEY_ELEARN, $encode_account);
            $login_time     = DES::decrypt(KEY_ELEARN, $encode_time);
            $timeout        = time()-$login_time;
            $isOtherLogin   = $_GET['isOtherLogin'];

            if($timeout < TIME_OUT)
            {
                session_start();

                $user = get_complete_user_data('username', $login_account);
                header("Content-Type:text/html; charset=utf-8");
                if(!$user){
                    if(substr($login_account, 0, 4) == 'edat' or substr($login_account, 0, 4) == 'edap'){
                        echo '<script>
                                alert("您目前登入的身分為人事帳號，無法閱讀課程。");
                                location.href="https://elearning.taipei/mpage/home";
                            </script>';
                    } else {
                        echo '沒有這名'.$login_account.'使用者，將導回臺北e大';
                        redirect("https://elearning.taipei/mpage/home");
                    }
                }
                else{
                    $user->isOtherLogin = $isOtherLogin;
                                      
                    complete_user_login($user);
                    add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=".SITEID,
    $user->id, 0, $user->id);
                    $cookie_name = "sesskey";
                    $cookie_value = "$USER->sesskey";
                    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day

                   //取的使用者研習時數核發設定資料    
                    $cert_data   = $DB->get_record('fet_cert_setting', array('uid' => $user->id));
                    if(empty($cert_data)) {
                        // die('cert');
                        redirect("{$CFG->wwwroot}/fetcerthours/hours_set.php");     
                    }

                   if(isset($_GET['course_center'])) {       
                       if('y'==$_GET['course_center']) {                        
                            // echo "y";
                            if ($vid > 0) {
                                redirect("{$CFG->wwwroot}/courseinfo/index.php?courseid=$vid");
                            } elseif ($search == '空中大學') {
                                redirect("{$CFG->wwwroot}/course/index.php?search=空中大學");
                            } else {    
                                redirect("{$CFG->wwwroot}/course/index.php");
                            }
                        }else{
                            die("您的IP已被記錄，請勿嘗試測試非法網址輸入!");
                        }
                    }else{
			// echo "t";
                        if ($vid > 0) {
                            redirect("{$CFG->wwwroot}/course/view.php?id=$vid&act=reg");
                        }
                        else {
                            if(isset($_GET['tab'])) {
                                redirect("{$CFG->wwwroot}/my/index.php?mytoctab=P2");
                            }
                            redirect("{$CFG->wwwroot}/courserecord/index.php");
                        }
                    }
                }
            }
            else{
                // print_r($encode_account);
                // print_r($login_time);
                die("Timeout");
            }
        }
        else{
            die("Error");
        }
    //從手機板來的做這隻 先登入moodle再導回手機頁面
    function fromMobi($a, $t, $mode='', $c='') {
        $encode_account = $a;//$_GET['a']; //account
        $encode_time    = $t;//$_GET['t']; //time
        $encode_account = DES::base64url_decode($encode_account);
        $encode_time    = DES::base64url_decode($encode_time);
        $login_account  = DES::decrypt(KEY_MOBILE, $encode_account);
        $login_time     = DES::decrypt(KEY_MOBILE, $encode_time);
        $timeout        = time()-$login_time;
        if($timeout < TIME_OUT)
        {

            session_start();
            $user = get_complete_user_data('username', $login_account);
            //header("Content-Type:text/html; charset=utf-8");
            if(!$user) {
                die('沒有這名'.$login_account.'使用者，將導回臺北e大');
            }
            else{
                complete_user_login($user);
                add_to_log(SITEID, 'user', 'login', "view.php?id=$user->id&course=".SITEID,
$user->id, 0, $user->id);
                $cookie_name = "sesskey";
                $cookie_value = "$user->sesskey";
               
                setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
                if($mode == '1'){
                    // $url = "/mobile/login/sso_validate";
                    $url = "/mobile/login/sso_validate?a=$a&t=$t&mode=$mode";
                } else if($mode == '2'){
                    $url = "/mobile/login/sso_validate?a=$a&t=$t&mode=$mode&c=$c";
                } else {
                    $url = "/mobile/login/sso_validate?a=$a&t=$t";
                }
                // echo $url;exit();
                header("Location: {$url}");
                exit;
                // if(isset($_GET['course_center'])) {
                    // if('y'==$_GET['course_center']) {
                        // echo "y";
                    // }
                    // else{
                        // die("請勿嘗試測試非法網址輸入!");
                    // }
                // }
                // else{
                    // echo "t";
                // }
            }
        }
        else{
            die("Timeout");
        }
    }
?>
