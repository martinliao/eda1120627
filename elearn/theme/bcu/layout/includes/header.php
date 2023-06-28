<?php
set_time_limit(10800);

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    theme
 * @subpackage bcu
 * @copyright  2014 Birmingham City University <michael.grant@bcu.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
// Fixed header is determined by the individual layouts.

if(!ISSET($fixedheader)) {
    $fixedheader = false;
}
theme_bcu_initialise_zoom($PAGE);
$setzoom = theme_bcu_get_zoom();

theme_bcu_initialise_full($PAGE);
$setfull = theme_bcu_get_full();

$left = (!right_to_left());  // To know if to add 'pull-right' and 'desktop-first-column' classes in the layout for LTR.

//$hasmiddle = $PAGE->blocks->region_has_content('middle', $OUTPUT);
$hasfootnote = (!empty($PAGE->theme->settings->footnote));
$haslogo = (!empty($PAGE->theme->settings->logo));

// Get the HTML for the settings bits.
$html = theme_bcu_get_html_for_settings($OUTPUT, $PAGE);

if (right_to_left()) {
    $regionbsid = 'region-bs-main-and-post';
} else {
    $regionbsid = 'region-bs-main-and-pre';
}

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <!-- <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet"> -->
    <!--<link href='//fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>-->
    <link href="<?php echo $CFG->wwwroot; ?>/theme/bcu/dl_style/font-awesome.min.css" rel="stylesheet">
    <link href='<?php echo $CFG->wwwroot; ?>/theme/bcu/dl_style/css.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $CFG->wwwroot; ?>/theme/bcu/dl_style/jquery-ui.css' rel='stylesheet' type='text/css'>
    <meta property="og:title" content="課程分享"/>
    <meta property="og:description" content="台北e大課程分享"/>
    <meta property="og:type" content="website"/>
    
    <meta property="fb:admins" content="585913848244854" />
    <meta property="og:image" content="https://elearning.taipei/mpage/system/application/assets/new/images_newest/xlogo.png.pagespeed.ic.epKHkxB8lE.webp"/>
    <?php 
      if(1) { 
        echo '<link href="'.$CFG->wwwroot.'/theme/bcu/new/css_newest/main.css" rel="stylesheet">';
        //BASE
        echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/libs/jquery.224.min.js"></script>';
        echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/libs/jquery-debounce-min.js"></script>';
        echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/libs/jquery.easing.min.js"></script>';
        echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/libs/imgLiquid-min.js"></script>';

        //custom 
        echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/effect.js"></script>';
      }
    ?>

    <?php echo $OUTPUT->standard_head_html() ?>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-T01M02L6MS"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-T01M02L6MS');
    </script>
	<script>

	$(document).ready(function(){
		$("#fb_fans").text("");
		$("#search_word").text("");
	});

	</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(array('two-column', $setzoom)); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page" class="container-fluid <?php echo "$setfull"; ?>">

<?php if (core\session\manager::is_loggedinas()) { ?>
<div class="customalert">
<div class="container">
<?php echo $OUTPUT->login_info(); ?>
</div>
</div>
<div class="customalert" style="position: fixed;z-index: 99;top: 0;width: 100%;">
<div class="container">
<?php echo $OUTPUT->login_info(); ?>
</div>
</div>
<?php
} else if (!empty($PAGE->theme->settings->alertbox)) {
?>
<div class="customalert">
<div class="container" >
<?php echo $OUTPUT->get_setting('alertbox', 'format_html');; ?>
</div>
</div>
<?php
}
?>

<header id="page-header-wrapper" <?php if($fixedheader) { ?> style="position: fixed;" <?php } ?>>
    <style type="text/css">
        span.circle{
         height: 18px;
         width: 18px;
         border-radius: 20px;
         font-size: 8px;
         background-color: white;
         border: 1px solid red;
         color: red;
         text-align: center;
         float: left;
         vertical-align: top;
         font-weight:bold;
        }
    </style>
    <?php if(1) { ?>
    <div>
    <?php } else { ?>
    <div id="above-header">
    <?php } ?>
        <div class="clearfix container userhead"  >

            <div class="pull-left">
                <?php echo $OUTPUT->user_menu(); ?>
            </div>
            <div class="headermenu row">
                <?php if (!isloggedin() || isguestuser()) { ?>
                    <?php echo $OUTPUT->login_info() ?>
                <?php } else { ?>

                    <div class="dropdown secondone">
                        <a id="slink" class="dropdown-toggle usermendrop" data-toggle="dropdown" data-target=".secondone">
                        <span class="fa fa-user"></span><?php echo fullname($USER) ?> 
                        <span class="fa fa-angle-down"></span>
                        </a>
                        <ul class="dropdown-menu usermen" role="menu">
                            <?php if (!empty($PAGE->theme->settings->enablemy)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/my" title="My Dashboard"><i class="fa fa-dashboard"></i><?php echo get_string('myhome') ?></a></li>
                            <?php } ?>
                            <?php if (!empty($PAGE->theme->settings->enableprofile)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/user/profile.php" title="View profile"><i class="fa fa-user"></i><?php echo get_string('viewprofile') ?></a></li>
                            <?php } ?>
                            <?php if (!empty($PAGE->theme->settings->enableeditprofile)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/user/edit.php" title="Edit profile"><i class="fa fa-cog"></i><?php echo get_string('editmyprofile') ?></a></li>
                            <?php } ?>
                            <?php if (!empty($PAGE->theme->settings->enableprivatefiles)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/user/files.php" title="private files"><i class="fa fa-file"></i><?php echo get_string('privatefiles', 'block_private_files') ?></a></li>
                            <?php } ?>
                            <?php  if (!empty($PAGE->theme->settings->enablebadges)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/badges/mybadges.php" title="badges"><i class="fa fa-certificate"></i><?php echo get_string('badges') ?></a></li>
                            <?php } ?>
                            <?php if (!empty($PAGE->theme->settings->enablecalendar)) { ?>
                                <li><a href="<?php p($CFG->wwwroot) ?>/calendar/view.php" title="Calendar"><i class="fa fa-calendar"></i><?php echo get_string('pluginname', 'block_calendar_month') ?></a></li>
                            <?php } ?>
                            <?php 
                            
                                echo "<li><a href=\"".$CFG->wwwroot."/login/logout.php\" title=\"Log out\"><i class=\"fa fa-lock\"></i>".get_string('logout')."</a></li>";
                                //訪客身分登出按鈕隱藏
                                //修改需求。lux.s 20160527 
                                    echo "<script>$('#slink').html('');</script>";    
                            ?>
                        </ul>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="page-header" class="clearfix container" style="height:160 px;">
    <?php
    if (isset($PAGE) && !$PAGE->theme->settings->sitetitle) {
        $header = theme_bcu_remove_site_fullname($PAGE->heading);
        $PAGE->set_heading($header);
    }
    ?>
     <div class='head_banner' style="background-color: #AFDCFA;">

         <style type="text/css" media="screen">
        
        .count p {
        display: block;
        -webkit-margin-before: 1em;
        -webkit-margin-after: 1em;
        -webkit-margin-start: 0px;
        -webkit-margin-end: 0px;
    }
         </style>
    <?php if(1) { ?>
    <a href="#content" class="to_content" title="跳到主要內容區塊">跳到主要內容區塊</a>
    <div class="loadbar"></div>

    <div id="wrapper">
        <!--header-->
        <div id="header">

          <div class="inner-width">
              <a href="#Accesskey_U" id="Accesskey_U" accesskey="U" title="上方選單連結區，此區塊列有本網站的主要連結">:::</a>
              <h1><div class="logo" style="z-index:999"><a href="https://elearning.taipei/mpage/home"><img src="https://elearning.taipei/mpage/system/application/assets/new/images_newest/logo.png" alt="臺北e大數位學習網logo"/></a></div></h1>
              <div class="top_menu">
                      <ul class="nav">
                        <?php if(isloggedin() and !isguestuser()){ ?>
                          <li><a id="pocket-link" href="https://elearning.taipei/mpage/course/pocket"><i class="fa fa-get-pocket" aria-hidden="true"></i> 選課口袋</a></li>
                        <?php } ?>  

                        <li><a href="https://elearning.taipei/mpage/home/sitemap"><i class="fa fa-sitemap" aria-hidden="true"></i> 網站導覽</a></li>
                        <?php
                          $db_ip = "172.25.154.75";
                          $db_username = "nelearn";
                          $db_password = "L@admin01!";
                          $db_name1 = "mpage";
                          $db_name2 = "moodle";

                          $link1 = new mysqli($db_ip, $db_username, $db_password, $db_name1); //mpage
                          $link2 = new mysqli($db_ip, $db_username, $db_password, $db_name2); //moodle
                          if (!$link2 || !$link1) {
                              die('connect error');
                          }

                          mysqli_set_charset($link1, "utf8");
                          mysqli_set_charset($link2, "utf8");

                          if($USER->id == 21){
                            echo '<li><a href="#"><i class="fa fa-sign-in" aria-hidden="true"></i> 訪客</a></li>';
                          } else {
                            if($USER->id > 0){
                              $sql = sprintf("insert into mdl_fet_user_behavior(uid,pages,search_time) values('%s','%s','%s')",$USER->id,$_SERVER['REQUEST_URI'],time());
                              mysqli_query($link2, $sql);
                            }
                            

                            $sql = sprintf("select count(1) cnt from mdl_role_assignments where userid = '%s' and roleid in (1,11)",$USER->id);

                            $result = mysqli_query($link2, $sql);
                            $row = $result->fetch_object();

                            if($row->cnt > 0){
                              $m_cnt = 'admin';
                              $path = "https://elearning.taipei/eda/manage/Instant_message";
                            } else {
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
                                              )",$USER->id,$USER->id);

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

                            echo '<li class="submenuheader">';
                            echo '<a href="'.$CFG->wwwroot.'/login/logout.php"><i class="fa fa-sign-in" aria-hidden="true"></i> '.$USER->profile['communityname'].'(登出)</a>';
                            echo '</li>';

                            echo '<li>';
                            echo '<a href="'.$path.'" target="_blank" title="另開新視窗"><img alt="E-mail" style="vertical-align:middle;height:15px;margin-top:-4px;display: initial;" src="https://elearning.taipei/mpage/webfile/userfiles/icon/icon/'.$m_cnt.'.png"></a>';
                            echo '</li>';
                          }
                        ?>
                        <li><a href="https://www.facebook.com/elearning.taipei/" target="_blank" title="臺北e大樂在學習臉書"><i class="fa fa-facebook-official" style="font-size: 16px" aria-hidden="true"></i><span id="fb_fans">臺北e大粉絲頁</span></a></li>
                      </ul>
                      <ul class="main">
                      <?php
                        $sql_chk = sprintf("select ecpa,env,edu from mdl_fet_cert_setting where uid = '%s'",$USER->id);
                        $result_chk = mysqli_query($link2, $sql_chk);
                        $row_chk = $result_chk->fetch_object();
                        if(!empty($row_chk->ecpa) || !empty($row_chk->env) || !empty($row_chk->edu)){
                          $identity = '1';//公務人員
                        } else {
                          $identity = '2';//一般民眾
                        }

                        if($USER->id != 21 && $identity == '1'){
                          echo '<li class="submenuheader">';
                          echo '<a href="#">我的課程</a>';
                          echo '<ul class="submenu">';
                          echo '<li><a href="'.$CFG->wwwroot.'/mod/url/view.php?id=84">學習紀錄</a></li>';
                          echo '<li><a target="_blank" title="另開新視窗" href="'.$CFG->wwwroot.'/sso/phy.php">實體班期專區</a></li>';
                          if(time() < 1554048000){
                            echo '<li><a href="https://elearning.taipei/sso/to.php?sitelink=forum">e大學習論壇</a></li>';
                          }
                          // if(date('Y-m-d') == '2019-03-15'){
                          //   echo '<li><a target="_blank" href="'.$CFG->wwwroot.'/sso/epa.php">ePA市政管理學苑</a></li>';
                          // }
                          echo '</ul></li>';
                          echo '<li class="submenuheader">';
                          echo '<a href="#">選課中心</a>';
                          echo '<ul class="submenu">';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/learn_type_list">公務員核心課程庫</a></li>';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
                          echo '<li><a href="http://elearning.taipei/elearn/fetpayment/apply_class.php">語言自費班期</a></li>';
                          echo '<li><a href="'.$CFG->wwwroot.'/course/courselist_export.php">課程清單下載</a></li>';
                          echo '<li><a href="'.$CFG->wwwroot.'/my/index.php?mytoctab=P2">研討/活動報名</a></li>';
                          echo '</ul></li>';
                          echo '<li>';
                          echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                          echo '</li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                          echo '<li class="submenuheader">';
                          echo '<a href="#">合作推廣</a>';
                          echo '<ul class="submenu">';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';
                          echo '<li><a href="https://elearning.taipei/goeplus.php">e等公務園+</a></li>';
                          echo '</ul></li>';
                          // if(time() < 1554048000){
                            echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank">鮮活電子報</a></li>';
                          // }
                          echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                          // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                        } else if($USER->id != 21 && $identity == '2'){
                          echo '<li class="submenuheader">';
                          echo '<a href="#">我的課程</a>';
                          echo '<ul class="submenu">';
                          echo '<li><a href="'.$CFG->wwwroot.'/mod/url/view.php?id=84">學習紀錄</a></li>';
                          echo '<li><a target="_blank" title="另開新視窗" href="'.$CFG->wwwroot.'/sso/phy.php">實體班期專區</a></li>';
                          if(time() < 1554048000){
                            echo '<li><a href="https://elearning.taipei/sso/to.php?sitelink=forum">e大學習論壇</a></li>';
                          }
                          // if(date('Y-m-d') == '2019-03-15'){
                          //   echo '<li><a target="_blank" href="'.$CFG->wwwroot.'/sso/epa.php">ePA市政管理學苑</a></li>';
                          // }
                          echo '</ul></li>';
                          echo '<li class="submenuheader">';
                          echo '<a href="#">選課中心</a>';
                          echo '<ul class="submenu">';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
  //                        echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
  //                        echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
  //                        echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
  //                        echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
  //                        echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
                          echo '<li><a href="http://elearning.taipei/elearn/fetpayment/apply_class.php">語言自費班期</a></li>';
                          echo '<li><a href="https://elearning.taipei/elearn/course/courselist_export.php">課程清單下載</a></li>';
                          echo '<li><a href="'.$CFG->wwwroot.'/my/index.php?mytoctab=P2">研討/活動報名</a></li>';
                          echo '</ul></li>';
                          echo '<li>';
                          echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                          echo '</li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                          echo '<li class="submenuheader">';
                          echo '<a href="#">合作推廣</a>';
                          echo '<ul class="submenu">';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';
                          echo '<li><a href="https://elearning.taipei/goeplus.php">e等公務園+</a></li>';
                          echo '</ul></li>';
                          // if(time() < 1554048000){
                            echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank" title="另開新視窗">鮮活電子報</a></li>';
                          // }
                          echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                          // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                        } else {
                          echo '<li class="submenuheader">';
                          echo '<a href="#">選課中心</a>';
                          echo '<ul class="submenu">';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_type_list">分類列表</a></li>';
    //                      echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/3">公務10小時專區</a></li>';
    //                      echo '<li><a href="https://elearning.taipei/mpage/home/view_news/1609">人權教育最前線</a></li>';
     //                     echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/1">臺北施政廣播站</a></li>';
     //                     echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/4">退休增職充電站</a></li>';
     //                     echo '<li><a href="https://elearning.taipei/mpage/home/view_customize_course/2">主題系列課程</a></li>';
                          echo '<li><a href="https://elearning.taipei/elearn/course/courselist_export.php">課程清單下載</a></li>';
                          echo '<li><a href="'.$CFG->wwwroot.'/my/index.php?mytoctab=P2">研討/活動報名</a></li>';
                          echo '</ul></li>';
                          echo '<li>';
                          echo '<a href="https://elearning.taipei/mpage/home/view_page/372">新手上路</a>';
                          echo '</li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_news_more">最新消息</a></li>';
                          echo '<li class="submenuheader">';
                          echo '<a href="#">合作推廣</a>';
                          echo '<ul class="submenu">';
//                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/820">電子報專欄合作</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/79">專班服務</a></li>';
                          echo '<li><a href="https://elearning.taipei/mpage/home/view_article/1119">教材提供與上架</a></li>';
                          echo '<li><a href="https://elearn.hrd.gov.tw">e等公務園+</a></li>';
                          echo '</ul></li>';
                          // if(time() < 1554048000){
                            echo '<li><a href="https://epaper.gov.taipei/Epaper_paperList.aspx?n=0FE5CCC71725D055&siteSN=E6BE3790C28B3B1D&categorySN=6A6B57F5FE966020" target="_blank" title="另開新視窗">鮮活電子報</a></li>';
                          // }
                          echo '<li><a href="https://elearning.taipei/mpage/home/feedback/11">客服中心</a></li>';
                          // echo '<li><a href="https://elearning.taipei/mpage/home/view_page/19">關於我們</a></li>';
                        }
                      ?>
                      </ul>
                      
                  <div class="clear"></div>
              </div>
              <?php
              //marquee
               echo '<script src="'.$CFG->wwwroot.'/theme/bcu/new/js_newest/libs/marquee_taipei.js"></script>';
              ?>
              <div class="marquee_box">
              <div  class="scroller">
                <div class="scroller-text" style="position: absolute; white-space: nowrap;">
                <?php
                  $sql_marquee = "SELECT
                                    link,name
                                  FROM
                                    fet_article
                                  WHERE
                                    category_id = 17
                                  AND ONLINE = 1
                                  AND (
                                    publish_on IS NULL
                                    OR publish_on < now()
                                  )
                                  AND (
                                    publish_off IS NULL
                                    OR publish_off > now()
                                  )
                                  ORDER BY
                                    ordering ASC";

                  if($result_marquee = mysqli_query($link1,$sql_marquee)){
                    while ($row_marquee = $result_marquee->fetch_row()){
                      //echo '<li><a href="'.$row_marquee[0].'">'.$row_marquee[1].'</a></li>';
                      
                      ?>
                            <span><a href="<?php echo $row_marquee[0];?>" title="<?php echo strip_tags($row_marquee[1]);?>(另開新視窗)" rel="noreferrer noopener" target="_blank" onfocus="$('.scroller').trigger('mouseover')" onblur="$('.scroller').trigger('mouseout')"><?php echo $row_marquee[1];?></a></span>
                      <?php
                      
                    }
                   
                    mysqli_free_result($result_marquee);
                    mysqli_close($link1);
                  }
                ?>
                </div></div>
                
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
      <div id="content">
      <div class="inner-width">
        <a href="#Accesskey_C" id="Accesskey_C" accesskey="C" title="中間主要內容區，此區塊呈現網頁的網頁內容">:::</a>
    <?php } else { ?>
        <?php $URL_logo = $CFG->fet.'/system/application/assets/new/images/logo_new.png'; ?>
        <span class='head_banner_left' >
            <h1><a href="/mpage/home"><img src="<?php echo "$URL_logo"; ?>" alt=""></a></h1>
        </span>

        <span class='head_banner_main' >
           <img id='imgNow' src="/mpage/system/application/assets/new/images/header_morning.png" alt="">
        </span>
    <?php } ?>
<?php if(1) { ?>

<?php } else { ?>
<!-- <span class='head_banner_right' > -->
<span style='display: inline-block; width: 30%; height: 100px;'>    
<!-- 限制高度。 -->
<?php } ?>
<?php 

// 如果IE8 無法呈現效果，修改樣式。
echo '
<script type="text/javascript">
$(function() {
    var isIE = navigator.userAgent.search("MSIE") > -1;
    if (isIE) 
    {
        $("#logout_dropdown").hide();
        $("#ie8_logout").show();
    }
});
</script>
';
if(1){

} else {
$login_path = "http://elearning.taipei/mpage/system/application/assets/new/images/login_icon.png";
    
    $logout_html = '訪客 |'; //訪客沒有登出功能
    if($USER->id!=21) {
        
            $db_ip = "172.25.154.75";
            $db_username = "nelearn";
            $db_password = "L@admin01!";
            $db_name2 = "moodle";

            $link2 = new mysqli($db_ip, $db_username, $db_password, $db_name2); //moodle
            if (!$link2) {
                die('connect error');
            }

            mysqli_set_charset($link2, "utf8");

            $sql = sprintf("select count(1) cnt from mdl_role_assignments where userid = '%s' and roleid in (1,11)",$USER->id);

            $result = mysqli_query($link2, $sql);
            $row = $result->fetch_object();

            if($row->cnt > 0){
              $m_cnt = 'admin';
              $path = "https://elearning.taipei/eda/manage/Instant_message";
            } else {
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
                              )",$USER->id,$USER->id);

              $result2 = mysqli_query($link2, $sql2);
              $row2 = $result2->fetch_object();
            

              if($row2->cntt > 0){
                if($row2->cntt > 9){
                  $m_cnt = "9+";
                } else {
                  $m_cnt = $row2->cntt;
                }
              } else {
                $m_cnt = '0';
              }
              
              $path = "https://elearning.taipei/eda/manage/Instant_message_receive/";
            }

        
            $logout_html = '<a href="'.$path.'" target="_blank"><img alt="E-mail" height="30px" src="http://elearning.taipei/mpage/webfile/userfiles/icon/icon/'.$m_cnt.'.png"></a><a style="display:none; font-size:14px;" id= "ie8_logout" href="'.$CFG->wwwroot.'/login/logout.php">'.$USER->profile['communityname'].'(登出) <img src="'.$login_path.'">|</a> 
            <div class="dropdown secondone" id ="logout_dropdown" style="display: inline;">
                 <a href="#" class="dropdown-toggle" style ="font-size:14px;" data-toggle="dropdown">'.$USER->profile['communityname'].'</a> 
                 <img src="'.$login_path.'"> |
                 <ul class="dropdown-menu usermen" style="">
                    <li>
                        <a style="color: black;"  href="'.$CFG->wwwroot.'/login/logout.php">登出</a>
                    </li>
                 </ul>
            </div>';
       
       
    }
  
      echo '
      <div  style="text-align: right; float: right;">
          '.$logout_html.'
         <a href="/mpage/home/view_page/19" style ="float:right;margin-top:3px"> 關於我們 |</a> 

            <br>
            <div style="float: right;">
               <a href="/mpage/home/feedback/11">客服中心</a> | 
               <a href="/mpage/home/view_page/15">網站導覽</a> |
           </div>
          ';

          echo '
           <div class="count" style="margin-top: 40px;">
              <p id="p1"> 目前在線人數 ：0 人</p>
              <p id="p2"> 總瀏覽數 : 0 人</p>
          </div>
      </div>
      ';
   
}

// else
// {
//     echo '<a href="/mpage/home/view_page/19">關於我們</a> | <a href="/mpage/home/feedback/11">客服中心</a></li><a href="/mpage/home/feedback/11"><a>
//             <a href="/mpage/home/feedback/11"></a> | <a href="/mpage/home/view_page/15">網站概覽</a>
//             </li>
//             <a href="/mpage/home/"><img src="/mpage/system/application/assets/new/images/home_01.png" alt="">回首頁</a></li>';
//     echo '
//     <div class="count">
//         <p id="p1"> 目前在線人數 ：0 人</p>
//         <p id="p2"> 總瀏覽數 : 0 人</p>
//     </div>';
// }
?>
       

           
             <div id="edittingbutton" class="pull-right breadcrumb-button">
      <?php 
        if(preg_match("/elearn\/mod\/scorm\/player.php/", $_SERVER['REQUEST_URI'])) { //在scorm player下改變按鈕
          echo "<input type=\"button\" title=\"回到我的課程\" value=\"回到我的課程\" onclick=\"javascript:location.href='http://elearning.taipei/elearn/transformation.php?fun=courserecord'\" />";
          echo "　<input type=\"button\" title=\"離開課程\" value=\"離開課程\" onclick=\"javascript:location.href='http://elearning.taipei/elearn/transformation.php?fun=courseview&courseid=$COURSE->id'\" />";
        }
        else {
          if(1) {
  
          } else {
            echo $OUTPUT->page_heading_button(); 
          }

          
        }
      ?>
             </div>
         </span>
  <script>
        // 由於強制採用 https 因此，必須改用跨網域方式執行ajax lux.s
        $.ajax({
            type: 'GET',
            url: 'https://elearning.taipei/mpage/home/getonline/',
            crossDomain: true,
            dataType: 'text',
            success: function(data, textStatus, jqXHR) {
                var cAry = data.split(",");
                if(cAry[0]>0&&cAry[0]!=="") {
                    $("#p1").html("目前在線人數 ："+cAry[0]+"人");
                }
                if(cAry[1]>0&&cAry[1]!=="") {
                    $("#p2").html("總瀏覽數 ："+cAry[1]+"人");
                }
            }
        });

    // $.get( "http://elearning.taipei/mpage/home/getonline", function( data ) {
    //  var cAry = data.split(",");
    //  if(cAry[0]>0&&cAry[0]!=="") {
    //    $("#p1").html("目前在線人數 ："+cAry[0]+"人");
    //  }
    //  if(cAry[1]>0&&cAry[1]!=="") {
    //    $("#p2").html("總瀏覽數 ："+cAry[1]+"人");
    //  }
    // });
    </script>
    
    <script type="text/javascript">
    var d   = new Date();
    var h   = d.getHours();
    
    
     if(h>=5 && h<15) {
      document.getElementById("imgNow").src=  "<?php echo '/mpage/system/application/assets/new/images/header_morning.png'; ?>";
     }else if(h>=15&&h<18){
      document.getElementById("imgNow").src=  "<?php echo '/mpage/system/application/assets/new/images/header_afternoon.png'; ?>";
     }else{
      document.getElementById("imgNow").src=  "<?php echo '/mpage/system/application/assets/new/images/header_night.png'; ?>";
     }
    
  </script>


     </div>
        <!-- <div class="searchbox">
            <form action="<?php p($CFG->wwwroot) ?>/course/search.php">
                <label class="hidden" for="search-1" style="display: none;">Search iCity</label>
                <div class="search-box grey-box bg-white clear-fix">
                    <input placeholder="<?php echo get_string("searchcourses")?>" accesskey="6" class="search_tour bg-white no-border left search-box__input ui-autocomplete-input" type="text" name="search" id="search-1" autocomplete="off">
                    <button type="submit" class="no-border bg-white pas search-box__button"><abbr class="fa fa-search"></abbr></button>
                </div>
            </form>
        </div> -->

        <!-- <div id="course-header">
            <?php echo $OUTPUT->course_header(); ?>
        </div> -->
    </div>
    
<?php if(1) { ?>
   <style type="text/css">
      html, body {
        background-color: #fff; 
      }

      #page-content {
        background-color: #fff; 
      }
   </style>
<?php } ?>
</header>
