<?php
/**
 * Lists the course records(history)
 *
 * @package course
 */

require_once("../config.php");
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once($CFG->dirroot. '/courserecord/lib.php');
// require_once($CFG->libdir. '/coursecatlib.php');
// require_once($CFG->dirroot. '/functions.php');
require_once('../customize/edaold.php');

if(false) {
    if(isset($_GET['repeat']) && $_GET['repeat'] == '1'){
        echo '<script>
                var act = confirm("系統偵測到您同時間上多門課，即將導回我的課程頁");
                if(confirm){
                    location.href = "http://elearning.taipei/elearn/courserecord/index.php";
                } else {
                    location.href = "http://elearning.taipei/elearn/courserecord/index.php";
                }
                
            </script>';

    } else if(isset($_GET['repeat']) && $_GET['repeat'] == '2'){
        echo '<script>
                var act = confirm("發生錯誤，將導回我的課程頁，請重新進入課程");
                if(confirm){
                    location.href = "http://elearning.taipei/elearn/courserecord/index.php";
                } else {
                    location.href = "http://elearning.taipei/elearn/courserecord/index.php";
                }
            </script>';
    }
}


//error_reporting(E_ALL);
if($USER->id != 21) {
  if(!isset($_SESSION['update_study_time'])){
    $_SESSION['update_study_time'] = time();
    update_study_time($USER->id);
  } else {
    $t = time();
    if($t-$_SESSION['update_study_time'] > 600){
        $_SESSION['update_study_time'] = time();
        update_study_time($USER->id);
    }
  } 
} 

$site = get_site();


$PAGE->set_title($SITE->fullname. ': 我的課程');
$PAGE->set_context(context_system::instance());

// customized nav-bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('我的課程', new moodle_url('/courserecord/index.php'));
// $PAGE->navbar->add(get_string('name of thing'), new moodle_url('/courserecord/index2.php'));


// $PAGE->set_pagelayout('frontpage');
$PAGE->set_pagelayout('standard'); //顯示上面灰階導覽使用

$PAGE->set_heading($site->fullname);

require_login();

// $recordrenderer = $PAGE->get_renderer('core', 'courserecord');
// $content = $recordrenderer->course_category($categoryid);
if($USER->id==21) {//不允許訪客存取
    header("Location:../course/index.php");
}
// 限制訪客存取
if($USER->username=='guest'){
    throw new Exception('不允許訪客存取，請登入');
}

$pagenum   = optional_param('pagenum', 0, PARAM_INT); // page number
// $isOldY     = optional_param('oldY', '', PARAM_TEXT); // 是否為留存紀錄
// $isOld     = optional_param('old', 0, PARAM_INT); // 是否為舊版
$isMode    = optional_param('mode', 0, PARAM_INT); // 模式 預設0精簡, 1完整
$cStaus    = optional_param('cstatus', 0, PARAM_INT); // 課程狀態 0全部, 1完成, 2未完成
$classname = optional_param('ssearch', '', PARAM_TEXT); // 課程狀態 0全部, 1完成, 2未完成
$rUpdate   = optional_param('r', 0, PARAM_INT); // 更新學習紀錄
$list  = optional_param('list', 10, PARAM_INT);
$from_mode = optional_param('from_mode', 0, PARAM_INT);
$queryYear = optional_param('queryYear', -1, PARAM_INT);

if($queryYear == -1){
    $queryYear = date('Y')-1911;
}

$year_key = date('Y')-1912;

$isOld = $isOldY = false;
if ($queryYear >= 98 && $queryYear <=104){
    $isOld = true;
}elseif ($queryYear > 104 && $queryYear <= $year_key){
    $isOldY = true;
}

if($from_mode == '1') {
    $courseid   = optional_param('from_id', 0, PARAM_INT);
    
    $sql = "SELECT
                mdl_course_modules.id
            FROM
                mdl_course_modules mdl_course_modules
            JOIN mdl_feedback_completed mdl_feedback_completed ON mdl_feedback_completed.feedback = mdl_course_modules.instance
            WHERE
                mdl_course_modules.course = ?
            AND mdl_course_modules.module = 7
            AND mdl_course_modules.visible = 1
            AND mdl_feedback_completed.userid = ?";

    $check_feedback = $DB->get_records_sql_ng($sql, array($courseid,$USER->id));
    
    if(empty($check_feedback)){
        $sql = "SELECT id FROM mdl_course_modules WHERE course = ? and visible = 1 and module = 7";
        $feedback_id = $DB->get_records_sql_ng($sql, array($courseid));

        $feedback_url = 'http://elearning.taipei/elearn/show_feedback_link.php?id='.$feedback_id[0]->id.'&mode=2';
        echo "<script>
                window.open('".$feedback_url."', 'feedback', config='height=100,width=500,top=');
            </script>";
    }
} 

if($pagenum<1){
    $pagenum = 1;
}

$record_count = get_courserecord_count();
$limit = $CustomCFG->courserecord->pagerows;
$near = $CustomCFG->courserecord->nearpages;

echo $OUTPUT->header();

// 說明區塊
$opts = $div_html = $url = "";
// for($i=date("Y")-1911;$i<=date("Y")-1911;$i++) {
//  if($i==date("Y")-1911) {
//      $opts_seled = "selected='selected'";
//  }
//  else {
//      $opts_seled = "";
//  }
//  $opts .= "<option value='$i' $opts_seled>$i</option>\n";
// }

// $opts_old = "<option value='0'>請選擇年度</option>\n<!--<option value='-1' ".($isOld==-1?"selected='selected'":"").">全部</option>\n-->";
// //舊年度資料轉移 測試先寫死
// if("Y105"==$isOldY) {
//     $opts_seled = "selected='selected'";
// }
// else {
//     $opts_seled = "";
// }

// //2018年度轉換新增
// if("Y106"==$isOldY) {
//     $opts_seled_106 = "selected='selected'";
// }
// else {
//     $opts_seled_106 = "";
// }

// if("Y107"==$isOldY) {
//     $opts_seled_107 = "selected='selected'";
// }
// else {
//     $opts_seled_107 = "";
// }

// if("Y108"==$isOldY) {
//     $opts_seled_108 = "selected='selected'";
// }
// else {
//     $opts_seled_108 = "";
// }

// // 2021-01-05 edit
// if("Y109"==$isOldY) {
//     $opts_seled_109 = "selected='selected'";
// }
// else {
//     $opts_seled_109 = "";
// }

// if("Y110"==$isOldY) {
//     $opts_seled_110 = "selected='selected'";
// }
// else {
//     $opts_seled_110 = "";
// }

// $opts_old .= "<option value='Y110' $opts_seled_110>110</option>\n";

// $opts_old .= "<option value='Y109' $opts_seled_109>109</option>\n";

// $opts_old .= "<option value='Y108' $opts_seled_108>108</option>\n";

// $opts_old .= "<option value='Y107' $opts_seled_107>107</option>\n";

// $opts_old .= "<option value='Y106' $opts_seled_106>106</option>\n";

// $opts_old .= "<option value='Y105' $opts_seled>105</option>\n";
// for($i=104;$i>=98;$i--) {
//     if($i==$isOld) {
//         $opts_seled = "selected='selected'";
//     }
//     else {
//         $opts_seled = "";
//     }
//     $opts_old .= "<option value='$i' $opts_seled>$i</option>\n";
// }

$URL =$CFG->wwwroot.'courserecord/index.php';

$r1box_s1 = $r1box_s2 = "";
if($isMode==1) {
    $r1box_s2 = 'checked="checked"';
}
else {
    $r1box_s1 = 'checked="checked"';
}

$r2box_s1 = $r2box_s2 = $r2box_s3 = "";
if($cStaus==1) {
    $r2box_s2 = 'checked="checked"';
}
elseif($cStaus==2) {
    $r2box_s3 = 'checked="checked"';
}
else {
    $r2box_s1 = 'checked="checked"';
}

// if($isOld==0) {
//  $div_html = "顯示：<input type=\"radio\" name=\"mode\" id=\"r1s1\" value=\"0\" $r1box_s1 /> 精簡模式
//  <input type=\"radio\" name=\"mode\" id=\"r1s2\"id=\"r2s1\" value=\"1\" $r1box_s2 />完整模式 <br><br>
//  課程：<input type=\"radio\" name=\"cstaus\" id=\"r2s1\" value=\"0\" $r2box_s1 />全部
//  <input type=\"radio\" name=\"cstaus\" id=\"r2s2\" value=\"1\" $r2box_s2 />已完成
//  <input type=\"radio\" name=\"cstaus\" id=\"r2s3\" value=\"2\" $r2box_s3 />未完成 <br><br>";
// }

// echo <<<EOF
// <div class="hv_studyrecord_head_cmmt">
//  <p style="font-weight:bold;margin-top:10px;margin-bottom:3px">課程作業規定</p>

//  <ul>
//  <li>
//  1. 選課：每門課程開設一期全年班，自 1 月 1 日或開課日起，至 12 月 31 日止，開放學員隨時選課。
//  課程開放後由學員自由選課，隨報隨上，選課當日即可登入「我的課程」上課去。
//  </li>
//  <li>
//  2. 異動：課程一經報名選課，且有時數則概不受理異動申請。
//  </li>
//  </ul>
// <hr>
// <select name="select_old" id="select_old">$opts_old</select>
// <input type="button" value="顯示舊平台紀錄" onclick="changeold()">　　
// <select name="select">$opts</select>
// <a href=""><input type="button" value="更新我的課程"></a><br><br>
// <input type="text" size="10" id="ssearch" name="ssearch" value="$classname" placeholder="課程名稱" /> <input type="button" id="search" value="課程搜尋"><br>
// $div_html
// </div>
// EOF;

$r2box_s1 = $r2box_s2 = $r2box_s3 = '';
if ($cStaus == 0){
    $r2box_s1 = 'checked';
}elseif ($cStaus == 1){
    $r2box_s2 = 'checked';
}elseif ($cStaus == 2){
    $r2box_s3 = 'checked';
}else{
    $r2box_s1 = 'checked';
}

$queryData = array();
$queryData['course_name'] = $classname;
$queryData['sort'] = array(
    'sortField' => optional_param('sortField', '', PARAM_TEXT),
    'sorttype' => optional_param('sorttype', '', PARAM_TEXT)
);

$yearOption = "";



for($year = $year_key; $year >= 98; $year--){
    $selected = $queryYear == $year ? 'selected' : '';
    $yearOption .= "<option value=\"".$year."\" ".$selected.">".$year."</option>";
}

$yearSearchForm = '

<select name="queryYear">
    <option value="-1">請選擇年度</option>
    '.$yearOption.'
</select>
<input type="submit" value="顯示舊平台紀錄">　　
';

$courseSearch = "
<input type=\"text\" size=\"20\" id=\"ssearch\" name=\"ssearch\" value=\"$classname\" placeholder=\"課程名稱\" />
<input type=\"submit\" id=\"search\" value=\"課程搜尋\">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
";

if($isOld) {
    $div_html = "<table width='100%'>
                <tbody>
                    <tr>                   
                        <td style=\"width:30%;vertical-align:middle;\">
                            <p>課程：<input type=\"radio\" name=\"cstatus\" id=\"r2s1_old\" value=\"0\" $r2box_s1 />全部
                                    <input type=\"radio\" name=\"cstatus\" id=\"r2s2_old\" value=\"1\" $r2box_s2 />已完成
                                    <input type=\"radio\" name=\"cstatus\" id=\"r2s3_old\" value=\"2\" $r2box_s3 />未完成
                            </p>
                        </td>
                        <td style=\"width:37%;text-align:center;vertical-align:bottom;\" >
                        </td>
                    </tr>
                    <tr>
                        <td width='42%' style='text-align:left'>
                            $yearSearchForm
                        </td>                    
                        <td width='42%'>$courseSearch</td>                    
                        <td width='16%' style='text-align:right'>
                            <input type='button' onclick='printpdf($USER->id,$queryYear)' value='列印證明'>
                        </td>
                    </tr>
                </tbody>
                </table>                    
                ";
               
    $paginate_html = showPage_old_new($list, $queryYear, $USER->id, $cStaus, $pagenum, $queryData)."<br>";       
}elseif ($isOldY){
    $div_html = "<table width='100%'>
    <tbody>
        <tr>
            <td style=\"width:30%;vertical-align:bottom;\">
                <p>課程：<input type=\"radio\" name=\"cstatus\" id=\"r2s1_OldY\" value=\"0\" $r2box_s1 />全部
                        <input type=\"radio\" name=\"cstatus\" id=\"r2s2_OldY\" value=\"1\" $r2box_s2 />已完成
                        <input type=\"radio\" name=\"cstatus\" id=\"r2s3_OldY\" value=\"2\" $r2box_s3 />未完成
                </p>
            </td>
            <td style=\"width:37%;text-align:center;vertical-align:bottom;\" >
            </td>
        </tr>
        <tr> 
        <td width='42%' style='text-align:left'>
            $yearSearchForm
        </td>
        <td width='42%'> $courseSearch</td>
        <td width='16%' style='text-align:right'>
            <input type='button' onclick='printpdf()' value='列印證明'>
        </td></tr>
        </tbody></table>                   
    ";

    $paginate_html = showPage_oldY_new($list, $queryYear, $USER->id, $cStaus, $pagenum, $queryData)."<br>";      
}else{
    $paginate_html = null;
    $div_html = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:86%;\">
    <tbody>
        <tr>
            <td style=\"width:30%;vertical-align:top;\">
                <p>課程：<input type=\"radio\" name=\"cstatus\" id=\"r2s1\" value=\"0\" $r2box_s1 />全部
                        <input type=\"radio\" name=\"cstatus\" id=\"r2s2\" value=\"1\" $r2box_s2 />已完成
                        <input type=\"radio\" name=\"cstatus\" id=\"r2s3\" value=\"2\" $r2box_s3 />未完成
                </p>
            </td>
            <td rowspan=\"2\" style=\"width:37%;text-align:center;vertical-align:bottom;\" >
            <input type=\"text\" size=\"20\" id=\"ssearch\" name=\"ssearch\" value=\"$classname\" placeholder=\"課程名稱\" />
            <input type=\"submit\" id=\"search\" value=\"課程搜尋\">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp            
            </td>
            <td rowspan=\"2\" style=\"width:30%;text-align:center;\" >
                <p>
                    <a href=\"#\"><input type=\"button\" onclick='reflash()' value=\"更新我的課程\" style=\"zoom: 1.5;font-size:18px\"></a>
                </p>
            </td>
        </tr>
        <tr>
            <td style=\"width:30%;vertical-align:bottom;\">
                <p>
                    顯示：<input type=\"radio\" name=\"mode\" id=\"r1s1\" value=\"0\" $r1box_s1 /> 精簡模式
                    <input type=\"radio\" name=\"mode\" id=\"r1s2\"id=\"r2s1\" value=\"1\" $r1box_s2 />完整模式
                </p>
            </td>
        </tr>
    </tbody>
    </table>";
}

$div_html .= "<input type=\"hidden\" name=\"list\" value=\"".intVal($list)."\">";

if($isOld) {
    $yearSearchForm = null;
}elseif ($isOldY){
    $yearSearchForm = null;
}else{
    $yearSearchForm = "<form id='searchYearForm'>$yearSearchForm</form>";
}

//print tabs
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require($CFG->dirroot . '/blocks/mytoc/tabs.php');
$print_tabs = print_tabs([$top], 2, null, null, true);

echo <<<EOF
$print_tabs

<div class="hv_studyrecord_head_cmmt">
    <p style="font-weight:bold;margin-top:10px;margin-bottom:3px">課程作業規定</p>
    <ul>
    <li>
    1. 選課：每門課程開設一期全年班，自 1 月 1 日或開課日起，至 12 月 31 日止，開放學員隨時選課。
    課程開放後由學員自由選課，隨報隨上，選課當日即可登入「我的課程」上課去。
    </li>
    <li>
    2. 異動：課程一經報名選課，且有時數則概不受理異動申請。
    </li>
    </ul>
    <hr>
    <div style="text-align:center;"> $paginate_html</div>
    $yearSearchForm   
    <form id="searchForm"> 
        <!-- <input type="hidden" name="queryYear" value="$queryYear">-->
        <br>
        $div_html
    </form>
</div>
EOF;



// echo '<hr/>';
// 一般課程清單
//echo '<p style="font-weight:bold;margin-top:10px;margin-bottom:3px">一般課程</p>';



$idnoshow = "<div style=\"text-align:right\">
                <input type=\"checkbox\" style=\"width:30px; height:20px\" value=\"1\" id=\"idnoshow\">身分證不隱碼
            </div>";

if($isOld) {
    //顯示舊平台紀錄
    $obj = new DB_edaold();
    $yearHour = $obj->getEnrollHourByYear($queryYear + 1911, $USER->id);

    echo '<div style="white-space: nowrap;">
            <div style="text-align: left;width: 50%;float: left;/* white-space: nowrap; */">
              已報名課程總時數： '.$yearHour['hours'].' 小時　　　已完成課程時數： '.$yearHour['completehours'].' 小時
            </div>    
            <div style="text-align:right;width: 50%;float: left;">
              <input type="checkbox" style="width:30px; height:20px" value="1" id="idnoshow">身分證不隱碼
            </div>
          </div>';
          
    echo get_courserecord_old($cStaus,$queryYear,($pagenum-1)*$list,$list, $queryData);
}elseif ($isOldY){
    $comp_total_hour = get_course_total_hours($USER->id,'complete');
    $reg_total_hour = get_course_total_hours($USER->id,'reg');
    $ary = queryUserUploadHours($USER->id, $queryYear); //查詢對應學員課程的人工核發時數
    $ecap_hour = get_ecpa_gothours($USER->id, $queryYear); //取得ECPA 總時數。

    // history備份歷年資料
    // show CheckBox        
    $yearHour = get_course_total_hoursOldYear($USER->id, $queryYear + 1911);
    // $yearHour = empty($yearHour) ? 0 : $yearHour;

    echo '<div style="white-space: nowrap;">
            <div style="text-align: left;width: 50%;float: left;/* white-space: nowrap; */">
              已報名課程總時數： '.$yearHour->hours.' 小時　　　已完成課程時數： '.$yearHour->completehours.' 小時
            </div>    
            <div style="text-align:right;width: 50%;float: left;">
              <input type="checkbox" style="width:30px; height:20px" value="1" id="idnoshow">身分證不隱碼
            </div>
          </div>';
    // 不同年度做法不同 
    if ( $isOldY=='Y109' ) {
        //echo showPage_fix_year($list,$USER->id, $cStaus, $pagenum,'',$isOldY)."<br>"; 
        //echo get_courserecord_newYear(($pagenum-1)*$list, $cStaus, $list, $rUpdate,$isOldY);
        echo get_courserecordYear(($pagenum-1)*$list,$list,$queryYear,$cStaus, $queryData);
    } else {
        echo get_courserecordYear(($pagenum-1)*$list,$list,$queryYear,$cStaus, $queryData);
    }
    //echo showPage_fix($USER->id, $cStaus, $pagenum,$classname); //分頁
    echo "已報名課程總時數:".$yearHour->hours."小時" ." ". "已完成課程總時數:".$yearHour->completehours."小時<br>";
    echo "已上傳認證時數總時數<br>
    行政院人事行政總處： $ecap_hour 小時<br>
    環境教育終身學習網： $ary[1] 小時<br>
    全國教師在職進修網： $ary[2] 小時<br>";
}else{
    $comp_total_hour = get_course_total_hours($USER->id,'complete');
    $reg_total_hour = get_course_total_hours($USER->id,'reg');
    $ary = queryUserUploadHours($USER->id); //查詢對應學員課程的人工核發時數
    $ecap_hour = get_ecpa_gothours($USER->id); //取得ECPA 總時數。

    if($classname=='') {
        echo showPage_fix_new($list,$USER->id, $cStaus, $pagenum,$classname)."<br>"; //分頁
        echo $idnoshow;
        echo get_courserecord(($pagenum-1)*$list, $cStaus, $list, $rUpdate); //我的課程資料
        // echo get_courserecord_pages($pagenum, $record_count, $limit, $near);
        echo showPage_fix_new($list,$USER->id, $cStaus, $pagenum,$classname,'Y'); //分頁
    }else {
        echo showPage_fix_new($list,$USER->id, $cStaus, $pagenum,$classname)."<br>"; //分頁
        echo $idnoshow;
        echo get_courserecord1($classname,$cStaus,($pagenum-1)*$list,$list, $rUpdate);
        echo showPage_fix_new($list,$USER->id, $cStaus, $pagenum,$classname,'Y'); //分頁
    }
    // 分頁區塊
    echo "已報名課程總時數:".$reg_total_hour."小時" ." ". "已完成課程總時數:".$comp_total_hour."小時<br>";
    echo "已上傳認證時數總時數<br>
    行政院人事行政總處： $ecap_hour 小時<br>
    環境教育終身學習網： $ary[1] 小時<br>
    全國教師在職進修網： $ary[2] 小時<br>"; // 2019 05 02 鵬 原本 全國教師終身學習網 改為  全國教師在職進修網
}
    //======== 認證時數加總

echo '<hr/>';

$url = "list=".$list."&mode=0"./*"&pagenum=".$pagenum.*/"&cstatus=";
$search_url = "list=".$list."&cstatus=";

echo <<<EOF
<script>
var URL = "index.php?$url";
var SEARCH_URL = "index.php?$search_url";
if($("#r1s1").prop("checked")){
    $(document).ready(function() {
        $('.simplify').hide();
    });
}
if($("#r1s2").prop("checked")){
    $(document).ready(function() {
        $('.simplify').show();
    });
}
function openNode(uid, cid) {
    window.open('nodeHistory.php?u='+uid+'&c='+cid, '123', 'height=280,width=420,resizable=yes,scrollbars=yes,location=no,status=no');
}
function changeold() {
    $("#searchYearForm").submit();
}

//顯示模式
$("#r1s1").click(function (){
    $('.simplify').hide();
});
$("#r1s2").click(function (){
    $('.simplify').show();
});

$("input[name=mode]").change(function(){
    var pageBtn = $("a.paginate");
    let selectMode = $("input[name=mode]:checked").val();
    for(let i=0;i<pageBtn.length; i++){
        let urlParams = new URLSearchParams(pageBtn[i].href);
        urlParams.set('mode', selectMode);
        pageBtn[i].href = decodeURIComponent(urlParams.toString());
    }
});

$("#data_count").change(function(){
    $("#searchForm input[name=list]").val(this.value);
    $("#searchForm").submit();
}); 

$("#data_count_bottom").change(function(){
    $("#searchForm input[name=list]").val(this.value);
    $("#searchForm").submit();
});

// 課程狀態選擇
$("input[name=cstatus]").change(function (){
    $("#searchForm").submit();
});

function reflash() {
    if('http://elearning.taipei/elearn/courserecord/index.php'==window.location.href) {
        location.href=window.location.href+'?r=1';
    }
    else {
        location.href=window.location.href+'&r=1';
    }
}

</script>
EOF;

?>

<script>
    $(".sort").click(function(e){
        e.preventDefault();
        console.log($(this).data());
        let urlParams = new URLSearchParams(location.href);
        urlParams.set('sortField', $(this).data().fieldname);
        urlParams.set('sorttype', $(this).data().sorttype);
        console.log(decodeURIComponent(urlParams.toString()));
        location.href = decodeURIComponent(urlParams.toString());
    });
</script>

<?php 
echo $OUTPUT->footer();
?>
