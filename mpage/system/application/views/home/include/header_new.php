<!DOCTYPE html>
<html lang="en">
<head>

<!-- start: Meta -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<meta name="keywords" content="臺北e大,台北e大,數位學習,線上學習,學習,教學,免費,課程,e-learning,elearning,線上課程,台北,臺北,教育,終身學習,認證時數,鮮活電子報,教育訓練,訓練,語言,資訊,管理,人文,公務,教材,學電腦,資源,自由軟體">
<meta name="robots" content="all" />
<title>臺北e大</title>
<meta name="title" content="臺北e大"/>
<meta name="description" content="" />
<meta name="copyright" content="©2018 臺北e大"/>
  <!-- end: Meta -->
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

<!-- MOBILE METAS -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta names="apple-mobile-web-app-status-bar-style" content="black-translucent">

<!-- OG -->
<meta property="fb:app_id" content="392465500920619">
<meta property="og:title" content="臺北e大"/>
<meta property="og:type" content="article"/>
<meta property="og:url" content="https://elearning.taipei/mpage/"/>
<meta property="og:image" content="https://elearning.taipei/mpage/webfile/userfiles/images/logo_new_08.png"/>
<meta property="og:description" content="「臺北e大」是臺北市政府主辦的數位學習網，本站自90年11月正式開站以來，會員人數穩定成長，教材類型持續豐富，學習服務不斷創新，並在92年3月、94年7月、100年7月，分別進行三度網站改版，創造出現今「臺北e大」的網站規模，至今已是國內數位學習服務指標性領導品牌之一。"/>
<meta property="og:site_name" content="臺北e大"/> 

<!-- start: Favicon and Touch Icons -->
<link rel="shortcut icon" href="<?php echo base_url().asset_new_url('new/ico/favicon.ico') ?>">
<!-- end: Favicon and Touch Icons -->

<!-- CSS -->
<link href="<?php echo base_url().asset_new_url('new/js_newest/owlcarousel/assets/owl.carousel.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url().asset_new_url('new/js_newest/owlcarousel/assets/owl.theme.default.min.css'); ?>" rel="stylesheet"/>
<link href="<?php echo base_url().asset_new_url('new/css_newest/main.css'); ?>" rel="stylesheet"/>
<script src="<?php echo base_url().asset_new_url('js/device.js'); ?>" type="text/javascript"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-T01M02L6MS"></script> -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-T01M02L6MS" integrity="sha384-Rn8vwI2yzsIBsJ0Ycs2GhvdZxaMNS+s9Yt+NqMkioE0r1ZxEiVCHuB29cSWA+k4M" crossorigin="anonymous"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-T01M02L6MS');
</script>
<style>
.mobileshow{
  display:none !important;
}

.customalert {
    background: #fbf3bb;
    font-weight: 600;
    padding: 8px 0px;
    font-size: 13px;
    color: #be1e2d;
    border-bottom: 1px solid #f2b535;
    text-align: center;
    font-family:"Microsoft Yahei",Arial,'Hiragino Sans GB',sans-serif;
}

.customalert a:hover, a:active {
    color: #001E3C;
}

.customalert a:link, a:visited, .tabtree .tabrow0 li a, .dropdown-menu.usermen>li>a {
    color: #002f67;
    text-decoration: none;
}

</style>
</head>

<body>
<a href="#content" class="to_content" title="跳到主要內容區塊">跳到主要內容區塊</a>
<div class="loadbar"></div>

<div id="wrapper">

    <?php 
    if (isset($_COOKIE['moodle_loginasuser']) && !empty($_COOKIE['moodle_loginasuser'])) {
      $loginas = json_decode($_COOKIE['moodle_loginasuser'])->content;
    ?>
    <div class="customalert" style="position: relative;z-index: 99;top: 0;width: 100%;">
      <div class="container">
        <?php echo html_entity_decode($loginas, ENT_HTML5|ENT_QUOTES)?>
      </div>
      </div>
    <?php  
    }
    ?>

    <!--header-->
    <div id="header">
      <div class="inner-width">
          <a href="#Accesskey_U" id="Accesskey_U" accesskey="U" title="上方選單連結區，此區塊列有本網站的主要連結">:::</a>
          <div class="logo"><a href="https://elearning.taipei/mpage/home"><img src="<?php echo base_url().asset_new_url('new/images_newest/logo.png') ?>" alt="logo"/></a></div>
          <div class="top_menu">
                  <ul class="nav">
                    <?php if(!empty($_SESSION["accountuuid"])): ?>
                    <li><a id="pocket-link" href="https://elearning.taipei/mpage/course/pocket"><i class="fa fa-get-pocket" aria-hidden="true"></i> 選課口袋</a></li>
                    <?php endif ?>                    
                    <li><a href="https://elearning.taipei/mpage/home/sitemap"><i class="fa fa-sitemap" aria-hidden="true"></i> 網站導覽</a></li>
                    <?php
                      if(empty($_SESSION["accountuuid"])){
                        // 
                           // echo '<li class="submenuheader"><a href="#"><i class="fa fa-sign-in" aria-hidden="true"></i> 會員登入</a>';
                           // echo '<ul class="submenu w2">';
                           // echo '<li><a href="https://elearning.taipei/AuthorizationGrant_tpcd.php" title="臺北卡登入">臺北卡</a></li>';
                           // echo '<li><a href="https://elearning.taipei/mpage/home/login_other" title="臨時帳號登入">臨時帳號</a></li>';
                           // echo '</ul></li>';

                        // } else {
                          // echo '<li><a href="https://elearning.taipei/AuthorizationGrant_tpcd.php"><i class="fa fa-sign-in" aria-hidden="true"></i> 會員登入</a></li>';
                           echo '<li class="submenuheader"><a href="#"><i class="fa fa-sign-in" aria-hidden="true"></i> 會員登入</a>';
                           echo '<ul class="submenu w2">';
                           echo '<li><a href="https://elearning.taipei/AuthorizationGrant_tpcd.php" title="台北通登入" style="font-size:15px">台北通帳號</a></li>';
                           
                           echo '<li><a href="https://elearning.taipei/mpage/home/login_other" title="e大帳號登入" style="font-size:15px">e大帳號</a></li>';  
                           
                           echo '</ul></li>';
                        // }
                      } else {
                        $nick_view = !empty($nick)?$nick:'學員';

                        $db_ip = "172.25.154.75";
                        $db_username = "nelearn";
                        $db_password = "L@admin01!";
                        $db_name2 = "moodle";

                        $link2 = new mysqli($db_ip, $db_username, $db_password, $db_name2); //moodle
                        if (!$link2) {
                            die('connect error');
                        }

                        mysqli_set_charset($link2, "utf8");

                        if(isset($_SESSION['userData']) && $_SESSION['userData']['userid'] > 0){
                          $sql = sprintf("insert into mdl_fet_user_behavior(uid,pages,search_time) values('%s','%s','%s')",
                              addslashes($_SESSION['userData']['userid']),
                              addslashes($_SERVER['REQUEST_URI']),
                              addslashes(time())
                          );
                          mysqli_query($link2, $sql);
                        }
                        

                        $sql = sprintf("select count(1) cnt from mdl_fet_pid a join mdl_role_assignments b on a.uid = b.userid where a.idno = '%s' and roleid in (1,11)",$_SESSION['userData']['Usrid']);

                        $result = mysqli_query($link2, $sql);
                        $row = $result->fetch_object();

                        if($row->cnt > 0){
                          $m_cnt = 'admin';
                          $path = "https://elearning.taipei/eda/manage/Instant_message";
                        } else {
                          $sql_uid = sprintf("select uid from mdl_fet_pid where idno = '%s'",$_SESSION['userData']['Usrid']);
                          $id_result = mysqli_query($link2, $sql_uid);
                          $row_id = $id_result->fetch_object();

                          $sql2 = sprintf("SELECT
                                            count(1) cntt
                                          FROM
                                            mdl_fet_instant_message a
                                          WHERE
                                            (
                                              a.recipient_id = '%s'
                                              OR a.recipient_id = 'all'
                                            )
                                          AND a.id NOT IN (
                                            SELECT
                                              mid
                                            FROM
                                              mdl_fet_instant_message_readed
                                            WHERE
                                              uid = '%s'
                                          )",
                                          addslashes($row_id->uid),
                                          addslashes($row_id->uid)
                                        );

                          $result2 = mysqli_query($link2, $sql2);
                          $row2 = $result2->fetch_object();
                        

                          if($row2->cntt > 0){
                            if($row2->cntt > 9){
                              $m_cnt = "9+";
                            } else {
                              $m_cnt = $row2->cntt;
                            }
                          } else {
                            $m_cnt = "0";
                          }
                          
                          $path = "https://elearning.taipei/eda/manage/Instant_message_receive/";
                        }

                        mysqli_close($link2);
                        
  
                        // } else {



                        echo '<li class="submenuheader">';
                        echo '<a href="https://elearning.taipei/mpage/home/logout"><i class="fa fa-sign-in" aria-hidden="true"></i> '.$nick.'(登出)</a>';
                        echo '</li>';
                      // }
                        echo '<li>';
                        echo '<a href="'.htmlspecialchars($path, ENT_HTML5|ENT_QUOTES).'" target="_blank"><img alt="E-mail" style="vertical-align:middle;height:20px;margin-top:-4px;" src="https://elearning.taipei/mpage/webfile/userfiles/icon/icon/'.htmlspecialchars($m_cnt, ENT_HTML5|ENT_QUOTES).'.png"></a>';
                        echo '</li>';
                      }
                    ?>
                    <li><a href="https://www.facebook.com/elearning.taipei/" title="臺北e大樂在學習臉書" target="_blank"><i class="fa fa-facebook-official" style="font-size: 22px" aria-hidden="true"></i></a></li>
                  </ul>
                  <ul class="main">
                  <?php

                    if (preg_match("/iPod|iPhone|iPad|Android|webOS|BlackBerry|RIM Tablet|Mobile/", $_SERVER["HTTP_USER_AGENT"])) {
                      $course_record_url = 'https://elearning.taipei/sso/to.php?sitelink=mobile&mode=1';
                      $other_city_url = 'http://elearning.taipei/sso/to.php?sitelink=mobile&mode=1&tab=P2';
                      $apply_class_url = '';
                    } else {
                      $course_record_url = 'https://elearning.taipei/sso/to.php?sitelink=moodle';
                      $other_city_url = 'http://elearning.taipei/sso/to.php?sitelink=moodle&tab=P2';
                      $apply_class_url = '<li><a href="https://elearning.taipei/elearn/fetpayment/apply_class.php">語言自費班期</a></li>';
                    }

                    if (isset($_COOKIE['moodle_loginasuser']) && !empty($_COOKIE['moodle_loginasuser'])) {
                      $course_record_url = 'http://elearning.taipei/elearn/courserecord/index.php';
                      $other_city_url = 'http://elearning.taipei/elearn/my/index.php?mytoctab=P2';
                    }

                    if(!empty($_SESSION["accountuuid"]) && $_SESSION["userData"]["identity"] == '1'){
                      if (!empty($_SESSION['accountuuid']) && !empty($_SESSION['other_login'])){
                        echo '<li><a href="/mpage/member" class="mobileshow">修改個人資料</a></li>';
                      }                      
                      echo '<li class="submenuheader">';
                      echo '<a href="#">我的課程</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="'.$course_record_url.'">學習紀錄</a></li>';
                      echo '<li><a target="_blank" href="https://elearning.taipei/sso/to.php?sitelink=homephy">實體班期專區</a></li>';
                      if(time() < 1554048000){
                        echo '<li><a href="https://elearning.taipei/sso/to.php?sitelink=forum">e大學習論壇</a></li>';
                      }
                      // if(date('Y-m-d') == '2019-03-15'){
                      //   echo '<li><a target="_blank" href="https://elearning.taipei/sso/to.php?sitelink=homeepa">ePA市政管理學苑</a></li>';
                      // }
                      echo '</ul></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">選課中心</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/learn_type_list">公務員核心課程庫</a></li>';
//                      echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
//                      echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
//                      echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
//                      echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
//                      echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
                      echo $apply_class_url;
                      echo '<li><a href="https://elearning.taipei/elearn/course/courselist_export.php">課程清單下載</a></li>';
                      echo '<li><a href="'.$other_city_url.'">研討/活動報名</a></li>';
                      echo '</ul></li>';
                      echo '<li>';
                      echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                      echo '</li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">合作推廣</a>';
                      echo '<ul class="submenu">';
                      //echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';
                      echo '<li><a href="https://elearning.taipei/goeplus.php">e等公務園+</a></li>';
                      echo '</ul></li>';
                      // if(time() < 1554048000){
                        //echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank">鮮活電子報</a></li>';
                      // }
                      echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                      // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">公務人員版</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="https://elearning.taipei/mpage/home/index/2">一般民眾版(切換)</a></li>';
                      echo '</ul></li>';
                      echo '<li>';
                    } else if(!empty($_SESSION["accountuuid"]) && $_SESSION["userData"]["identity"] == '2'){
                      if (!empty($_SESSION['accountuuid']) && !empty($_SESSION['other_login'])){
                        echo '<li><a href="/mpage/member" class="mobileshow">修改個人資料</a></li>';
                      }                           
                      echo '<li class="submenuheader">';
                      echo '<a href="#">我的課程</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="'.$course_record_url.'">學習紀錄</a></li>';
                      echo '<li><a target="_blank" href="https://elearning.taipei/sso/to.php?sitelink=homephy">實體班期專區</a></li>';
                      if(time() < 1554048000){
                        echo '<li><a href="https://elearning.taipei/sso/to.php?sitelink=forum">e大學習論壇</a></li>';
                      }
                      // if(date('Y-m-d') == '2019-03-15'){
                      //   echo '<li><a target="_blank" href="https://elearning.taipei/sso/to.php?sitelink=homeepa">ePA市政管理學苑</a></li>';
                      // }
                      echo '</ul></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">選課中心</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
   //                   echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
   //                   echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
   //                   echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
   //                   echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
   //                   echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
                      echo $apply_class_url;
                      echo '<li><a href="https://elearning.taipei/elearn/course/courselist_export.php">課程清單下載</a></li>';
                      echo '<li><a href="'.$other_city_url.'">研討/活動報名</a></li>';
                      echo '</ul></li>';
                      echo '<li>';
                      echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                      echo '</li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">合作推廣</a>';
                      echo '<ul class="submenu">';
                      //echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';

                      // if(preg_match("/^211.79.136.202$/", $_SERVER["REMOTE_ADDR"]) == true) ) {
                          if (isset($_SESSION['userData']['promotion_type'])) {
                              echo '<li><a href="/mpage/home/promotion">服務內容申請</a></li>';
                          } 
                      // }
                      echo '<li><a href="https://elearning.taipei/goeplus.php">e等公務園+</a></li>';
                      echo '</ul></li>';
                      // if(time() < 1554048000){
                        //echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank">鮮活電子報</a></li>';
                      // }
                      echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                      // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">一般民眾版</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="https://elearning.taipei/mpage/home/index/1">公務人員版(切換)</a></li>';
                      echo '</ul></li>';
                      echo '<li>';
                    } else {
                      echo '<li class="submenuheader">';
                      echo '<a href="#">選課中心</a>';
                      echo '<ul class="submenu">';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
     //                 echo '<li><a href="https://elearning.taipei/elearn/course/courselist_export.php">課程清單下載</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/login_other">研討/活動報名</a></li>';
                      echo '</ul></li>';
                      echo '<li>';
                      echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                      echo '</li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                      echo '<li class="submenuheader">';
                      echo '<a href="#">合作推廣</a>';
                      echo '<ul class="submenu">';
                      //echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                      echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';

                      # if(preg_match("/^211.79.136.202$/", $_SERVER["REMOTE_ADDR"]) == true) ) {
                          echo '<li><a onClick="'."window.open('/elearn/fet_promotion/common/cooperationPromotion/apply.php','2',config='height=400,width=550,toolbar=no');".'">合作推廣申請</a></li>';  
                       // }
                      echo '<li><a href="https://elearn.hrd.gov.tw">e等公務園+</a></li>';
                      echo '</ul></li>';
                      // if(time() < 1554048000){
                        //echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank">鮮活電子報</a></li>';
                      // }
                      echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                      // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                    }
                  ?>
                  </ul>

              <div class="clear"></div>
          </div>
          <div class="marquee_box">
            <ul>
            <?php
              for($i=0;$i<count($marquee);$i++){
                echo '<li><a href="'.$marquee[$i]->link.'">'.$marquee[$i]->name.'</a></li>';
              }
            ?>
            </ul>
            
          </div>
        </div>

        <!--MOBILE-->
        <div class="menu_btn">
          <div class="m1"></div>
          <div class="m2"></div>
          <div class="m3"></div>
        </div>
        <div class="top_menu_mask"></div>
        <div class="bg"></div>
        <div class="bg2"></div>
        <div class="bg3"></div>
    </div>
