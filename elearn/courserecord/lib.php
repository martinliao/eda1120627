<?php
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
 * Library of useful functions
 *
 * @package core_course
 */

defined('MOODLE_INTERNAL') || die;

//require_once('../functions.php');
require_once(dirname(__FILE__) . '/../functions.php');

// require_once($CFG->libdir.'/completionlib.php');
// require_once($CFG->libdir.'/filelib.php');
// require_once($CFG->dirroot.'/course/format/lib.php');

/**
 * Get course records count
 */
function update_study_time($uid){
	global $USER, $DB;

	$sql = sprintf("SELECT
						a.userid AS uid,
						s.course AS cid,
						a.value AS value
					FROM
						mdl_scorm_scoes_track a
					JOIN mdl_scorm_scoes b ON a.scoid = b.id
					JOIN mdl_scorm s ON s.id = a.scormid
					WHERE
						a.element = 'cmi.core.total_time' and a.userid = %s order by s.course
					",$uid);

	$data = $DB->get_records_sql_ng($sql);

	$new_data = array();
	$j = -1;

	for($i=0;$i<count($data);$i++){
	    if($data[$i]->cid == $data[$i-1]->cid){
	    	$cnt = explode('.',$data[$i]->value);
	    	$cnt_2 = explode(':',$cnt[0]);
	    	$total_time = (3600 * (int)$cnt_2[0]) + (60 * (int)$cnt_2[1]) + (int)$cnt_2[2];

	    	$new_data[$j]['time'] = $new_data[$j]['time'] + $total_time;    
	    } else {
	    	$j++; 
	    	$cnt = explode('.',$data[$i]->value);
	    	$cnt_2 = explode(':',$cnt[0]);
	    	$total_time = (3600 * (int)$cnt_2[0]) + (60 * (int)$cnt_2[1]) + (int)$cnt_2[2];
	    	
	    	$new_data[$j]['time'] = $total_time;
	    	$new_data[$j]['cid'] = $data[$i]->cid; 
	    	$new_data[$j]['uid'] = $data[$i]->uid;
	    }
	}

	for($i=0;$i<count($new_data);$i++){
		$sql = sprintf("select timetotalstudy from mdl_fet_course_history where userid = %s and courseid = %s",$new_data[$i]['uid'],$new_data[$i]['cid']);
		$info = $DB->get_records_sql_ng($sql);
		if($new_data[$i]['time'] > 26715) {$new_data[$i]['time'] = 26715; }  //避免scorm_scoes_track value值過大影響timetotalstudy計算, 2021-03-29, chris
		if($new_data[$i]['time'] > $info[0]->timetotalstudy && is_numeric($new_data[$i]['uid']) && is_numeric($new_data[$i]['cid'])){
			$sql = sprintf("update mdl_fet_course_history set timetotalstudy = %s where userid = %s and courseid = %s",$new_data[$i]['time'],$new_data[$i]['uid'],$new_data[$i]['cid']);
			$DB->execute($sql);
		}
	}

	return true;
}

function get_courserecord_count($type=0){
	global $USER, $DB;

	if($type==1) {
		$type = " and a.timecomplete > 0 ";
	}
	elseif($type==2) {
		$type = " and a.timecomplete is null ";
	}
	else {
		$type = "";
	}

	// 顯示總數，若都沒有則顯示0
	$sql = sprintf('SELECT count(1) as cnt FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id WHERE userid = %d AND b.format=\'topics\' '.$type.' and timetotalstudy > 0 ORDER BY a.id ASC ', $USER->id);

	if($records = $DB->get_records_sql($sql)){

		foreach ($records as $record) {
			$total = $record->cnt;
		}
		return $total ;
	}
	else
	{
		return '0';
	}

	// return $DB->count_records('fet_course_history', array('userid'=>$USER->id));
}

/**
* 取得使用者資料 (使用功能: 列印證明)
* @param int $userid 使用者ID
* @return data Object; username,lastname,firstname,idno
* @return username 使用者帳號, lastname,firstname 姓名 ,idno 身分證編號
*/

function get_user_info($userid)
{
	global $DB;

	$sql = "select
		u.username,
		u.lastname,
		u.firstname,
		pid.idno, 
        c.ecpa, c.edu, IF(u.email = '',t.email,u.email) as email
		FROM {user} u
		JOIN {fet_pid} pid ON u.id = pid.uid
        JOIN {fet_cert_setting} c ON c.uid = u.id
		LEFT JOIN {temporary_user} t ON t.idno = pid.idno
		where u.id = :id";
	$data = $DB->get_record_sql($sql, array("id"=>$userid));

	return $data;

}


/**
*	取得多門課程 認證時數(依據課程設定檔)、課程資料 (使用功能: 列印證明)
*	@param string courseids 課程ID(複數)
*	@param int userid 使用者ID
*	@return $data Object;
*	fullname,timecreate,timecomplete,certhour,quizgrade,timetotalstudy,gothours,uploadstatus
*/
function get_history_info($userid,$courseids)
{
	global $DB;
	$sql = "SELECT
			fullname,timecreate,timecomplete,c.certhour,quizgrade,timetotalstudy,
			gothours,uploadstatus
		FROM
			mdl_fet_course_history a
		JOIN mdl_course b ON a.courseid = b.id
		join mdl_fet_course_data c on c.courseid = b.id
		WHERE a.userid = ".$userid." and b.id in (".$courseids.")";
	$data = $DB->get_records_sql_ng($sql);
	return $data;
}

/**
 * 取得列印證明的資料
 * @param int $userId 使用者ID
 * @param int $courseids 課程ID(複數)
 * @return dataSet array;
 */
function getProofDatas($userid, $courseids)
{
	$dataSet = array();

	$datas = get_history_info($userid,$courseids); 	// 取得 學習紀錄、課程資訊。

	if(!empty($datas))
	{
		$i = 0;
		foreach ($datas as $data )
		{
			$i++;
			$dataSet[$i]['coursename'] = $data->fullname; //課程名稱
			$timecreate = explode(' ',$data->timecreate);
			$dataSet[$i]['signTime'] = $timecreate[0]; //課程註冊時間
			$dataSet[$i]['timecomplete'] = $data->timecomplete; //課程完成時間
			$dataSet[$i]['certhour'] = $data->certhour; //取得時數
		}
	}
	return $dataSet;
}

//轉移後備份資料庫
function get_history_infoYear($userid, $courseids, $year)
{
    global $DB;
    $year = intval($year)+1911;
    $sql = "SELECT
            fullname,timecreate,timecomplete,c.certhour,quizgrade,timetotalstudy,
            gothours,uploadstatus
        FROM
            mdl_fet_course_history_$year a
        JOIN mdl_course_$year b ON a.courseid = b.id
        join mdl_fet_course_data_$year c on c.courseid = b.id
        WHERE a.userid = ".$userid." and b.id in (".$courseids.")";
    $data = $DB->get_records_sql_ng($sql);
    return $data;
}

/**
 * 取得列印證明的資料 轉移後備份資料庫
 * @param int $userId 使用者ID
 * @param int $courseids 課程ID(複數)
 * @return dataSet array;
 */
function getProofDatasYear($userid, $courseids, $year)
{
    $dataSet = array();

    $datas = get_history_infoYear($userid, $courseids, $year);  // 取得 學習紀錄、課程資訊。

    if(!empty($datas))
    {
        $i = 0;
        foreach ($datas as $data )
        {
            $i++;
            $dataSet[$i]['coursename'] = $data->fullname; //課程名稱
            $timecreate = explode(' ',$data->timecreate);
            $dataSet[$i]['signTime'] = $timecreate[0]; //課程註冊時間
            $dataSet[$i]['timecomplete'] = $data->timecomplete; //課程完成時間
            $dataSet[$i]['certhour'] = $data->certhour; //取得時數
        }
    }
    return $dataSet;
}

/**
 * get curse record table
 *
 * @param $offset, int of query offset
 * @param $limit, int of query limit
 */
function get_courserecord($offset, $type, $limit, $rUpdate=0){
	global $USER, $DB;

	// require('../functions.php'); //modified by Sam

	if($type==1) {
		$type = " and a.timecomplete > 0 ";
	}
	elseif($type==2) {
		$type = " and a.timecomplete is null ";
	}
	else {
		$type = "";
	}
	
	// if(1==$rUpdate) {
		//===先更新一輪此頁面狀態
		$sql = sprintf('SELECT a.courseid
						FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
						JOIN (SELECT DISTINCT b.instanceid FROM {context} b
							LEFT JOIN {role_assignments} c ON c.contextid =b.id
							WHERE c.userid=%d AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
						LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
						LEFT JOIN {fet_upload_hours} mh ON a.userid = mh.uid AND a.courseid = mh.cid
						LEFT OUTER JOIN {fet_course_data} tmp ON a.courseid = tmp.courseid
						LEFT OUTER JOIN view_total_artifical fah ON a.courseid = fah.courseid AND a.userid = fah.userid
						WHERE a.userid = %d '.$type.'AND b.format=\'topics\'  ORDER BY UNIX_TIMESTAMP(a.timecreate) DESC LIMIT %d OFFSET %d', $USER->id, $USER->id, $limit, $offset);
		$records = $DB->get_records_sql_ng($sql);
		foreach ($records as $record) {
			updateQuizGrade($record->courseid, $USER->id);//更新history.quizgrade
			updateHistoryComplete($USER->id, $record->courseid);
		}
		//===先更新一輪此頁面狀態 END
	// }

	//20160407 增加註冊過的課程才顯示 by Hao
	$sql = sprintf('SELECT a.id, a.courseid, IFNULL(a.certhour,tmp.certhour) certhour, gothours, quizgrade, ifnull(timetotalstudy, 0) + ifnull(mobitime, 0) + ifnull(fah.ts, 0) timetotalstudy
					, timecomplete, uploadstatus, b.fullname, IF(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) >= b.startdate AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP)<b.enddate+60*60*24, TRUE, FALSE) coursebegin,c.ecpa,c.edu,c.name AS env
					,mh.ecpa AS ecpa_, mh.hcert1, mh.hcert2,c.id AS hours_set ,a.timecreate
					FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
					JOIN (SELECT DISTINCT b.instanceid FROM {context} b
						LEFT JOIN {role_assignments} c ON c.contextid =b.id
						WHERE c.userid=%d AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
					LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
					LEFT JOIN {fet_upload_hours} mh ON a.userid = mh.uid AND a.courseid = mh.cid
					LEFT OUTER JOIN {fet_course_data} tmp ON a.courseid = tmp.courseid
					LEFT OUTER JOIN view_total_artifical fah ON a.courseid = fah.courseid AND a.userid = fah.userid
					WHERE a.userid = %d '.$type.'AND b.format=\'topics\'  ORDER BY UNIX_TIMESTAMP(a.timecreate) DESC LIMIT %d OFFSET %d', $USER->id, $USER->id, $limit, $offset);

	$quiz_sql = sprintf("SELECT a.course,b.timefinish
					FROM mdl_quiz a JOIN mdl_quiz_attempts b ON a.id = b.quiz
					WHERE b.userid = %s and a.id not in (
														SELECT
															quizid
														FROM
															mdl_fet_pre_quiz
														WHERE
															pre_quiz = '1')",$USER->id);
	$quizs = $DB->get_records_sql_ng($quiz_sql);
	$quiz_ary = array();

	foreach ($quizs as $quiz)
	{
		$quiz_ary[$quiz->course] = $quiz->timefinish;
	}

	$content = '';

	if($records = $DB->get_records_sql_ng($sql)){
		$odd=0;

		foreach ($records as $record) {
			$content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

			$content .= html_writer::start_tag('td', array('class'=>'cell c0'));
			$content .= html_writer::link(new moodle_url('/course/view.php', array('id'=>$record->courseid)), $record->fullname);
			$content .= html_writer::end_tag('td');

			$content .= html_writer::start_tag('td', array('class'=>'cell c0 simplify'));
			$content .= "<input type='button' onclick=\"location.href = 'http://elearning.taipei/elearn/courseinfo/index.php/?courseid=".$record->courseid."';\" value='課程介紹'>";
			$content .= html_writer::end_tag('td');

			$content .= html_writer::start_tag('td', array('class'=>'cell c1', 'style'=>'text-align:center;'));
			$content .= $record->coursebegin?'開課中':'未開放';
			$content .= html_writer::end_tag('td');


			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$timecreate = explode(' ',$record->timecreate);
			$content .= $record->timecreate?$timecreate['0']:'-';
			$content .= html_writer::end_tag('td');


			// 修課時間
			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$content .= "<a href=# onclick='openNode($USER->id, $record->courseid)'>".getCountingTimeDisplay(isset($record->timetotalstudy)?$record->timetotalstudy:0)."</a>";
			$content .= html_writer::end_tag('td');

			// 認證時數
			$content .= html_writer::start_tag('td', array('class'=>'cell c3', 'style'=>'text-align:center;'));
			$content .= isset($record->certhour)?$record->certhour:0;
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(ECPA)
			$content .= html_writer::start_tag('td', array('class'=>'cell c4 simplify', 'style'=>'text-align:center;'));
			// $content .= '-';
			// isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';

			$content .= (!empty($record->gothours))?'已上傳':'-';
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(環教)
			//if(!empty($record->env))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c5 simplify', 'style'=>'text-align:center;'));

				$content .= ($record->hcert1>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}
			// 已上傳認證時數(全教)
			//if(!empty($record->edu))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c6 simplify', 'style'=>'text-align:center;'));
				$content .= ($record->hcert2>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}

			// 上傳狀態
			// $content .= html_writer::start_tag('td', array('class'=>'cell c5', 'style'=>'text-align:center;'));
			// $content .= isset($record->uploadstatus)?'已上傳':'未上傳';
			// $content .= html_writer::end_tag('td');

			// 測驗成績
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$sql = "SELECT id FROM {course_modules} WHERE course=:course  AND visible=1 AND module = (SELECT v.id FROM {modules} v WHERE v.name='quiz') AND instance not in (
						SELECT
							quizid
						FROM
							mdl_fet_pre_quiz
						WHERE
							pre_quiz = '1')";
			$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
			$QuId = 0;
			if($data) {
				if($data->id) {
					$QuId = $data->id;
				}
			}
			if($QuId>0) {
				$content .= "<a href='/elearn/mod/quiz/view.php?id=$QuId'>".($record->quizgrade>0?$record->quizgrade:"未完成")."</a>";
			}
			else {
				$content .= "-";
			}
			$content .= html_writer::end_tag('td');
			// 完成測驗日期
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$content .= !empty($quiz_ary[$record->courseid])?date('Y-m-d',$quiz_ary[$record->courseid]):'-';
			$content .= html_writer::end_tag('td');
			// 問卷完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c8', 'style'=>'text-align:center;'));
			$fbHref = "-";
			if(checkfeedback($record->courseid)) {
				$sql = "SELECT id FROM {course_modules} WHERE course=:course and module = (SELECT v.id FROM mdl_modules v WHERE v.name='feedback')";
				$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
				$fbId = 0;
				if($data) {
					if($data->id) {
						$fbId = $data->id;
					}
				}
				if($fbId>0) {
					$fbHref = "<a href='/elearn/mod/feedback/view.php?id=$fbId'>未完成</a>";
				}
                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'未完成';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
            else {
            	
        		$sql = "SELECT id FROM {course_modules} WHERE course=:course and visible = '1' and module = (SELECT v.id FROM mdl_modules v WHERE v.name='feedback')";
				$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
				$fbId = 0;
				if($data) {
					if($data->id) {
						$fbId = $data->id;
					}
				}
				if($fbId>0) {
					$fbHref = "<a href='/elearn/mod/feedback/view.php?id=$fbId'>立即前往</a>";
				}
  				

                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'-';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
			$content .= html_writer::end_tag('td');

			// 課程完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c9', 'style'=>'text-align:center;'));
			$content .=  isset($record->timecomplete)?'已完成':'未完成';
			$content .= html_writer::end_tag('td');

			// 列印證書
			$content .= html_writer::start_tag('td', array('class'=>'cell c10 lastcol', 'style'=>'text-align:center;'));
			// $content .= html_writer::select(array(1 => '一般民眾', 2 => '公務人員'), '請選擇', 0);

			// if(!empty($record->hours_set))
			// {
			if($record->timecomplete > 0)
			{
			    $content .= "<input style = 'width:30px; height:20px' type='checkbox' class ='courseid' value=$record->courseid >";
			}
			// }
			// else
			// {
			// 	if($record->uploadstatus > 0)
			// 	{
			// 		$content .= "<input type='button' onclick='printpdf($record->courseid,1)' value='列印證明'>";
			// 	}
			// }
			$content .= html_writer::end_tag('td');
			$content .= html_writer::end_tag('tr');

			$odd = $odd?0:1;
		}
	}
	$edu = $env ='';
	//if(!empty($record->env))
	{
		$env = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c5 simplify" scope="col">已上傳<br>認證時數<br>(環境教育)</th>';
	}
	//if(!empty($record->edu))
	{
		$edu = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c6 simplify" scope="col">已上傳<br>認證時數<br>(全國教師)</th>';
	}
return <<<EOF
<div style = "text-align:right; width:96.5%">
</div>
<table width="100%" cellpadding="5" cellspacing="1" class="generaltable">
<thead style="background-color:#D3E4ED">
	<tr>
		<th style="vertical-align:top; white-space:nowrap; center; width: 25%;" class="header c0" scope="col">課程名稱</th>
		<th style="vertical-align:top; white-space:nowrap; center; width: 10%;text-align: center;" class="header c0 simplify" scope="col">課程介紹</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c1" scope="col">開課<br>狀態</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">報名<br>日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">修課<br>時間</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c3" scope="col">認證<br>時數</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c4 simplify" scope="col">已上傳<br>認證時數<br>(終身學習)</th>
		{$env}
		{$edu}
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">測驗<br>成績</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">完成<br>測驗日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">問卷</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c9" scope="col">課程<br>完成與否</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c10 lastcol" scope="col">列印證明<br>全選
				<input type='checkbox' id='CheckAll' style="width:30px; height:20px; margin-right: 0px;">
		</th>
	</tr>
</thead>
<tbody>
	$content
</tbody>
</table>
<script>
$(document).ready(function(){
  	$("#CheckAll").click(function(){
   		if($("#CheckAll").prop("checked")){
    		$("input[class='courseid']").prop("checked",true);
   		}else{
    		$("input[class='courseid']").prop("checked",false);
   			}
  	})
})
function printpdf(id,status)
{
	var str = '';
	var cnt = 0;
	if($("#idnoshow").prop("checked")){
	var idnoshow = 1;
	}else{
	var idnoshow = 0;
	}
	$(".courseid:checked").each(function() {
		str = str+this.value+',';
		cnt++;
	});
	var cid = str.substr(0,(str.length-1));
	if(cnt > 1)
	{
	   	var URL = 'output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+0+'&yy='+(new Date().getFullYear()-1911);
		window.open(URL, '', config='height=600,width=480');
	}
	else if(cnt == 1)
	{
		var URL = 'one_output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+status+'&yy='+(new Date().getFullYear()-1911);
		window.open(URL, '', config='height=600,width=480');
	}
	else
	{
		alert('請勾選課程');
	}
}
</script>
EOF;
// 	return <<<EOF
// <table width="100%" cellpadding="5" cellspacing="1" class="generaltable boxaligncenter">
// <thead style="background-color:#6FF08E">
// 	<tr>
// 		<th style="vertical-align:top;;white-space:nowrap;" class="header c0" scope="col">課程名稱</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c1" scope="col">開課狀態</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c2" scope="col">修課時間</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c3" scope="col">認證時數</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c4" scope="col">已上傳認證時數</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c5" scope="col">上傳狀態</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c6" scope="col">測驗成績</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c7" scope="col">問卷</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c8" scope="col">課程完成與否</th>
// 		<th style="vertical-align:top; text-align:center;white-space:nowrap;" class="header c9 lastcol" scope="col">列印證書</th>
// 	</tr>
// </thead>
// <tbody>
// 	$content
// </tbody></table>
// EOF;
}

//課程蒐尋獨立function
function get_courserecord1($s,$type,$offset, $limit, $rUpdate=0){
	global $USER, $DB;

	$offset = is_numeric($offset)?$offset:'1';
	$limit = is_numeric($limit)?$limit:'10';

	
	if($type==1) {
		$type = " and a.timecomplete > 0 ";
	}
	elseif($type==2) {
		$type = " and a.timecomplete is null ";
	}
	else {
		$type = "";
	}

	$sql = "SELECT a.courseid
		FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
		JOIN (SELECT DISTINCT b.instanceid from {context} b
			LEFT JOIN {role_assignments} c ON c.contextid =b.id
			WHERE c.userid=$USER->id AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
		LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
		LEFT JOIN {fet_upload_hours} mh on a.userid = mh.uid and a.courseid = mh.cid
		LEFT OUTER JOIN {fet_course_data} tmp on a.courseid = tmp.courseid
		LEFT OUTER JOIN view_total_artifical fah on a.courseid = fah.courseid and a.userid = fah.userid
		WHERE a.userid = $USER->id $type AND b.format='topics' and b.fullname like '%$s%' ORDER BY a.id ASC ";
	

	// if(1==$rUpdate) {
		//===先更新一輪此頁面狀態
		// $sql = "SELECT a.courseid
		// 	FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
		// 	JOIN (SELECT b.instanceid from {context} b
		// 		LEFT JOIN {role_assignments} c ON c.contextid =b.id
		// 		WHERE c.userid=$USER->id AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
		// 	LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
		// 	LEFT JOIN {fet_upload_hours} mh on a.userid = mh.uid and a.courseid = mh.cid
		// 	LEFT OUTER JOIN {fet_course_data} tmp on a.courseid = tmp.courseid
		// 	LEFT OUTER JOIN view_total_artifical fah on a.courseid = fah.courseid and a.userid = fah.userid
		// 	WHERE a.userid = $USER->id AND b.format='topics' and b.fullname like '%$s%' ORDER BY a.id ASC ";

		$records = $DB->get_records_sql_ng($sql);
		foreach ($records as $record) {
			updateQuizGrade($record->courseid, $USER->id);//更新history.quizgrade
			updateHistoryComplete($USER->id, $record->courseid);
		}
		//===先更新一輪此頁面狀態 END
	// }
		
	$sql = "SELECT a.id, a.courseid, IFNULL(a.certhour,tmp.certhour) certhour, gothours, quizgrade, ifnull(timetotalstudy, 0) + ifnull(mobitime, 0) + ifnull(fah.ts, 0) timetotalstudy
		, timecomplete, uploadstatus, b.fullname, IF(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) >= b.startdate AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP)<b.enddate+60*60*24, TRUE, FALSE) coursebegin,c.ecpa,c.edu,c.name as env
		,mh.ecpa as ecpa_, mh.hcert1, mh.hcert2,c.id as hours_set, a.timecreate
		FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
		JOIN (SELECT DISTINCT b.instanceid from {context} b
			LEFT JOIN {role_assignments} c ON c.contextid =b.id
			WHERE c.userid=$USER->id AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
		LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
		LEFT JOIN {fet_upload_hours} mh on a.userid = mh.uid and a.courseid = mh.cid
		LEFT OUTER JOIN {fet_course_data} tmp on a.courseid = tmp.courseid
		LEFT OUTER JOIN view_total_artifical fah on a.courseid = fah.courseid and a.userid = fah.userid
		WHERE a.userid = $USER->id $type AND b.format='topics' and b.fullname like '%$s%' ORDER BY a.id ASC LIMIT ".$limit." OFFSET ".$offset;
	
	$quiz_sql = sprintf("SELECT a.course,b.timefinish
			FROM mdl_quiz a JOIN mdl_quiz_attempts b ON a.id = b.quiz
			WHERE b.userid = %s and a.id not in (
												SELECT
													quizid
												FROM
													mdl_fet_pre_quiz
												WHERE
													pre_quiz = '1')",$USER->id);
	$quizs = $DB->get_records_sql_ng($quiz_sql);
	$quiz_ary = array();

	foreach ($quizs as $quiz)
	{
		$quiz_ary[$quiz->course] = $quiz->timefinish;
	}


	$content = '';
	if($records = $DB->get_records_sql_ng($sql)){
		$odd=0;

		foreach ($records as $record) {

			$content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

			$content .= html_writer::start_tag('td', array('class'=>'cell c0'));
			$content .= html_writer::link(new moodle_url('/course/view.php', array('id'=>$record->courseid)), $record->fullname);
			$content .= html_writer::end_tag('td');

			$content .= html_writer::start_tag('td', array('class'=>'cell c0 simplify'));
			$content .= "<input type='button'"
				.($record->coursebegin?"onclick=\"location.href = 'http://elearning.taipei/elearn/courseinfo/index.php/?courseid=".$record->courseid."';\"":"").
				 "value='課程介紹'>";
			$content .= html_writer::end_tag('td');

			$content .= html_writer::start_tag('td', array('class'=>'cell c1', 'style'=>'text-align:center;'));
			$content .= $record->coursebegin?'開課中':'未開放';
			$content .= html_writer::end_tag('td');

			// 報名日期
			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$timecreate = explode(' ',$record->timecreate);
			$content .= $record->timecreate?$timecreate['0']:'-';
			$content .= html_writer::end_tag('td');

			// 修課時間
			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$content .= "<a href=# onclick='openNode($USER->id, $record->courseid)'>".getCountingTimeDisplay(isset($record->timetotalstudy)?$record->timetotalstudy:0)."</a>";
			$content .= html_writer::end_tag('td');

			// 認證時數
			$content .= html_writer::start_tag('td', array('class'=>'cell c3', 'style'=>'text-align:center;'));
			$content .= isset($record->certhour)?$record->certhour:0;
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(ECPA)
			$content .= html_writer::start_tag('td', array('class'=>'cell c4 simplify', 'style'=>'text-align:center;'));
			// $content .= '-';
			// isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';

			$content .= (!empty($record->gothours))?'已上傳':'-';
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(環教)
			//if(!empty($record->env))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c5 simplify', 'style'=>'text-align:center;'));

				$content .= ($record->hcert1>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}
			// 已上傳認證時數(全教)
			//if(!empty($record->edu))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c6 simplify', 'style'=>'text-align:center;'));
				$content .= ($record->hcert2>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}

			// 測驗成績
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$sql = "SELECT id FROM {course_modules} WHERE course=:course AND module = (SELECT v.id FROM {modules} v WHERE v.name='quiz') AND instance not in (
						SELECT
							quizid
						FROM
							mdl_fet_pre_quiz
						WHERE
							pre_quiz = '1')";
			$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
			$QuId = 0;
			if($data) {
				if($data->id) {
					$QuId = $data->id;
				}
			}
			if($QuId>0) {
				$content .= "<a href='/elearn/mod/quiz/view.php?id=$QuId'>".($record->quizgrade>0?$record->quizgrade:"未完成")."</a>";
			}
			else {
				$content .= "-";
			}
			$content .= html_writer::end_tag('td');

			// 完成測驗日期
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$content .= !empty($quiz_ary[$record->courseid])?date('Y-m-d',$quiz_ary[$record->courseid]):'-';
			$content .= html_writer::end_tag('td');


			// 問卷完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c8', 'style'=>'text-align:center;'));
			//$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'未完成';
			$fbHref = "-";
			if(checkfeedback($record->courseid)) {
				$sql = "SELECT id FROM {course_modules} WHERE course=:course and module = (SELECT v.id FROM mdl_modules v WHERE v.name='feedback')";
				$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
				$fbId = 0;
				if($data) {

					if($data->id) {
						$fbId = $data->id;
					}
				}
				if($fbId>0) {
					$fbHref = "<a href='/elearn/mod/feedback/view.php?id=$fbId'>未完成</a>";
				}
                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'未完成';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
            else {
                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'-';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
			$content .= html_writer::end_tag('td');

			// 課程完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c9', 'style'=>'text-align:center;'));
			$content .=  isset($record->timecomplete)?'已完成':'未完成';
			$content .= html_writer::end_tag('td');

			// 列印證書
			$content .= html_writer::start_tag('td', array('class'=>'cell c10 lastcol', 'style'=>'text-align:center;'));
			// $content .= html_writer::select(array(1 => '一般民眾', 2 => '公務人員'), '請選擇', 0);

			// if(!empty($record->hours_set))
			// {
				if($record->timecomplete > 0)
				{
					// $content .= "<input type='button' onclick='printpdf($record->courseid,0)' value='列印證明'>";
					$content .= "<input style = 'width:30px; height:20px' type='checkbox' class ='courseid' value=$record->courseid >";
				}
			// }
			// else
			// {
			// 	if($record->uploadstatus > 0)
			// 	{
			// 		$content .= "<input type='button' onclick='printpdf($record->courseid,1)' value='列印證明'>";
			// 	}
			// }
			$content .= html_writer::end_tag('td');
			$content .= html_writer::end_tag('tr');

			$odd = $odd?0:1;
		}
	}
	$edu = $env ='';
	//if(!empty($record->env))
	{
		$env = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c5 simplify" scope="col">已上傳<br>認證時數<br>(環境教育)</th>';
	}
	//if(!empty($record->edu))
	{
		$edu = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c6 simplify" scope="col">已上傳<br>認證時數<br>(全國教師)</th>';
	}
return <<<EOF
<table width="100%" cellpadding="5" cellspacing="1" class="generaltable">
<thead style="background-color:#D3E4ED">
	<tr>
		<th style="vertical-align:top; white-space:nowrap; center; width: 120px;" class="header c0" scope="col">課程名稱</th>
		<th style="vertical-align:top; white-space:nowrap; center; width: 10%;text-align: center;" class="header c0 simplify" scope="col">課程介紹</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c1" scope="col">開課狀態</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">報名<br>日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">修課時間</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c3" scope="col">認證時數</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c4 simplify" scope="col">已上傳<br>認證時數<br>(終身學習)</th>
		{$env}
		{$edu}
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">測驗成績</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">完成<br>測驗日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">問卷</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c9" scope="col">課程完成與否</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c10 lastcol" scope="col">列印證明<br>全選
				<input type='checkbox' id='CheckAll' style="width:30px; height:20px; margin-right: 0px;">
		</th>
	</tr>
</thead>
<tbody>
	$content
</tbody>
</table>
<script>
$(document).ready(function(){
  	$("#CheckAll").click(function(){
   		if($("#CheckAll").prop("checked")){
    		$("input[class='courseid']").prop("checked",true);
   		}else{
    		$("input[class='courseid']").prop("checked",false);
   			}
  	})
})
function printpdf(id,status)
{
	var str = '';
	var cnt = 0;
	if($("#idnoshow").prop("checked")){
	var idnoshow = 1;
	}else{
	var idnoshow = 0;
	}
	$(".courseid:checked").each(function() {
		str = str+this.value+',';
		cnt++;
	});
	var cid = str.substr(0,(str.length-1));
	if(cnt > 1)
	{
	   	var URL = 'output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+0+'&yy='+(new Date().getFullYear()-1911);
		window.open(URL, '', config='height=600,width=480');
	}
	else if(cnt == 1)
	{
		var URL = 'one_output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+status+'&yy='+(new Date().getFullYear()-1911);
		window.open(URL, '', config='height=600,width=480');
	}
	else
	{
		alert('請勾選課程');
	}
}
</script>
EOF;
}

function get_courserecordYear($offset,$limit,$year,$type, $queryData = array()){
    global $USER, $DB;

	$params = array();

    if($type==1) {
		$type = " and a.timecomplete > 0 ";
	}
	elseif($type==2) {
		$type = " and a.timecomplete is null ";
	}
	else {
		$type = "";
	}

	$classname = "";
	if (isset($queryData['course_name'])){
		$classname = " AND b.fullname LIKE :course_name";
		$params['course_name'] = '%'.$queryData['course_name'].'%';
	}


	$year = intval($year)+1911;

	$orderby = [];

	if (in_array($queryData['sort']['sorttype'], ['asc', 'desc'])){
		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'classname'){
			$orderby[] = "b.fullname ".$queryData['sort']['sorttype'];
		}	

		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'signDate'){
			$orderby[] = "UNIX_TIMESTAMP(a.timecreate) ".$queryData['sort']['sorttype'];
		}	
		
		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'classDate'){ 
			$orderby[] = "ifnull(timetotalstudy, 0) + ifnull(mobitime, 0) + ifnull(d.ts, 0) ".$queryData['sort']['sorttype'];
		}	
		
		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'hour'){ 
			$orderby[] = "IFNULL(a.certhour, c.certhour) ".$queryData['sort']['sorttype'];
		}	

		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'classGrade'){ 
			$orderby[] = "quizgrade ".$queryData['sort']['sorttype'];
		}

		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'complete'){ 

		}	
		
		if (!empty($queryData['sort']['sortField']) && $queryData['sort']['sortField'] == 'question'){ 
			$orderby[] = "feedcomplete.timecompleted ".$queryData['sort']['sorttype'];
		}	
	}
	
	
	if (count($orderby) == 0){
		$orderby[] = "UNIX_TIMESTAMP(a.timecreate) DESC";
	}
	
	if (count($orderby) > 0){
		$orderby = "ORDER BY ".implode(", ", $orderby);
	}else{
		$orderby = "";
	}

	$sql = sprintf("SELECT a.id,
							a.courseid,
							IFNULL(a.certhour, c.certhour) certhour,
							gothours,
							quizgrade,
							ifnull(timetotalstudy, 0) + ifnull(mobitime, 0) + ifnull(d.ts, 0) timetotalstudy,
							timecomplete,
							uploadstatus,
							b.fullname,
							0 coursebegin,
							d.ecpa,
							d.edu,
							d. NAME AS env,
							f.ecpa AS ecpa_,
							f.hcert1,
							f.hcert2,
							d.id AS hours_set,
							a.timecreate,
							feedcomplete.timecompleted
						FROM mdl_fet_course_history_$year a
						JOIN mdl_course_$year b
						ON a.courseid=b.id
						JOIN mdl_fet_course_data_$year c
						ON a.courseid=c.courseid
						LEFT OUTER JOIN (
							SELECT
							`mdl_fet_artificial_hour_$year`.`userid` AS `userid`,
							`mdl_fet_artificial_hour_$year`.`courseid` AS `courseid`,
							sum(
								`mdl_fet_artificial_hour_$year`.`ts`
							) AS `ts`
						FROM
							`mdl_fet_artificial_hour_$year`
						GROUP BY
							`mdl_fet_artificial_hour_$year`.`userid`,
							`mdl_fet_artificial_hour_$year`.`courseid`
						)d
						ON a.courseid=d.courseid
						AND a.userid=d.userid
						LEFT JOIN mdl_fet_cert_setting d ON a.userid = d.uid
						LEFT JOIN mdl_fet_upload_hours_$year f ON a.userid = f.uid AND a.courseid=f.cid
						LEFT JOIN (
							SELECT mf.ID, fch.courseid, fch.userid, COUNT(mf.ID) - SUM(CASE WHEN fc.TIMEMODIFIED IS NOT NULL THEN 1 ELSE 0 END) timecompleted
							FROM mdl_fet_course_history_$year fch 
							JOIN mdl_feedback mf ON mf.course = fch.courseid 
							JOIN mdl_fet_course_data ON mdl_fet_course_data.courseid = fch.courseid 
							LEFT JOIN mdl_feedback_completed fc  ON fc.feedback = mf.id
							WHERE fch.userid=%s AND mdl_fet_course_data.checkfeedback = 1 AND mf.id IN (
								SELECT mcm.INSTANCE
								FROM mdl_course_modules mcm 
								WHERE mcm.MODULE = (SELECT a.ID FROM mdl_modules a WHERE a.NAME = 'feedback' )
								AND mcm.VISIBLE = 1
							) GROUP BY fch.courseid, fch.userid						
						) feedcomplete ON feedcomplete.userid = a.userid AND feedcomplete.courseid = a.courseid
						WHERE a.userid=%s %s $classname $orderby LIMIT %d OFFSET %d", $USER->id, $USER->id,$type,$limit,$offset);

    /*$quiz_sql = sprintf("SELECT a.course,b.timefinish
            FROM mdl_quiz a JOIN mdl_quiz_attempts b ON a.id = b.quiz
            WHERE b.userid = %s",$USER->id);
    $quizs = $DB->get_records_sql_ng($quiz_sql);
    $quiz_ary = array();
    foreach ($quizs as $quiz) {
        $quiz_ary[$quiz->course] = $quiz->timefinish;
    }*/
    $content = '';
    if($records = $DB->get_records_sql($sql, $params)) {
        $odd=0;

        foreach ($records as $record) {

            $content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

            $content .= html_writer::start_tag('td', array('class'=>'cell c0'));
            $content .= $record->fullname;
            $content .= html_writer::end_tag('td');

            /*$content .= html_writer::start_tag('td', array('class'=>'cell c1', 'style'=>'text-align:center;'));
            $content .= "-";
            $content .= html_writer::end_tag('td');
            $content .= html_writer::start_tag('td', array('class'=>'cell c1', 'style'=>'text-align:center;'));
            $content .= $record->coursebegin?'開課中':'未開放';
            $content .= html_writer::end_tag('td');*/

            // 報名日期
            $content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
            $timecreate = explode(' ',$record->timecreate);
            $content .= $record->timecreate?$timecreate['0']:'-';
            $content .= html_writer::end_tag('td');

            // 修課時間
            $content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
            $content .= getCountingTimeDisplay(isset($record->timetotalstudy)?$record->timetotalstudy:0);
            $content .= html_writer::end_tag('td');

            // 認證時數
            $content .= html_writer::start_tag('td', array('class'=>'cell c3', 'style'=>'text-align:center;'));
            $content .= isset($record->certhour)?$record->certhour:0;
            $content .= html_writer::end_tag('td');

            // 已上傳認證時數(ECPA)
            $content .= html_writer::start_tag('td', array('class'=>'cell c4 simplify', 'style'=>'text-align:center;'));
            // $content .= '-';
            // isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';

            $content .= (!empty($record->gothours))?'已上傳':'-';
            $content .= html_writer::end_tag('td');

            // 已上傳認證時數(環教)
            //if(!empty($record->env))
            {
                $content .= html_writer::start_tag('td', array('class'=>'cell c5 simplify', 'style'=>'text-align:center;'));

                $content .= ($record->hcert1>0)?'已上傳':'-';
                $content .= html_writer::end_tag('td');
            }
            // 已上傳認證時數(全教)
            //if(!empty($record->edu))
            {
                $content .= html_writer::start_tag('td', array('class'=>'cell c6 simplify', 'style'=>'text-align:center;'));
                $content .= ($record->hcert2>0)?'已上傳':'-';
                $content .= html_writer::end_tag('td');
            }

            // 測驗成績
			// 2021-0330,修正舊年度課程有測驗未取得成績顯示"未完成"
            $content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$sql = "SELECT id FROM {course_modules} WHERE course=:course AND module = (SELECT v.id FROM {modules} v WHERE v.name='quiz') AND instance not in (
				SELECT
					quizid
				FROM
					mdl_fet_pre_quiz
				WHERE
					pre_quiz = '1')";
			$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
			$QuId = 0;
			if($data) {
				if($data->id) {
					$QuId = $data->id;
				}
			}
			if($QuId>0) {
				$content .= ($record->quizgrade>0?$record->quizgrade:"未完成");
			}
			else {
				$content .= "-";
			}
            // $sql = "SELECT id FROM {course_modules} WHERE course=:course AND module = (SELECT v.id FROM {modules} v WHERE v.name='quiz')";
            // $data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
            // $QuId = 0;
            // if($data) {
            //     if($data->id) {
            //         $QuId = $data->id;
            //     }
            // }
            // if($QuId>0) {

            // }
            // else {
            //     $content .= "-";
            // }
            //$content .= ($record->quizgrade>0?$record->quizgrade:"-");//2021-0330註解
            $content .= html_writer::end_tag('td');

            // 完成測驗日期
            $content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
            $content .= !empty($quiz_ary[$record->courseid])?date('Y-m-d',$quiz_ary[$record->courseid]):'-';
            $content .= html_writer::end_tag('td');


            // 問卷完成與否
            $content .= html_writer::start_tag('td', array('class'=>'cell c8', 'style'=>'text-align:center;'));
            //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'未完成';
            $fbHref = "-";
            if(checkfeedback($record->courseid)) {
                $sql = "SELECT id FROM {course_modules} WHERE course=:course and module = (SELECT v.id FROM mdl_modules v WHERE v.name='feedback')";
                $data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
                $fbId = 0;
                if($data) {

                    if($data->id) {
                        $fbId = $data->id;
                    }
                }
                if($fbId>0) {
                    $fbHref = "<a href='/elearn/mod/feedback/view.php?id=$fbId'>未完成</a>";
                }
                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'未完成';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
            else {
                //$content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':'-';
                $content .= feedbackIsComplete($USER->id, $record->courseid)?'已完成':$fbHref;
            }
            $content .= html_writer::end_tag('td');

            // 課程完成與否
            $content .= html_writer::start_tag('td', array('class'=>'cell c9', 'style'=>'text-align:center;'));
            $content .=  isset($record->timecomplete)?'已完成':'未完成';
            $content .= html_writer::end_tag('td');

            // 列印證書
            $content .= html_writer::start_tag('td', array('class'=>'cell c10 lastcol', 'style'=>'text-align:center;'));
            // $content .= html_writer::select(array(1 => '一般民眾', 2 => '公務人員'), '請選擇', 0);

            // if(!empty($record->hours_set))
            // {
                if($record->timecomplete > 0)
                {
                    // $content .= "<input type='button' onclick='printpdf($record->courseid,0)' value='列印證明'>";
                    $content .= "<input style = 'width:30px; height:20px' type='checkbox' class ='courseid' value=$record->courseid >";
                }
            // }
            // else
            // {
            //  if($record->uploadstatus > 0)
            //  {
            //      $content .= "<input type='button' onclick='printpdf($record->courseid,1)' value='列印證明'>";
            //  }
            // }
            $content .= html_writer::end_tag('td');
            $content .= html_writer::end_tag('tr');

            $odd = $odd?0:1;
        }
    }
    $edu = $env ='';
    //if(!empty($record->env))
    {
        $env = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c5 simplify" scope="col">已上傳<br>認證時數<br>(環境教育)</th>';
    }
    //if(!empty($record->edu))
    {
        $edu = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c6 simplify" scope="col">已上傳<br>認證時數<br>(全國教師)</th>';
    }

	$sortClassName = $sortSignDate = $sortClassDate = $sortHour = $sortGrade = $sortComplete = $sortQuestion = "";

	$sortClassName .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classname\" data-sorttype=\"asc\">▲</a>"; 
	$sortClassName .= "<a href=\"\" class=\"sort\" data-fieldname=\"classname\" data-sorttype=\"desc\">▼</a>"; 

	$sortSignDate .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"signDate\" data-sorttype=\"asc\">▲</a>"; 
	$sortSignDate .= "<a href=\"\" class=\"sort\" data-fieldname=\"signDate\" data-sorttype=\"desc\">▼</a>";

	$sortClassDate .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classDate\" data-sorttype=\"asc\">▲</a>"; 
	$sortClassDate .= "<a href=\"\" class=\"sort\" data-fieldname=\"classDate\" data-sorttype=\"desc\">▼</a>";

	$sortHour .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"hour\" data-sorttype=\"asc\">▲</a>"; 
	$sortHour .= "<a href=\"\" class=\"sort\" data-fieldname=\"hour\" data-sorttype=\"desc\">▼</a>";
	
	$sortGrade .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classGrade\" data-sorttype=\"asc\">▲</a>"; 
	$sortGrade .= "<a href=\"\" class=\"sort\" data-fieldname=\"classGrade\" data-sorttype=\"desc\">▼</a>";	
	
	$sortComplete .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"complete\" data-sorttype=\"asc\">▲</a>"; 
	$sortComplete .= "<a href=\"\" class=\"sort\" data-fieldname=\"complete\" data-sorttype=\"desc\">▼</a>";
	
	$sortQuestion .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"question\" data-sorttype=\"asc\">▲</a>"; 
	$sortQuestion .= "<a href=\"\" class=\"sort\" data-fieldname=\"question\" data-sorttype=\"desc\">▼</a>";		
		

return <<<EOF
<table width="100%" cellpadding="5" cellspacing="1" class="generaltable">
<thead style="background-color:#D3E4ED">
    <tr>
        <th style="vertical-align:top; white-space:nowrap; center; width: 120px;" class="header c0" scope="col">課程名稱{$sortClassName}</th>
        <!-- <th style="vertical-align:top; white-space:nowrap; center; width: 10%;text-align: center;" class="header c0 simplify" scope="col">課程介紹</th> -->
        <!-- <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c1" scope="col">開課狀態</th> -->
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">報名<br>日期{$sortSignDate}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">修課時間{$sortClassDate}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c3" scope="col">認證時數{$sortHour}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c4 simplify" scope="col">已上傳<br>認證時數<br>(終身學習)</th>
        {$env}
        {$edu}
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">測驗成績{$sortGrade}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">完成<br>測驗日期{$sortComplete}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">問卷{$sortQuestion}</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c9" scope="col">課程完成與否</th>
        <th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c10 lastcol" scope="col">列印證明<br>全選
                <input type='checkbox' id='CheckAll' style="width:30px; height:20px; margin-right: 0px;">
        </th>
    </tr>
</thead>
<tbody>
    $content
</tbody>
</table>
<script>
$(document).ready(function(){
    $("#CheckAll").click(function(){
        if($("#CheckAll").prop("checked")){
            $("input[class='courseid']").prop("checked",true);
        }else{
            $("input[class='courseid']").prop("checked",false);
            }
    })
})
function printpdf(id,status)
{
    var str = '';
    var cnt = 0;
    if($("#idnoshow").prop("checked")){
	var idnoshow = 1;
	}else{
	var idnoshow = 0;
	}
    $(".courseid:checked").each(function() {
        str = str+this.value+',';
        cnt++;
    });
    var cid = str.substr(0,(str.length-1));
    
    if(cnt > 1)
    {
        var URL = 'output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+0+'&yy='+($year-1911);
        window.open(URL, '', config='height=600,width=480');
    }
    else if(cnt == 1)
    {
        var URL = 'one_output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+status+'&yy='+($year-1911);
        window.open(URL, '', config='height=600,width=480');
    }
    else
    {
        alert('請勾選課程');
    }
}
</script>
EOF;
}

/**
 * get pack curse record table
 */
function get_pack_courserecord(){
	global $USER, $DB;

	$sql = sprintf('SELECT a.id, courseid, certhour, gothours, quizgrade, timetotalstudy, timecomplete, uploadstatus, b.fullname
		FROM {fet_course_history} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
		WHERE userid = %d AND b.format=\'hvpack\' ORDER BY a.id ASC', $USER->id);

	$content = '';
	if($records = $DB->get_records_sql_ng($sql)){
		$odd=0;
		foreach ($records as $record) {
			$content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

			$content .= html_writer::start_tag('td', array('class'=>'cell c0'));
			$content .= html_writer::link(new moodle_url('/course/view.php', array('id'=>$record->courseid)), $record->fullname);
			$content .= html_writer::end_tag('td');

			$content .= html_writer::start_tag('td', array('class'=>'cell c8 lastcol', 'style'=>'text-align:center;'));
			$content .= isset($record->timecomplete)?'已完成':'未完成';
			$content .= html_writer::end_tag('td');

			$content .= html_writer::end_tag('tr');

			$odd = $odd?0:1;
		}
	}

	return <<<EOF
<table width="95%" cellpadding="5" cellspacing="1" class="generaltable boxaligncenter">
<thead style="background-color:#6FF08E">
	<tr>
		<th style="vertical-align:top;;white-space:nowrap;" class="header c0" scope="col">課程名稱</th>
		<th style="vertical-align:top; text-align:center;;white-space:nowrap;" class="header c8 lastcol" scope="col">課程完成與否</th>
	</tr>
</thead>
<tbody>
	$content
</tbody></table>
EOF;
}

/**
 * Get page-select html
 *
 * @param $current, int, current page-index
 * @param $total, int, total records count
 * @param limit, int, item per page
 * @param $near, int, near page-indxes to display
 *
 */
function get_courserecord_pages($current, $total, $limit, $near){
	$page_list = array();
	$pages = ceil($total / $limit);

	for($i=$current-$near;$i<=$current+$near;$i++){
		if($i>0 && $i<=$pages){
			array_push($page_list, $i);
		}
	}

	$page_content = array();
	if($current-$near>1){
		array_push($page_content, '('.html_writer::link(new moodle_url('/courserecord/index.php', array('idpagenum'=>$current-$near-1)), '往前').')');
	}

	for($i=0;$i<count($page_list);$i++){
		array_push($page_content, $page_list[$i]!=$current?html_writer::link(new moodle_url('/courserecord/index.php', array('pagenum'=>$page_list[$i])), $page_list[$i]):$page_list[$i]);
	}

	if($current+$near<$pages){
		array_push($page_content, '('.html_writer::link(new moodle_url('/courserecord/index.php', array('pagenum'=>$current+$near+1)), '往後').')');
	}

	$content = implode('&nbsp;&nbsp;', $page_content);
	return '<div style="text-align: center;margin: 10px 0 10px 0;">頁碼:&nbsp;&nbsp;'.$content.'</div>';
}

function getCountingTimeDisplay($ts) {
	$split_base = array(60, 60, 8760);
	$name_base = array('秒', '分', '時');
	$split = array();

	for($i=0;$i<3 && $ts>0;$i++) {
		array_push($split, ($ts % $split_base[$i]).$name_base[$i]);
		$ts = floor($ts/$split_base[$i]);
	}

	$str = implode('', array_reverse($split));
	return $str;
}

/**
 * 查詢對應學員課程的人工核發時數
 * @param $uid, int, userId
 * @param $cid, int, courseId
 * @param return Hour(seconds/3600) or zero
 *
 */
function queryArtificialHours($uid, $cid) {
	global $DB;
	$sql = "select sum(mh.ts) total
			from {fet_artificial_hour} mh
			where mh.userid = :userid
				and mh.courseid = :courseid ";
	$result = $DB->get_record_sql($sql, array("userid"=>$uid, "courseid"=>$cid));
	if($result) {
		return $result->total/3600;
	}
	else {
		return 0;
	}
}

/**
 * get curse record table
 *
 * @param $year, int of query year
 * @param $limit, int of query limit
 */
function get_courserecord_old($type, $year, $offset, $limit,$queryData=array()){
	global $USER, $DB;
	//if($USER->username!="admin")
	//	die("功能維護中");

	require_once('../customize/edaold.php');


	$obj = new DB_edaold();
	$data = $obj->getDataList($year,$type,$offset,$limit, $queryData);
	$content = $edu = $env ="";
	for($i=0; $i<count($data); $i++) {
		$odd=0;
		$classStatus = "未開放";
		$st_begin = explode(" ", $data[$i]['ST_BEGIN']);
		$st_end = explode(" ", $data[$i]['ST_END']);
		$add_time = explode(" ", $data[$i]['ADD_TIME']);
		$date_now = new DateTime();
		$date_be = new DateTime($st_begin[0]);
		$date_ed = new DateTime($st_end[0]);
		
		// if("zwei0910"==$USER->username && $data[$i]['co_isreadtimevalid']=="Y") {
			// $courseid = $data[$i]['COURSE_ID'];
			// $uuid = $USER->uuid;
			// $nohaveProveRecords($uuid, $courseid, $year, 1);
			// echo "<br>".$courseid;
			// echo "<br>".$uuid;
			// die();
		// }

		if($date_now>=$date_be && $date_now<=$date_ed) {
			$classStatus = "開課中";
		}
		$o = unserialize($data[$i]['CAPTION']);
		$classname = $o['Big5'];
		$content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

		$content .= html_writer::start_tag('td', array('class'=>'cell c0'));
		$content .= $classname;
		$content .= html_writer::end_tag('td');

		$content .= html_writer::start_tag('td', array('class'=>'cell c1', 'style'=>'text-align:center;'));
		$content .= $classStatus;
		$content .= html_writer::end_tag('td');

		// 報名日期
		$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
		$content .= empty($add_time[0])?"-":$add_time[0];
		$content .= html_writer::end_tag('td');

		// 修課時間
		$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
		$rss = getCountingTimeDisplay($data[$i]['rss']);
		$content .= $rss==""?"-":$rss;
		$content .= html_writer::end_tag('td');

		// 認證時數
		$content .= html_writer::start_tag('td', array('class'=>'cell c3', 'style'=>'text-align:center;'));
		$content .= $data[$i]['co_gettime'];
		$content .= html_writer::end_tag('td');

		// 已上傳認證時數(ECPA)
		$content .= html_writer::start_tag('td', array('class'=>'cell c4', 'style'=>'text-align:center;'));
		//$content .= '-';
		//isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';
		$content .= html_writer::end_tag('td');

		// 已上傳認證時數(環教)
		// if(!empty($record->env))
		// {
		// 	$content .= html_writer::start_tag('td', array('class'=>'cell c5', 'style'=>'text-align:center;'));
		// 	$content .= '-';
		// 	isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';
		// 	$content .= html_writer::end_tag('td');
		// }
		// 已上傳認證時數(全教)
		// if(!empty($record->edu))
		// {
		// 	$content .= html_writer::start_tag('td', array('class'=>'cell c6', 'style'=>'text-align:center;'));
		// 	$content .= '-';
		// 	isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';
		// 	$content .= html_writer::end_tag('td');
		// }

		// 測驗成績
		$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
		$content .= isset($data[$i]['score'])?$data[$i]['score']:'-';
		//$content .= isset($record->quizgrade)?$record->quizgrade:'-';
		$content .= html_writer::end_tag('td');

		// 測驗成績
		$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
		$content .= '-';
		$content .= html_writer::end_tag('td');

		// 問卷完成與否
		$content .= html_writer::start_tag('td', array('class'=>'cell c8', 'style'=>'text-align:center;'));
	//	$content .= feedbackIsComplete($USER->id, $record->id)?'已完成':'未完成';
		$content .= html_writer::end_tag('td');

		// 課程完成與否
		$content .= html_writer::start_tag('td', array('class'=>'cell c9', 'style'=>'text-align:center;'));
		$content .= $data[$i]['co_isreadtimevalid']=="Y"?'已完成':'未完成';
		$content .= html_writer::end_tag('td');

		// 列印證書
		$content .= html_writer::start_tag('td', array('class'=>'cell c10 lastcol', 'style'=>'text-align:center;'));
		// $content .= html_writer::select(array(1 => '一般民眾', 2 => '公務人員'), '請選擇', 0);

		if($data[$i]['co_isreadtimevalid']=="Y") {
			$courseid = $data[$i]['COURSE_ID'];
			$uid = $USER->id;
			$content .= "<input style = 'width:30px; height:20px' type='checkbox' class ='courseid' value=$courseid >";
		}
		$content .= html_writer::end_tag('td');
		$content .= html_writer::end_tag('tr');

		$odd = $odd?0:1;
	}

	$sortClassName = $sortSignDate = $sortClassDate = $sortHour = $sortGrade = $sortComplete = $sortQuestion = "";

	$sortClassName .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classname\" data-sorttype=\"asc\">▲</a>"; 
	$sortClassName .= "<a href=\"\" class=\"sort\" data-fieldname=\"classname\" data-sorttype=\"desc\">▼</a>"; 

	$sortSignDate .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"signDate\" data-sorttype=\"asc\">▲</a>"; 
	$sortSignDate .= "<a href=\"\" class=\"sort\" data-fieldname=\"signDate\" data-sorttype=\"desc\">▼</a>";

	$sortClassDate .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classDate\" data-sorttype=\"asc\">▲</a>"; 
	$sortClassDate .= "<a href=\"\" class=\"sort\" data-fieldname=\"classDate\" data-sorttype=\"desc\">▼</a>";

	$sortHour .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"hour\" data-sorttype=\"asc\">▲</a>"; 
	$sortHour .= "<a href=\"\" class=\"sort\" data-fieldname=\"hour\" data-sorttype=\"desc\">▼</a>";
	
	$sortGrade .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"classGrade\" data-sorttype=\"asc\">▲</a>"; 
	$sortGrade .= "<a href=\"\" class=\"sort\" data-fieldname=\"classGrade\" data-sorttype=\"desc\">▼</a>";	
	
	$sortComplete .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"complete\" data-sorttype=\"asc\">▲</a>"; 
	$sortComplete .= "<a href=\"\" class=\"sort\" data-fieldname=\"complete\" data-sorttype=\"desc\">▼</a>";
	
	$sortQuestion .= "<br><a href=\"\" class=\"sort\" data-fieldname=\"question\" data-sorttype=\"asc\">▲</a>"; 
	$sortQuestion .= "<a href=\"\" class=\"sort\" data-fieldname=\"question\" data-sorttype=\"desc\">▼</a>";		
		

	return <<<EOF
<table width="100%" cellpadding="5" cellspacing="1" class="generaltable boxaligncenter">
<thead style="background-color:#D3E4ED">
	<tr>
		<th style="vertical-align:top; white-space:nowrap; center; width: 120px;" class="header c0" scope="col">課程名稱{$sortClassName}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c1" scope="col">開課狀態</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c1" scope="col">報名日期{$sortSignDate}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">修課時間{$sortClassDate}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c3" scope="col">認證時數{$sortHour}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c4" scope="col">已上傳<br>認證時數<br>(ECPA)</th>
		<!-- {$env} -->
		<!-- {$edu} -->
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">測驗成績{$sortGrade}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">完成<br>測驗日期{$sortComplete}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">問卷{$sortQuestion}</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c9" scope="col">課程完成與否</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c10 lastcol" scope="col">列印證明<br>全選
		<input type='checkbox' id='CheckAll' style="width:30px; height:20px; margin-right: 0px;">
		</th>
	</tr>
</thead>
<tbody>
	$content
</tbody></table>
<script>
$(document).ready(function(){
  	$("#CheckAll").click(function(){
   		if($("#CheckAll").prop("checked")){
    		$("input[class='courseid']").prop("checked",true);
   		}else{
    		$("input[class='courseid']").prop("checked",false);
   			}
  	})
})
function printpdf(u,y)
{
	var str = '';
	var cnt = 0;
	if($("#idnoshow").prop("checked")){
	var idnoshow = 1;
	}else{
	var idnoshow = 0;
	}
	$(".courseid:checked").each(function() {
		str = str+this.value+',';
		cnt++;
	});
	var cid = str.substr(0,(str.length-1));
	if(cnt > 1)
	{
	   	var URL = 'old_outputs.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+0+'&yy='+y;
		window.open(URL, '', config='height=600,width=480');
	}
	else if(cnt == 1)
	{
		var URL = 'old_output.php?idnoshow='+idnoshow+'&uu='+u+'&cc='+cid+'&yy='+y;
		window.open(URL, '', config='height=600,width=480');
	}
	else
	{
		alert('請勾選課程');
	}
}
function old_printpdf(u,c,y)
{
	var URL = 'old_output.php?uu='+u+'&cc='+c+'&yy='+y;
	window.open(URL, '', config='height=800,width=600');
}
</script>
EOF;
	// $edu = $env ='';
	// if(!empty($record->env))
	// {
		// $env = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c5" scope="col">已上傳<br>認證時數<br>(環教)</th>';
	// }
	// if(!empty($record->edu))
	// {
		// $edu = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c6" scope="col">已上傳<br>認證時數<br>(全教)</th>';
	// }
}

function showPage_oldY($year, $uid, $ctype, $limit_start = 0) {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	if($ctype==1) {
		$where_s = "and a.timecomplete > 0";
	}
	elseif($ctype==2) {
		$where_s = "and a.timecomplete is null";
	}
	else {
		$where_s = "";
	}
	$year = intval(substr($year, 1, strlen($year)))+1911;
	$sql = sprintf("SELECT count(1) cnt
                    FROM mdl_fet_course_history_$year a
                    JOIN mdl_course_$year b
                    ON a.courseid=b.id
                    JOIN mdl_fet_course_data_$year c
                    ON a.courseid=c.courseid
                    LEFT OUTER JOIN (
                        SELECT
                        `mdl_fet_artificial_hour_$year`.`userid` AS `userid`,
                        `mdl_fet_artificial_hour_$year`.`courseid` AS `courseid`,
                        sum(
                            `mdl_fet_artificial_hour_$year`.`ts`
                        ) AS `ts`
                    FROM
                        `mdl_fet_artificial_hour_$year`
                    GROUP BY
                        `mdl_fet_artificial_hour_$year`.`userid`,
                        `mdl_fet_artificial_hour_$year`.`courseid`
                    )d
                    ON a.courseid=d.courseid
                    AND a.userid=d.userid
                    LEFT JOIN mdl_fet_cert_setting d ON a.userid = d.uid
                    LEFT JOIN mdl_fet_upload_hours_$year f ON a.userid = f.uid AND a.courseid=f.cid
                    WHERE a.userid=%s %s", $uid,$where_s);

	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }
	$getUrl = $CFG->wwwroot.'/courserecord/index.php?cstatus='.$ctype;

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a id='".$ctype.','.'1'."' href='#' class='pageBtn' onclick='isChecked(this.id)'>第一頁</a>";
		$previousPageHtml = "<a id='".$ctype.','.($thisPage-1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a id='".$ctype.','.($thisPage+1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>下一頁</a>";
		$lastPageHtml = "<a id='".$ctype.','.(ceil($thisConut/$limit))."' href='#' class='pageBtn' onclick='isChecked(this.id)'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}

echo <<<EOF
	<script>
	var link = "http://elearning.taipei/elearn/courserecord/index.php?cstatus=";
	function isChecked(id){	
		var id_Array = new Array();
		id_Array = id.split(",");
		location.href = link + id_Array[0] + "&pagenum=" + id_Array[1] + '&oldY='+$('#select_old').val();
	}
	</script>
EOF;

	$html .=
		"<td width='84%' style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td>
		<td width='16%' style='text-align:right'>
			<input type='button' onclick='printpdf()' value='列印證明'>
		</td></tr>
		</tbody></table>";

	return $html;
}

function showPage_oldY_new($list, $year, $uid, $ctype, $limit_start = 0, $queryData = array()) {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = $list;//每頁顯示筆數
	$params = array();

	if($ctype==1) {
		$where_s = "and a.timecomplete > 0";
	}
	elseif($ctype==2) {
		$where_s = "and a.timecomplete is null";
	}
	else {
		$where_s = "";
	}

	$year = intval($year)+1911;
	
	$classname = "";
	if (isset($queryData['course_name'])){
		$classname = " AND b.fullname LIKE :course_name";
		$params['course_name'] = '%'.$queryData['course_name'].'%';
	}

	$sql = sprintf("SELECT count(1) cnt
                    FROM mdl_fet_course_history_$year a
                    JOIN mdl_course_$year b
                    ON a.courseid=b.id
                    JOIN mdl_fet_course_data_$year c
                    ON a.courseid=c.courseid
                    LEFT OUTER JOIN (
                        SELECT
                        `mdl_fet_artificial_hour_$year`.`userid` AS `userid`,
                        `mdl_fet_artificial_hour_$year`.`courseid` AS `courseid`,
                        sum(
                            `mdl_fet_artificial_hour_$year`.`ts`
                        ) AS `ts`
                    FROM
                        `mdl_fet_artificial_hour_$year`
                    GROUP BY
                        `mdl_fet_artificial_hour_$year`.`userid`,
                        `mdl_fet_artificial_hour_$year`.`courseid`
                    )d
                    ON a.courseid=d.courseid
                    AND a.userid=d.userid
                    LEFT JOIN mdl_fet_cert_setting d ON a.userid = d.uid
                    LEFT JOIN mdl_fet_upload_hours_$year f ON a.userid = f.uid AND a.courseid=f.cid
                    WHERE a.userid=%s $classname %s", $uid,$where_s);

	$resource = $DB->get_record_sql($sql, $params);
	
	if($resource) {
		$thisConut = $resource->cnt;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='?".getQueryStringWithoutPage(1)."' class='pageBtn paginate' '>第一頁</a>";
		$previousPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage-1)."' class='pageBtn paginate'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage+1)."' class='pageBtn paginate'>下一頁</a>";
		$lastPageHtml = "<a href='?".getQueryStringWithoutPage(ceil($thisConut/$limit))."' class='pageBtn paginate'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}


	$select_10 = '';
	$select_50 = '';
	if($list == '10'){
		$select_10 = "selected";
	} 

	if($list == '50'){
		$select_50 = "selected";
	}


	$html .= "
	$firstPageHtml
	$previousPageHtml
	$ahref_html
	$nextPageHtml
	$lastPageHtml
	<select name='data_count' id='data_count' style='margin-bottom:3px;height:25px''>
		<option value='10'".$select_10.">每頁顯示10筆</option>
		<option value='50'".$select_50.">每頁顯示50筆</option>
	</select>
";		

	
	return $html;
}

function showPage_old($year, $uid, $ctype, $limit_start = 0) {
	global $CFG, $DB, $USER;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	if($ctype==1) {
		$where_s = "where a.co_isreadtimevalid = 'Y'";
	}
	elseif($ctype==2) {
		$where_s = "where a.co_isreadtimevalid is null";
	}
	else {
		$where_s = "";
	}
	$year+=1911;
	$where = "";
	if($year!=1910) { //等於-1的意思
		$where = "and DATE_FORMAT(a.ADD_TIME,'%Y') = $year";
	}

	require_once('../customize/edaold.php');

	$obj = new DB_edaold();
	$username = $obj->getOldUsername($USER->id);
	$resource = $obj->getOldCount($username,$where,$where_s);

	if($resource) {
		$thisConut = $resource;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }
	$getUrl = $CFG->wwwroot.'/courserecord/index.php?cstatus='.$ctype;

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a id='".$ctype.','.'1'."' href='#' class='pageBtn' onclick='isChecked(this.id)'>第一頁</a>";
		$previousPageHtml = "<a id='".$ctype.','.($thisPage-1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a id='".$ctype.','.($thisPage+1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>下一頁</a>";
		$lastPageHtml = "<a id='".$ctype.','.(ceil($thisConut/$limit))."' href='#' class='pageBtn' onclick='isChecked(this.id)'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}

echo <<<EOF
	<script>
	var link = "http://elearning.taipei/elearn/courserecord/index.php?cstatus=";
	function isChecked(id){	
		var id_Array = new Array();
		id_Array = id.split(",");
		location.href = link + id_Array[0] + "&pagenum=" + id_Array[1] + '&old='+$('#select_old').val();
	}
	</script>
EOF;

	$html .=
		"<td width='84%' style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td>
		<td width='16%' style='text-align:right'>
			<input type='button' onclick='printpdf($uid,$year-1911)' value='列印證明'>
		</td></tr>
		</tbody></table>";

	return $html;
}

function showPage_old_new($list, $year, $uid, $ctype, $limit_start = 0, $queryData = array()) {
	global $CFG, $DB, $USER;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = $list;//每頁顯示筆數
	if($ctype==1) {
		$where_s = "where a.co_isreadtimevalid = 'Y'";
	}
	elseif($ctype==2) {
		$where_s = "where a.co_isreadtimevalid is null";
	}
	else {
		$where_s = "";
	}
	$year+=1911;
	$where = "";
	if($year!=1910) { //等於-1的意思
		$where = "and DATE_FORMAT(a.ADD_TIME,'%Y') = $year";
	}

	require_once('../customize/edaold.php');

	$obj = new DB_edaold();
	$username = $obj->getOldUsername($USER->id);
	$resource = $obj->getOldCount($username,$where,$where_s, $queryData);

	if($resource) {
		$thisConut = $resource;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }


	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='?".getQueryStringWithoutPage(1)."' class='pageBtn paginate' >第一頁</a>";
		$previousPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage-1)."' class='pageBtn paginate' >上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage+1)."' class='pageBtn paginate'>下一頁</a>";
		$lastPageHtml = "<a href='?".getQueryStringWithoutPage(ceil($thisConut/$limit))."' class='pageBtn paginate'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}			

	$select_10 = '';
	$select_50 = '';
	if($list == '10'){
		$select_10 = "selected";
	} 

	if($list == '50'){
		$select_50 = "selected";
	}
	

		$html .=
		"<td width='84%' style='text-align:center' colspan=\"2\">
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		<select name='data_count' id='data_count' style='margin-bottom:3px;height:25px'>
			<option value='10'".$select_10.">每頁顯示10筆</option>
			<option value='50'".$select_50.">每頁顯示50筆</option>
		</select>
		</td>
";		

	return $html;
}

function showPage_fix($uid, $ctype, $limit_start = 0 ,$search = '') {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	if($ctype==1) {
		$where_s = "and a.timecomplete > 0";
	}
	elseif($ctype==2) {
		$where_s = "and a.timecomplete is null";
	}
	else {
		$where_s = "";
	}

	if(!empty($search))
	{
		$where_s.= " and b.fullname like '%".$search."%'";
	}

	$sql = "SELECT count(1) cnt FROM {fet_course_history} AS a
			LEFT JOIN {course} AS b ON a.courseid = b.id
			LEFT JOIN {context} mc on b.id=mc.instanceid AND mc.contextlevel=50
			JOIN {role_assignments} mr ON mr.contextid=mc.id
				AND mr.userid = a.userid
			LEFT JOIN {fet_cert_setting} AS c ON a.userid = c.uid
			WHERE a.userid = $uid
			AND b.format = 'topics' $where_s ";

	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }
	$getUrl = $CFG->wwwroot.'/courserecord/index.php?cstatus='.$ctype;

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a id='".$ctype.','.'1'."' href='#' class='pageBtn' onclick='isChecked(this.id)'>第一頁</a>";
		$previousPageHtml = "<a id='".$ctype.','.($thisPage-1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a id='".$ctype.','.($thisPage+1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>下一頁</a>";
		$lastPageHtml = "<a id='".$ctype.','.(ceil($thisConut/$limit))."' href='#' class='pageBtn' onclick='isChecked(this.id)'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}

echo <<<EOF
	<script>
	var link = "http://elearning.taipei/elearn/courserecord/index.php?cstatus=";
	var link_search = "http://elearning.taipei/elearn/courserecord/index.php?ssearch=";
	function isChecked(id){
		if($("#r1s2").prop("checked")){
			var id_Array = new Array();
			id_Array = id.split(",");
			if ($.trim($("#ssearch").val()) == ""){
				location.href = link + id_Array[0] + "&pagenum=" + id_Array[1] + "&mode=1";
			} else {
				location.href = link_search + $("#ssearch").val() + "&pagenum=" + id_Array[1] + "&mode=1&cstatus=" + id_Array[0];
			}
		}else{
			var id_Array = new Array();
			id_Array = id.split(",");
			if ($.trim($("#ssearch").val()) == ""){
				location.href = link + id_Array[0] + "&pagenum=" + id_Array[1] + "&mode=0";
			} else {
				location.href = link_search + $("#ssearch").val() + "&pagenum=" + id_Array[1] + "&mode=0&cstatus=" + id_Array[0];
			}
		}
	}
	</script>
EOF;
	
	$html .=
	"<table width='100%'><tbody>
	<tr><td width='84%' style='text-align:center'>
	$firstPageHtml
	$previousPageHtml
	$ahref_html
	$nextPageHtml
	$lastPageHtml
	</td>
	<td width='16%' style='text-align:right'>
		<input type='button' onclick='printpdf()' value='列印證明'>
	</td></tr>
	</tbody></table>";
	
	return $html;
}

function showPage_fix_new($list, $uid, $ctype, $limit_start = 0 ,$search = '',$bottom='') {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = $list;
	if($ctype==1) {
		$where_s = "and a.timecomplete > 0";
	}
	elseif($ctype==2) {
		$where_s = "and a.timecomplete is null";
	}
	else {
		$where_s = "";
	}

	if(!empty($search))
	{
		$where_s.= " and b.fullname like '%".$search."%'";
	}

	// $sql = "SELECT count(1) cnt FROM {fet_course_history} AS a
	// 		LEFT JOIN {course} AS b ON a.courseid = b.id
	// 		LEFT JOIN {context} mc on b.id=mc.instanceid AND mc.contextlevel=50
	// 		JOIN (SELECT DISTINCT userid, contextid FROM {role_assignments}) mr ON mr.contextid=mc.id
	// 			AND mr.userid = a.userid
	// 		LEFT JOIN {fet_cert_setting} AS c ON a.userid = c.uid
	// 		WHERE a.userid = $uid
	// 		AND b.format = 'topics' $where_s ";

	$sql = "SELECT count(1) cnt FROM {fet_course_history} AS a
			LEFT JOIN {course} AS b ON a.courseid = b.id
			LEFT JOIN {context} mc on b.id=mc.instanceid AND mc.contextlevel=50
			JOIN mdl_role_assignments mr ON mr.roleid=5 and mr.contextid=mc.id
				AND mr.userid = a.userid
			LEFT JOIN {fet_cert_setting} AS c ON a.userid = c.uid
			WHERE a.userid = $uid
			AND b.format = 'topics' $where_s ";

	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }
	$getUrl = $CFG->wwwroot.'/courserecord/index.php?cstatus='.$ctype;


	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='?".getQueryStringWithoutPage(1)."' class='pageBtn paginate'>第一頁</a>";
		$previousPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage-1)."' class='pageBtn paginate'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='?".getQueryStringWithoutPage($thisPage+1)."' class='pageBtn paginate'>下一頁</a>";
		$lastPageHtml = "<a href='?".getQueryStringWithoutPage(ceil($thisConut/$limit))."' class='pageBtn paginate'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtnS paginate'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='?".getQueryStringWithoutPage($i+1)."' class='cntBtn paginate'>".($i+1)."</a>";
			}
		}
	}

	$select_10 = '';
	$select_50 = '';
	if($list == '10'){
		$select_10 = "selected";
	} 

	if($list == '50'){
		$select_50 = "selected";
	} 

	if($bottom == 'Y'){
		$change_id = 'data_count_bottom';
	} else {
		$change_id = 'data_count';
	}

	$html .=
	"<table width='100%'><tbody>
	<tr><td width='84%' style='text-align:center'>
	$firstPageHtml
	$previousPageHtml
	$ahref_html
	$nextPageHtml
	$lastPageHtml
	<select name='data_count' id='$change_id' style='margin-bottom:3px;height:25px'>
		<option value='10'".$select_10.">每頁顯示10筆</option>
		<option value='50'".$select_50.">每頁顯示50筆</option>
	</select>
	</td>
	<td width='16%' style='text-align:right'>
		<input type='button' onclick='printpdf()' value='列印證明'>
	</td></tr>
	</tbody></table>";		

	return $html;
}


/**
 * 查詢對應學員課程的人工核發時數
 * @param $uid, int, userId
 * @param return array
 *
 */
function queryUserUploadHours($uid, $year = null) {
	global $DB;

	if ($year >= 105 && $year < intVal(date('Y'))){
		$year = '_'.($year+1911);
	}else{
		$year = null;
	}

	$cert_ary = array(0, 0, 0);
	$sql = "select sum(mh.ecpa) ecpa, sum(mh.hcert1) hcert1, sum(mh.hcert2) hcert2
			from {fet_upload_hours$year} mh
			where mh.uid = :uid";
	//echo $sql;
	$result = $DB->get_record_sql($sql, array("uid"=>$uid));
	if($result) {
		if(!empty($result->ecpa))
			$cert_ary[0] = $result->ecpa;
		if(!empty($result->hcert1))
			$cert_ary[1] = $result->hcert1;
		if(!empty($result->hcert2))
			$cert_ary[2] = $result->hcert2;
	}
	return $cert_ary;
}

/**
 * 查詢對應學員mobitime
 * @param $uid, int, userId
 * @param $cid, int, courseId
 * @param return int
 *
 */
function queryUserMobiTime($uid, $cid) {
	global $DB;
	$cert_ary = array(0, 0, 0);
	$sql = "select mobitime from {fet_course_history}
			where userid = :userid and courseid = :courseid";
	//echo $sql;
	$result = $DB->get_record_sql($sql, array("userid"=>$uid, "courseid"=>$cid));
	if($result) {
		if(!empty($result->mobitime)) {
			return $result->mobitime;
		}
	}
	return 0;
}




/**
 * 取得課程時數(報名時數、完成時數)
 * @param $uid,int,使用者ID
 * @param $type string 查詢項目(reg,complete)
 * @param return hours(int) 總時數
 */
function get_course_total_hours($uid,$type)
{
	global $DB;

	if($type == 'reg')
	{
		$where = '';
	}
	else if($type == 'complete')
	{
		$where = ' and timecomplete > 0 ';
	}
	else
	{
		die('not type');
	}
	$sql = "select sum(a.certhour) as hours from
				(
					select * from mdl_fet_course_history
					where userid = :uid ".$where."
				) a
				join mdl_course b on a.courseid = b.id
				join mdl_fet_course_data c on c.courseid = b.id
				join
				(
				SELECT b.instanceid from mdl_context b
				LEFT JOIN mdl_role_assignments c ON c.contextid =b.id
				WHERE c.userid = :userid AND b.contextlevel=50
				) reg on reg.instanceid = b.id
				where b.format='topics' ";

	$result = $DB->get_record_sql($sql, array("uid"=>$uid,"userid"=>$uid));

	return $result->hours;
}




function get_ecpa_gothours($uid, $year = null)
{
	global $DB;

	if ($year >= 105 && $year < intVal(date('Y'))){
		$year = '_'.($year+1911);
	}else{
		$year = null;
	}

	$sql = "select ifnull(sum(mh.gothours), 0) ecpa
			from {fet_course_history$year} mh
			where mh.userid = :uid";

	//echo $sql;
	$result = $DB->get_record_sql($sql, array("uid"=>$uid));

	return $result->ecpa;


}





function get_courserecord_newYear($offset, $type, $limit, $rUpdate=0,$year=0){
	global $USER, $DB;

	$year = intval(substr($year, 1, strlen($year)))+1911;

	if($type==1) {
		$type = " and a.timecomplete > 0 ";
	}
	elseif($type==2) {
		$type = " and a.timecomplete is null ";
	}
	else {
		$type = " and a.timecomplete > 0 ";
	}
	//20160407 增加註冊過的課程才顯示 by Hao
	$sql = sprintf("SELECT a.id, a.courseid, IFNULL(a.certhour,tmp.certhour) certhour, gothours, quizgrade, ifnull(timetotalstudy, 0) + ifnull(mobitime, 0) + ifnull(fah.ts, 0) timetotalstudy
					, timecomplete, uploadstatus, b.fullname, IF(UNIX_TIMESTAMP(CURRENT_TIMESTAMP) >= b.startdate AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP)<b.enddate+60*60*24, TRUE, FALSE) coursebegin,c.ecpa,c.edu,c.name AS env
					,mh.ecpa AS ecpa_, mh.hcert1, mh.hcert2,c.id AS hours_set ,a.timecreate
					FROM {fet_course_history_$year} AS a LEFT JOIN {course} AS b ON a.courseid=b.id
					JOIN (SELECT b.instanceid FROM {context} b
						LEFT JOIN {role_assignments} c ON c.contextid =b.id
						WHERE c.userid=%d AND b.contextlevel=50) tmp ON a.courseid = tmp.instanceid
					LEFT JOIN {fet_cert_setting} AS c ON a.userid =c.uid
					LEFT JOIN {fet_upload_hours_$year} mh ON a.userid = mh.uid AND a.courseid = mh.cid
					LEFT OUTER JOIN {fet_course_data_$year} tmp ON a.courseid = tmp.courseid
					LEFT OUTER JOIN view_total_artifical fah ON a.courseid = fah.courseid AND a.userid = fah.userid
					WHERE a.userid = %d ".$type." AND b.format='topics'  ORDER BY UNIX_TIMESTAMP(a.timecreate) DESC LIMIT %d OFFSET %d", $USER->id, $USER->id, $limit, $offset);

	$content = '';


	if($records = $DB->get_records_sql_ng($sql)){


		$odd=0;

		foreach ($records as $record) {
			$content .= html_writer::start_tag('tr', array('class'=>'r'.$odd));

			$content .= html_writer::start_tag('td', array('class'=>'cell c0'));
			$content .=  $record->fullname ;
			// $content .= html_writer::link(new moodle_url('/course/view.php', array('id'=>$record->courseid)), $record->fullname);
			$content .= html_writer::end_tag('td');


			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$timecreate = explode(' ',$record->timecreate);
			$content .= $record->timecreate?$timecreate['0']:'-';
			$content .= html_writer::end_tag('td');


			// 修課時間
			$content .= html_writer::start_tag('td', array('class'=>'cell c2', 'style'=>'text-align:center;'));
			$content .= getCountingTimeDisplay(isset($record->timetotalstudy)?$record->timetotalstudy:0) ;
			$content .= html_writer::end_tag('td');

			// 認證時數
			$content .= html_writer::start_tag('td', array('class'=>'cell c3', 'style'=>'text-align:center;'));
			$content .= isset($record->certhour)?$record->certhour:0;
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(ECPA)
			$content .= html_writer::start_tag('td', array('class'=>'cell c4 simplify', 'style'=>'text-align:center;'));
			// $content .= '-';
			// isset($record->uploadstatus)?(isset($record->certhour)?$record->certhour:0):'-';

			$content .= (!empty($record->gothours))?'已上傳':'-';
			$content .= html_writer::end_tag('td');

			// 已上傳認證時數(環教)
			//if(!empty($record->env))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c5 simplify', 'style'=>'text-align:center;'));

				$content .= ($record->hcert1>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}
			// 已上傳認證時數(全教)
			//if(!empty($record->edu))
			{
				$content .= html_writer::start_tag('td', array('class'=>'cell c6 simplify', 'style'=>'text-align:center;'));
				$content .= ($record->hcert2>0)?'已上傳':'-';
				$content .= html_writer::end_tag('td');
			}

			// 上傳狀態
			// $content .= html_writer::start_tag('td', array('class'=>'cell c5', 'style'=>'text-align:center;'));
			// $content .= isset($record->uploadstatus)?'已上傳':'未上傳';
			// $content .= html_writer::end_tag('td');

			// 測驗成績
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$sql = "SELECT id FROM {course_modules} WHERE course=:course AND module = (SELECT v.id FROM {modules} v WHERE v.name='quiz') AND instance not in (
						SELECT
							quizid
						FROM
							mdl_fet_pre_quiz
						WHERE
							pre_quiz = '1')";
			$data = $DB->get_record_sql($sql, array("course"=>$record->courseid));
			$QuId = 0;
			if($data) {
				if($data->id) {
					$QuId = $data->id;
				}
			}
			if($QuId>0) {
				$content .= "<a href='/elearn/mod/quiz/view.php?id=$QuId'>".($record->quizgrade>0?$record->quizgrade:"未完成")."</a>";
			}
			else {
				$content .= "-";
			}
			$content .= html_writer::end_tag('td');
			// 完成測驗日期
			$content .= html_writer::start_tag('td', array('class'=>'cell c7', 'style'=>'text-align:center;'));
			$content .= !empty($quiz_ary[$record->courseid])?date('Y-m-d',$quiz_ary[$record->courseid]):'-';
			$content .= html_writer::end_tag('td');
			// 問卷完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c8', 'style'=>'text-align:center;'));
            $content .= feedbackIsComplete_year($USER->id, $record->courseid,$year)?'已完成':'-';
			$content .= html_writer::end_tag('td');

			// 課程完成與否
			$content .= html_writer::start_tag('td', array('class'=>'cell c9', 'style'=>'text-align:center;'));
			$content .=  isset($record->timecomplete)?'已完成':'未完成';
			$content .= html_writer::end_tag('td');

			// 列印證書
			$content .= html_writer::start_tag('td', array('class'=>'cell c10 lastcol', 'style'=>'text-align:center;'));
			// $content .= html_writer::select(array(1 => '一般民眾', 2 => '公務人員'), '請選擇', 0);

			// if(!empty($record->hours_set))
			// {
			if($record->timecomplete > 0)
			{
			    $content .= "<input style = 'width:30px; height:20px' type='checkbox' class ='courseid' value=$record->courseid >";
			}
			// }
			// else
			// {
			// 	if($record->uploadstatus > 0)
			// 	{
			// 		$content .= "<input type='button' onclick='printpdf($record->courseid,1)' value='列印證明'>";
			// 	}
			// }
			$content .= html_writer::end_tag('td');
			$content .= html_writer::end_tag('tr');

			$odd = $odd?0:1;
		}
	}
	$edu = $env ='';
	//if(!empty($record->env))
	{
		$env = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c5 simplify" scope="col">已上傳<br>認證時數<br>(環境教育)</th>';
	}
	//if(!empty($record->edu))
	{
		$edu = '<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c6 simplify" scope="col">已上傳<br>認證時數<br>(全國教師)</th>';
	}

	$prinfY = $year - 1911 ;
	
return <<<EOF
<div style = "text-align:right; width:96.5%">
</div>
<table width="100%" cellpadding="5" cellspacing="1" class="generaltable">
<thead style="background-color:#D3E4ED">
	<tr>
		<th style="vertical-align:top; white-space:nowrap; center; width: 25%;" class="header c0" scope="col">課程名稱</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">報名<br>日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c2" scope="col">修課<br>時間</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c3" scope="col">認證<br>時數</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c4 simplify" scope="col">已上傳<br>認證時數<br>(終身學習)</th>
		
{$env}
		{$edu}
<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c7" scope="col">測驗<br>成績</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">完成<br>測驗日期</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c8" scope="col">問卷</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c9" scope="col">課程<br>完成與否</th>
		<th style="vertical-align:top; text-align:center;white-space:nowrap; padding-right: 2px;padding-left: 2px;" class="header c10 lastcol" scope="col">列印證明<br>全選
				<input type='checkbox' id='CheckAll' style="width:30px; height:20px; margin-right: 0px;">
		</th>
	</tr>
</thead>
<tbody>
	$content
</tbody>
</table>
<script>
$(document).ready(function(){
  	$("#CheckAll").click(function(){
   		if($("#CheckAll").prop("checked")){
    		$("input[class='courseid']").prop("checked",true);
   		}else{
    		$("input[class='courseid']").prop("checked",false);
   			}
  	})
})
function printpdf(id,status)
{
	var str = '';
	var cnt = 0;
	if($("#idnoshow").prop("checked")){
	var idnoshow = 1;
	}else{
	var idnoshow = 0;
	}
	$(".courseid:checked").each(function() {
		str = str+this.value+',';
		cnt++;
	});
	var cid = str.substr(0,(str.length-1));
	if(cnt > 1)
	{
	   	var URL = 'output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+0+'&yy={$prinfY}' ;
		window.open(URL, '', config='height=600,width=480');
	}
	else if(cnt == 1)
	{
		var URL = 'one_output.php?idnoshow='+idnoshow+'&cid='+cid+'&status='+status+'&yy={$prinfY}' ;
		window.open(URL, '', config='height=600,width=480');
	}
	else
	{
		alert('請勾選課程');
	}
}
</script>
EOF;
}





function showPage_fix_year($list, $uid, $ctype, $limit_start = 0 ,$search = '',$oldY=1911) {
	global $CFG, $DB;

	$year = intval(substr($oldY, 1, strlen($oldY)))+1911;

	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start;
	$thisConut = 0;
	$limit = $list;
	if($ctype==1) {
		$where_s = "and a.timecomplete > 0";
	}
	elseif($ctype==2) {
		$where_s = "and a.timecomplete is null";
	}
	else {
		$where_s = "";
	}

	if(!empty($search))
	{
		$where_s.= " and b.fullname like '%".$search."%'";
	}

	$sql = "SELECT count(1) cnt FROM {fet_course_history_$year} AS a
			LEFT JOIN {course} AS b ON a.courseid = b.id
			LEFT JOIN {context} mc on b.id=mc.instanceid AND mc.contextlevel=50
			JOIN {role_assignments} mr ON mr.contextid=mc.id
				AND mr.userid = a.userid
			LEFT JOIN {fet_cert_setting} AS c ON a.userid = c.uid
			WHERE a.userid = $uid
			AND b.format = 'topics' $where_s ";

	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	// if($type=='up') {
		// $html .=
			// "<table width='100%' border='0'>
			// <tbody>
			// <tr>
			// <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			// </tr>
			// </tbody></table>";
	// }
	$getUrl = $CFG->wwwroot.'/courserecord/index.php?cstatus='.$ctype;

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a id='".$ctype.','.'1'."' href='#' class='pageBtn' onclick='isChecked(this.id)'>第一頁</a>";
		$previousPageHtml = "<a id='".$ctype.','.($thisPage-1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a id='".$ctype.','.($thisPage+1)."' href='#' class='pageBtn' onclick='isChecked(this.id)'>下一頁</a>";
		$lastPageHtml = "<a id='".$ctype.','.(ceil($thisConut/$limit))."' href='#' class='pageBtn' onclick='isChecked(this.id)'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&pagenum='.($i+1));
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&pagenum='.($i+1);
			if($i+1==$thisPage) {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtnS' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a id='".$ctype.','.($i+1)."' href='#' class='cntBtn' onclick='isChecked(this.id)'>".($i+1)."</a>";
			}
		}
	}

echo <<<EOF
	<script>
	var link = "http://elearning.taipei/elearn/courserecord/index.php?cstatus=";
	var link_search = "http://elearning.taipei/elearn/courserecord/index.php?ssearch=";
	function isChecked(id){
		var id_Array = new Array();
		id_Array = id.split(",");
		location.href = link + $('#r2s2_OldY').val() + "&pagenum=" + id_Array[1] + "&oldY=$oldY&list=" + $('#data_count').val()  ;
	}
	function showList(obj){
		var cstatus = $('input[name=cstaus]:checked').val();
		location.href = link + $('#r2s2_OldY').val() + "&pagenum=1&oldY=$oldY" + "&list=" + obj.value  ;
		
	}
	</script>
EOF;
	$select_10 = '';
	$select_50 = '';
	if($list == '10'){
		$select_10 = "selected";
	} 

	if($list == '50'){
		$select_50 = "selected";
	} 

  	$html .=
	"<table width='100%'><tbody>
	<tr><td width='84%' style='text-align:center'>
	$firstPageHtml
	$previousPageHtml
	$ahref_html
	$nextPageHtml
	$lastPageHtml
	<select name='data_count' id='data_count' style='margin-bottom:3px;height:25px' onchange='showList(this)'>
		<option value='10'".$select_10.">每頁顯示10筆</option>
		<option value='50'".$select_50.">每頁顯示50筆</option>
	</select>
	</td>
	<td width='16%' style='text-align:right'>
		<input type='button' onclick='printpdf()' value='列印證明'>
	</td></tr>
	</tbody></table>";
	


	return $html;
}

function getQueryStringWithoutPage($page, $pageName='pagenum')
{
	$queryString = $_SERVER['QUERY_STRING'];
	parse_str($queryString, $queryArray);
	$queryArray[$pageName] = $page;

	$queryString = array();
	foreach ($queryArray as $key => $value){
		$queryString[] = $key."=".urlencode($value);
	}

	$queryString = implode("&", $queryString);
	return $queryString;
}

function get_course_total_hoursOldYear($uid, $year=null)
{
	global $DB;
	
	if ($year >= 2016 && $year < intVal(date('Y'))){
		$year = '_'.intVal($year);
	}else{
		$year = null;				
	}

	$sql = "select sum(history.certhour) hours, sum(CASE WHEN history.timecomplete is not null THEN history.certhour ELSE null END) completehours
			from mdl_fet_course_history$year history
			JOIN mdl_course$year course
			ON history.courseid=course.id
			JOIN mdl_fet_course_data$year data
			ON history.courseid=data.courseid			
			where history.userid = :uid";
	
	$result = $DB->get_record_sql($sql, array("uid"=>$uid));

	return $result;
}
