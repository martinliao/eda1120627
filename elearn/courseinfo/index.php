<?php

require_once("../config.php");
require_once('lib.php');

error_reporting(E_ALL);

$site = get_site();

$PAGE->set_title($SITE->fullname. ': 課程資訊');
//$PAGE->set_title(': 課程資訊');  //0602test
$PAGE->set_context(context_system::instance());

// customized nav-bar
$PAGE->navbar->ignore_active();
$PAGE->navbar->add('主選單', new moodle_url('/?redirect=0'));
// $PAGE->navbar->add('課程','index.php');
$PAGE->navbar->add('課程資訊');

$PAGE->set_pagelayout('standard');
$PAGE->set_heading($site->fullname);

require_login();

// $cid          = optional_param('id', 0, PARAM_INT);
if(!empty($_GET['courseid'])) {
  $cid = intval($_GET['courseid']);
}


$check_course = check_course_isvisible($cid);

if(empty($check_course)){
  echo '<script>
          alert("此課程不存在，點選確定後，將導轉回首頁");
          location.href="https://elearning.taipei/mpage/home";
        </script>';
}


// $URL=$pre_url=$_SERVER['HTTP_REFERER'];    //讀取前一頁透過連結過來的網址

//   if(strstr($URL,'elearn/enrol/index.php'))
//   {
//     redirect('/elearn/course/view.php/?id='.$cid);
//   }

// else
// {
//    throw new Exception('courseid not found');
// }
// $cid =84;
$ecpa_name = get_ecpa_name($cid);

$course =  getCourse($cid);

$courseinfo = getCourse_Info($cid);

if(preg_match("/^211.79.136.20[2,3,4,5,6]$/", $_SERVER["REMOTE_ADDR"]) || preg_match("/^163.29.35.[0-9]*[0-9]*[0-9]*$/", $_SERVER["REMOTE_ADDR"])) {
  $course_extend = getCourseExtend($cid);
  if(isset($course_extend->subscription) && $course_extend->subscription=='1'){
    if(isset($course->startdate) && !empty($course->startdate)){
      $subscription_startdate = date('Y-m-d',$course->startdate);
      $subscription_enddate = date('Y-m-d',($course->startdate+777600));

      $today = date('Y-m-d');
      if(strtotime($today) >= strtotime($subscription_startdate) && strtotime($today) <= strtotime($subscription_enddate)){
        $button_name = '新課訂閱';
      } else {
        $button_name = '報名課程';
      }
    }
  } else {
    $button_name = '報名課程';
  }
}

$course_sign = '0';

if(strstr($USER->username,'guest')) {

  if(course_enrol($cid))
  {
  // echo "<script> alert('你未登入，將不會紀錄時數。'); </script>";
  $button = '<button onclick="alert(\'你未登入，將不會紀錄時數。\');location.href=\'/elearn/course/view.php?id='.$cid.'\'">直接觀看</button>';

  if(preg_match("/^211.79.136.20[2,3,4,5,6]$/", $_SERVER["REMOTE_ADDR"]) || preg_match("/^163.29.35.[0-9]*[0-9]*[0-9]*$/", $_SERVER["REMOTE_ADDR"])) {
    $button.= '<button onclick="alert(\'點選確定後，將導轉至台北通\'); location.href=\'https://elearning.taipei/autoRedirectCourse.php?id='.$cid.'\'">'.$button_name.'</button>';
  } else {
    $button.= '<button onclick="alert(\'點選確定後，將導轉至台北通\'); location.href=\'https://elearning.taipei/autoRedirectCourse.php?id='.$cid.'\'">報名課程</button>';
  }
  
  // $button.= '&nbsp;<button onclick="location.href=\'/elearn/course/index.php\'">取消</button>';

  // $button = '<a href="/elearn/course/view.php?id='.$cid.'"><input type="button" value="試讀" "></a>
  //             &nbsp;<a href="/elearn/login/index.php"><input type="button" value="登入" "></a>
  //             &nbsp;<a href="/elearn/course/index.php"><input type="button" value="取消""></a>';
  // throw new Exception('不允許訪客存取，請登入');
  }
  else
  {
    if(preg_match("/^211.79.136.20[2,3,4,5,6]$/", $_SERVER["REMOTE_ADDR"]) || preg_match("/^163.29.35.[0-9]*[0-9]*[0-9]*$/", $_SERVER["REMOTE_ADDR"])) {
      $button = '<button onclick="alert(\'點選確定後，將導轉至台北通\'); location.href=\'https://elearning.taipei/autoRedirectCourse.php?id='.$cid.'\'">'.$button_name.'</button>';
    } else {
      $button = '<button onclick="alert(\'點選確定後，將導轉至台北通\'); location.href=\'https://elearning.taipei/autoRedirectCourse.php?id='.$cid.'\'">報名課程</button>';
    }
    // $button.= '&nbsp;<button onclick="location.href=\'/elearn/course/index.php\'">取消</button>';
    $button.= '<h2> 此課程不開放給訪客閱讀</h2>';

     // $button = '<a href="/elearn/login/index.php"><input type="button" value="登入" "></a>
     //          &nbsp;<a href="/elearn/course/index.php"><input type="button" value="取消""></a>
     //          <h2> 此課程不開放給訪客閱讀</h2>
     //          ';
  }
}
else
{
  $coursecontext = \context_course::instance($course->id); //20210204
  //if (!has_capability('moodle/course:view', $coursecontext)) {
  if(!empty(Course_Sign2($cid,$USER->id)))  //2021-07-23 Course_Sign 改為Course_Sign2 由於mdl_role_assignments不刪資料,加入mdl_fet_course_history作為今年篩選
  {
    $course_sign = '1';
    $button = '<button onclick="location.href=\'/elearn/course/view.php?id='.$cid.'\'">進入課程</button>';
    // $button.= '&nbsp;<button onclick="location.href=\'/elearn/course/index.php\'">取消</button>';

    // $button = '<a href="/elearn/course/view.php?id='.$cid.'&outlineDisplay=N"><input type="button" value="進入課程""></a>
    //           // &nbsp;<a href="/elearn/course/index.php"><input type="button" value="取消""></a>';
  }
  else
  {
    if(preg_match("/^211.79.136.20[2,3,4,5,6]$/", $_SERVER["REMOTE_ADDR"]) || preg_match("/^163.29.35.[0-9]*[0-9]*[0-9]*$/", $_SERVER["REMOTE_ADDR"])) {
      $button = '<button onclick="location.href=\'/elearn/course/view.php?id='.$cid.'&act=reg\'">'.$button_name.'</button>';
    } else {
      $button = '<button onclick="location.href=\'/elearn/course/view.php?id='.$cid.'&act=reg\'">報名課程</button>';
    }
    
    // $button.= '&nbsp;<button onclick="location.href=\'/elearn/course/index.php\'">取消</button>';

    // $button = '<a href="/elearn/enrol/index.php?id='.$cid.'&outlineDisplay=N"><input type="button" value="註冊""></a>
    // &nbsp;<input type="button" value="取消"">';

    if (has_capability('moodle/course:view', $coursecontext)) {  
      $course_sign = '1';
      $button .= '<button onclick="location.href=\'/elearn/course/view.php?id='.$cid.'\'">管理員進入課程</button>';
    }
  }
//}else{
//    $course_sign = '1';
//    $button = '<button onclick="location.href=\'/elearn/course/view.php?id='.$cid.'\'">管理員進入課程</button>';
//  }
}
// 限制訪客存取

// $s = $d = $type1 = $type2 = $html_content = $ahref_html = $after_html = "";
// 限制訪客存取
// if($USER->username=='guest'){
// 	throw new Exception('不允許訪客存取，請登入');
// }

/*if(optional_param('sel_type', '', PARAM_TEXT)=="classname"){
	$s = optional_param('sel_str', '', PARAM_TEXT);//班級名稱
	$type1 = "selected";
}
else if(optional_param('sel_type', '', PARAM_TEXT)=="startdate"){
	$d = optional_param('sel_str', '', PARAM_TEXT);//班級名稱
	$type2 = "selected";
}
$p = optional_param('p', 0, PARAM_INT); //page number
$sel_str = optional_param('sel_str', '', PARAM_TEXT);
if($d=="") {
	$d = "0";
}*/
// if(empty($courseinfo))
// {
//   $courseinfo = object;
// }


$fullname         ='';
$course_introduce ='';
$course_target    ='';
$course_outline   ='';
$course_remark    ='';
$course_teacher   ='';
$course_info      ='';
$course_outher    ='';

// 課程名稱
if(!empty($course->fullname))
{
  $fullname = $course->fullname ;
}
// 課程簡介
if(!empty($courseinfo->course_introduce))
{
  $course_introduce = $courseinfo->course_introduce ;
}
// 課程目標
if(!empty($courseinfo->course_target))
{
  $course_target = $courseinfo->course_target ;
}
// 課程大綱
if(!empty($courseinfo->course_outline))
{
  $course_outline = $courseinfo->course_outline ;
}
// 其他注意事項
if(!empty($courseinfo->course_remark))
{
  $course_remark = $courseinfo->course_remark ;
}
// 講師
if(!empty($courseinfo->course_teacher))
{
  $course_teacher = $courseinfo->course_teacher ;
}
// 學習認證資訊
if(!empty($courseinfo->course_info))
{
  $course_info = $courseinfo->course_info ;
}
// 備註
if(!empty($courseinfo->course_outher))
{
  $course_outher = $courseinfo->course_outher ;
}


$tt =1 ;
echo $OUTPUT->header();
//加入搜尋列

echo <<<EOF
<table  cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <td colspan="2" style="height:40px">
      <p><strong>課程名稱:</strong></p>
      <h2>{$fullname}</h2>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>課程簡介：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      <p><strong>{$course_introduce}</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <br />
      <p><strong>終身學習入口網類別[類別代碼]：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      <p>{$ecpa_name}</p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>課程目標：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      <p><strong>{$course_target}</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>課程大綱：</strong></p>
      </td>
    </tr>
    <tr>
      <td style="height:188px">
      <p>&nbsp;</p>
      </td>
      <td style="height:188px">
      {$course_outline}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>其他注意事項：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      {$course_outher}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>講師：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      {$course_teacher}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>學習認證資訊：</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      {$course_info}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:28px">
      <p><strong>備註:</strong></p>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="height:80px">
      {$course_remark}
      </td>
    </tr>
  </tbody>
</table>

{$button}
EOF;




echo $OUTPUT->footer();
