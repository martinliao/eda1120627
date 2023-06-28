<?php

defined('MOODLE_INTERNAL') || die;

/* memo
 * 實體課程報名流程
 * status:
 * newCreate 報名(等待上傳檔案) time1
 * waitCheck 報名(等待審核) time2
 * auditFailure 審查失敗(資格不符) time0
 * waitPayment 等待付款 time3
 * Approval 審核通過 time4
 * */

/*
 * 取得所選擇實體班期資訊，可代入班期名稱、開課日期 或 空(預設找最新的課程)
 * @param start date 開始日期, format yyyymmdd
 * @param class_name 班期名稱, string
 * @param limit_start 顯示的起點, int
 * return list
 */
// function getPhyClassAll($start_date = "", $class_name = "", $limit_start = 0) {
	// global $USER, $DB;
	// $where_s = "";
	// if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		// $start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		// $where_s .= "and mt.START_DATE1 < '$start_date'";
	// }
	// if($class_name!="") {
		// $where_s .= "and mt.class_name like '%$class_name%'";
	// }
	// if($limit_start>0) {
		// $limit_start = $limit_start*10;
	// }
	// $sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mt.START_DATE1, md.issignup
			// from {fet_phy_require} mt
			// left join {fet_phy_require_data} md
			// on mt.id = md.rid
			// where 1=1 and mt.disable=0 $where_s
			// order by mt.START_DATE1, mt.term desc
			// limit $limit_start, 10";
	// $records = $DB->get_records_sql_ng($sql);
	// if($records) {
		// return $records;
	// }
	// else {
		// return null;
	// }
//
// }

/*
 * 取得所選擇實體班期資訊，可代入班期名稱、開課日期 或 空(預設找最新的課程) 只找 require_data.issignup = 1
 * @param start date 開始日期, format yyyymmdd
 * @param class_name 班期名稱, string
 * @param limit_start 顯示的起點, int
 * return list
 */
function getPhyClass($start_date = "", $class_name = "", $limit_start = 0) {
	global $USER, $DB;
	$where_s = "";
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}
	if($limit_start>0) {
		$limit_start = $limit_start*10;
	}
	$sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mp.fee, mp.status, mp.fee_type, mp.partner,
			mt.START_DATE1, mt.END_DATE1, mt.disable, mt.APPLY_S_DATE, mt.APPLY_E_DATE, mt.type, mp.id as cp_id, u.firstname
			from {fet_phy_require} mt
			left join {fet_phy_check_progress} mp
			on mt.id = mp.rid
			and mp.userid = $USER->id
			and mp.del=0			
			left join mdl_user u ON u.id = mp.userid
			where 1=1 and (mp.fee>0 or (now()>ADDTIME(mt.APPLY_S_DATE, '00:00:00') and now()<ADDTIME(mt.APPLY_E_DATE, '23:59:59')))
			$where_s
			order by mt.START_DATE1 desc, mt.term desc
			limit $limit_start, 10";

	$records = $DB->get_records_sql_ng($sql);
	if($records) {
		return $records;
	}
	else {
		return null;
	}

}

function getPhyPayType($type){
	global $USER, $DB;

	$sql = sprintf("select id from {fet_payment_manage} where phy_course_code = '%s'",$type);
	$id = $DB->get_records_sql_ng($sql);

	if(!empty($id)){
		$sql = sprintf("select * from {fet_payment_manage_detail} where fid = %s",$id[0]->id);
		$result = $DB->get_records_sql_ng($sql);
		// echo '<pre>';
		// print_r($result);
		// die();
		if(!empty($result)){
			return $result;
		} else {
			return false;
		}
		
	} else {
		return false;
	}
	

}

function getPhyPayTypeForold($type,$fee_type){
	global $USER, $DB;

	$sql = sprintf("select id from {fet_payment_manage} where phy_course_code = '%s'",$type);
	$id = $DB->get_records_sql_ng($sql);

	if(!empty($id)){
		$sql = sprintf("select * from {fet_payment_manage_detail} where fid = %s and cost_name = '%s' and cost_type = '舊生'",$id[0]->id,$fee_type);
		$result = $DB->get_records_sql_ng($sql);

		if(!empty($result)){
			return $result;
		} else {
			return false;
		}
		
	} else {
		return false;
	}
	

}

/* for status_class.php
 * 取得所選擇實體班期資訊，可代入班期名稱、開課日期 或 空(預設找最新的課程) where USER->id
 * @param start date 開始日期, format yyyymmdd
 * @param class_name 班期名稱, string
 * @param limit_start 顯示的起點, int
 * return list
 */
function getPhyClassStatus($start_date = "", $class_name = "", $limit_start = 0) {
	global $USER, $DB;
	$where_s = "";
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}
	if($limit_start>0) {
		$limit_start = $limit_start*10;
	}
	$uid = $USER->id;
	$sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mt.start_date1, mt.end_date1, mt.apply_s_date2, mt.apply_e_date2, mp.*,mp.id as cp_id, mt.ext_s_date, mt.ext_e_date, mt.ref_s_date, mt.ref_e_date
			from {fet_phy_require} mt
			left join {fet_phy_check_progress} mp
			on mt.id = mp.rid
				and mp.del=0
			where mp.userid=$uid $where_s
			order by mp.updatetime desc
			limit $limit_start, 10";
	$records = $DB->get_records_sql_ng($sql);
	if($records) {
		return $records;
	}
	else {
		return null;
	}

}

function getStudentReceipt($year,$serial_no,$receipt_no,$fee){
	global $DB;

	$sql = sprintf("select id from {fet_receipt_logs} where year = '%s' and serialNo = '%s' and receipt_no = '%s' and fee = '%s'", intval($year), addslashes($serial_no),addslashes($receipt_no),intval($fee));
	$records = $DB->get_records_sql_ng($sql);
	if($records) {
		return $records;
	}
	else {
		return null;
	}
}

/* for admin_class_status.php
 * 取得所選擇實體班期資訊，可代入班期名稱、開課日期 或 空(預設找最新的課程)
 * @param start date 開始日期, format yyyymmdd
 * @param class_name 班期名稱, string
 * @param limit_start 顯示的起點, int
 * return list
 */
function getAllUserClassStatus($start_date = "", $class_name = "", $limit_start = 0) {
	global $USER, $DB;
	$where_s = "";
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}
	if($limit_start>0) {
		$limit_start = $limit_start*10;
	}
	$uid = $USER->id;
	$sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mt.start_date1, mt.end_date1, mp.*,
			(select t.username from mdl_user t where t.id = mp.userid) username,
			(select t.firstname from mdl_user t where t.id = mp.userid) realname
			from {fet_phy_check_progress} mp
			join {fet_phy_require} mt
			on mt.id = mp.rid
				 and mp.del=0
			where 1=1 $where_s
			order by mp.updatetime desc
			limit $limit_start, 10";
	
	$records = $DB->get_records_sql_ng($sql);
	if($records) {
		return $records;
	}
	else {
		return null;
	}

}

/* for admin_class_status.php
 * 取得所選擇實體班期資訊，可代入班期名稱 年度 期別
 * @param class_name 班期名稱, string
 * @param limit_start 顯示的起點, int
 * @param f_Year 年度, int
 * @param f_Term 期別, int
 * return list
 */
function getAllUserClassStatusII($class_name = "", $limit_start = 0, $f_Year, $f_Term) {
    global $USER, $DB;
    $where_s = "";
    if($class_name!="") {
        $where_s .= "and mt.class_name like '%$class_name%'";
    }
    if($limit_start>0) {
        $limit_start = $limit_start*10;
    }
    $_Year = intval($f_Year)==0?-1:intval($f_Year);
    if($_Year>-1) {
        $where_s .= sprintf("and mt.YEAR=%d ", $_Year);
    }
    $_Term = intval($f_Term)==0?-1:intval($f_Term);
    if($_Term>-1) {
        $where_s .= sprintf("and mt.TERM=%d ", $_Term);
    }
    $uid = $USER->id;
    $sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mt.start_date1, mt.end_date1, mp.*,
            (select t.username from mdl_user t where t.id = mp.userid) username,
            (select t.firstname from mdl_user t where t.id = mp.userid) realname
            from {fet_phy_check_progress} mp
            join {fet_phy_require} mt
            on mt.id = mp.rid
            where 1=1 $where_s
            order by mp.updatetime desc
            limit $limit_start, 10";
    $records = $DB->get_records_sql_ng($sql);
    if($records) {
        return $records;
    }
    else {
        return null;
    }
}

/* for audit_management.php
 * 取得所有學員的申請紀錄，可代入班期名稱、開課日期 或 空(預設找最近更新)
 * @param start date 開始日期, format yyyymmdd
 * @param class_name 班期名稱, string
 * @param $code_number 繳費單號 string
 * @param limit_start 顯示的起點, int
 * return list
 */
function getPhyAutidProgress($order, $limit, $classname = "", $year = "", $studentname = "", $limit_start = 0,  $sel_type ="") {
	global $USER, $DB;
	$where_s = "";
	if(strlen($year)>0) {
		if(!preg_match('/^([0-9]+)$/', $year)) {
			return null;
		}
	}
	// if(strlen($start_date)==8 && preg_match('[a-zA-Z0-9]/', $start_date)) {
	// 	$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
	// 	$where_s .= "and mt.START_DATE1 < '$start_date'";
	// }
	if($classname!="") {
		$where_s .= "and mt.class_name like '%$classname%'";
	}
	if($year!="") {
		$where_s .= "and mt.year = '$year'";
	}
	if($studentname!="") {
		$where_s .= "and concat(ifnull(mu.lastname, ''), ifnull(mu.firstname,'')) like '%$studentname%'";
	}

	if($sel_type!="") {
		$status = '"'.$sel_type.'"';
	} else {
		$status = '"waitCheck", "auditFailure", "waitPayment", "Approval"';
	}

	if($order == 0){
		$where_o = "order by mp.updatetime desc";
	} elseif($order == 1){
		$where_o = "order by mt.term,mt.class_name";
	} elseif($order == 2){
		$where_o = "order by mt.term desc,mt.class_name desc";
	}

	if($limit_start>0) {
		$limit_start = $limit_start*$limit;
	}
	$sql = "select mt.id, mt.year, mt.class_no, mt.term, mt.class_name, mt.start_date1, mt.end_date1,mt.type,
			concat(ifnull(mu.lastname, ''), ifnull(mu.firstname,'')) username, mp.*, wp.id wp_id, wp.created_at
			from {fet_phy_check_progress} mp
			join {fet_phy_require} mt
			on mp.rid = mt.id
				and mp.del=0
			join mdl_user mu on mp.userid = mu.id
			left join {fet_phy_wisdom_pay} wp on wp.payment_no = mp.paymentNo
			where mp.`status` in ($status)
			$where_s
			$where_o
			limit $limit_start, $limit";
			
	$records = $DB->get_records_sql_ng($sql);

	if($records) {
		return $records;
	}
	else {
		return null;
	}

}

function getOldclassinfo($uid){
	global $USER, $DB;

	$sql = sprintf("select idno from mdl_fet_pid where uid = %s",$uid);
	$records = $DB->get_records_sql_ng($sql);
	
	if(!empty($records)){
		$sql = sprintf("SELECT
							a.year,
							a.term,
							a.class_name
						FROM
							mdl_fet_payment_old_student a
						JOIN (
							SELECT
								max(sel_date) AS m
							FROM
								mdl_fet_payment_old_student
							WHERE
								idno = '%s'
						) t ON a.sel_date = t.m
						WHERE
							idno = '%s'",$records[0]->idno,$records[0]->idno);
		$info = $DB->get_records_sql_ng($sql);
	}
	
	return $info;
}

/* for apply_class.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function showPageList($start_date = "", $class_name = "", $limit_start = 0, $type = 'up') {
	global $CFG, $DB, $USER;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start+1;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	$start_date_bak = $start_date=="0"?"":$start_date;
	//where 條件
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}

	$sql = "select count(1) cnt
			from {fet_phy_require} mt
			LEFT JOIN mdl_fet_phy_check_progress mp ON mt.id = mp.rid
				AND mp.userid = $USER->id
				and mp.del=0
			where 1=1 and (mp.fee>0 or (now()>ADDTIME(mt.APPLY_S_DATE, '00:00:00') and now()<ADDTIME(mt.APPLY_E_DATE, '23:59:59')))
            $where_s";

	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	if($type=='up') {
		$html .=
			"<table width='100%' border='0'>
			<tbody>
			<tr>
			<td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			</tr>
			</tbody></table>";
	}

	$getUrl = $CFG->wwwroot.'/fetpayment/apply_class.php?';
	if($class_name!="") {
		$getUrl .= "&sel_type=classname&sel_str=".$class_name;
	}
	else if($start_date!="0"&&$start_date!="") {
		$getUrl .= "&sel_type=startdate&sel_str=".$start_date_bak;
	}

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
		$previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
		$lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&p='.($i));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	$html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td></tr>
		<tr><td></td></tr>
		</tbody></table>";
	return $html;
}

/* for setting_class.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function showPageListAll($start_date = "", $class_name = "", $limit_start = 0, $type = 'up') {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start+1;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	$start_date_bak = $start_date=="0"?"":$start_date;
	//where 條件
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}

	$sql = "select count(1) cnt
			from {fet_phy_require} mt
			where 1=1 $where_s";
	$resource = $DB->get_record_sql($sql, array());
	if($resource) {
		$thisConut = $resource->cnt;
	}

	if($type=='up') {
		$html .=
			"<table width='100%' border='0'>
			<tbody>
			<tr>
			<td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			</tr>
			</tbody></table>";
	}

	$getUrl = $CFG->wwwroot.'/fetpayment/setting_class.php?';
	if($class_name!="") {
		$getUrl .= "&sel_type=classname&sel_str=".$class_name;
	}
	else if($start_date!="0"&&$start_date!="") {
		$getUrl .= "&sel_type=startdate&sel_str=".$start_date_bak;
	}

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
		$previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
		$lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&p='.($i));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	$html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td></tr>
		<tr><td></td></tr>
		</tbody></table>";
	return $html;
}

/* for status_class.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function showPageListStatus($start_date = "", $class_name = "", $limit_start = 0, $type = 'up') {
	global $CFG, $DB, $USER;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start+1;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	$start_date_bak = $start_date=="0"?"":$start_date;
	//where 條件
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}
	$uid = $USER->id;
	$sql = "select count(1) cnt
			from {fet_phy_require} mt
			join {fet_phy_check_progress} mp
			on mt.id = mp.rid
				 and mp.del=0
			where mp.userid=:uid $where_s";
	$resource = $DB->get_record_sql($sql, array('uid'=> $uid));
	if($resource) {
		$thisConut = $resource->cnt;
	}

	if($type=='up') {
		$html .=
			"<table width='100%' border='0'>
			<tbody>
			<tr>
			<td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			</tr>
			</tbody></table>";
	}

	$getUrl = $CFG->wwwroot.'/fetpayment/status_class.php?';
	if($class_name!="") {
		$getUrl .= "&sel_type=classname&sel_str=".$class_name;
	}
	else if($start_date!="0"&&$start_date!="") {
		$getUrl .= "&sel_type=startdate&sel_str=".$start_date_bak;
	}

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
		$previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
		$lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&p='.($i));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	$html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td></tr>
		<tr><td></td></tr>
		</tbody></table>";
	return $html;
}

/* for status_class.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function admin_showPageListStatus($start_date = "", $class_name = "", $limit_start = 0, $type = 'up') {
	global $CFG, $DB, $USER;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start+1;
	$thisConut = 0;
	$limit = 10;//每頁顯示筆數
	$start_date_bak = $start_date=="0"?"":$start_date;
	//where 條件
	if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
		$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
		$where_s .= "and mt.START_DATE1 < '$start_date'";
	}
	if($class_name!="") {
		$where_s .= "and mt.class_name like '%$class_name%'";
	}
	#$uid = $USER->id;
	$sql = "select count(1) cnt
			from {fet_phy_check_progress} mp
			join {fet_phy_require} mt
			on mt.id = mp.rid
			where 1=1 $where_s";

	$resource = $DB->get_record_sql($sql, array());

	if($resource) {
		$thisConut = $resource->cnt;
	}

	if($type=='up') {
		$html .=
			"<table width='100%' border='0'>
			<tbody>
			<tr>
			<td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			</tr>
			</tbody></table>";
	}

	$getUrl = $CFG->wwwroot.'/fetpayment/admin_class_status.php?';
	if($class_name!="") {
		$getUrl .= "&sel_type=classname&sel_str=".$class_name;
	}
	else if($start_date!="0"&&$start_date!="") {
		$getUrl .= "&sel_type=startdate&sel_str=".$start_date_bak;
	}

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
		$previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
		$lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&p='.($i));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	$html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td></tr>
		<tr><td></td></tr>
		</tbody></table>";
	return $html;
}

/* for status_class.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function admin_showPageListStatusII($class_name = "", $limit_start = 0, $f_Year, $f_Term, $type = 'up') {
    global $CFG, $DB, $USER;
    $where_s = $html = $ahref_html = "";
    $thisPage = $limit_start+1;
    $thisConut = 0;
    $limit = 10;//每頁顯示筆數
    //$start_date_bak = $start_date=="0"?"":$start_date;
    //where 條件
    if($class_name!="") {
        $where_s .= "and mt.class_name like '%$class_name%'";
    }
    $_Year = intval($f_Year)==0?-1:intval($f_Year);
    if($_Year>-1) {
        $where_s .= sprintf("and mt.YEAR=%d ", $_Year);
    }
    $_Term = intval($f_Term)==0?-1:intval($f_Term);
    if($_Term>-1) {
        $where_s .= sprintf("and mt.TERM=%d ", $_Term);
    }
    #$uid = $USER->id;
    $sql = "select count(1) cnt
            from {fet_phy_check_progress} mp
            join {fet_phy_require} mt
            on mt.id = mp.rid
            where 1=1 $where_s";
    $resource = $DB->get_record_sql($sql, array());
    if($resource) {
        $thisConut = $resource->cnt;
    }

    if($type=='up') {
        $html .=
            "<table width='100%' border='0'>
            <tbody>
            <tr>
            <td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
            </tr>
            </tbody></table>";
    }

    $getUrl = $CFG->wwwroot.'/fetpayment/admin_class_status.php?';
    if($class_name!="") {
        $getUrl .= "&sel_type=classname&sel_str=".$class_name;
    }
    if($_Year>-1) {
        $getUrl .= sprintf("&text_year=%d", $_Year);
    }
    $_Term = intval($f_Term)==0?-1:intval($f_Term);
    if($_Term>-1) {
        $getUrl .= sprintf("&text_term=%d", $_Term);
    }
    /*else if($start_date!="0"&&$start_date!="") {
        $getUrl .= "&sel_type=startdate&sel_str=".$start_date_bak;
    }*/

    if($thisPage==1) {
        $firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
        $previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
    }
    else {
        $firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
        $previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
    }
    if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
        $nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
        $lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
    }
    else {
        $nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
        $lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
    }
    if($thisPage<10) {//前10筆
        for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
            $sUrl = ($i==0?'':'&p='.($i));
            if($i+1==$thisPage) {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
            }
            else {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
            }
        }
    }
    elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
        for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
            $sUrl = '&p='.$i;
            if($i+1==$thisPage) {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
            }
            else {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
            }
        }
    }
    else {//前4後5
        for($i=$thisPage-5;$i<$thisPage+5;$i++) {
            $sUrl = '&p='.$i;
            if($i+1==$thisPage) {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
            }
            else {
                $ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
            }
        }
    }
    $html .=
        "<table width='100%'><tbody>
        <tr><td style='text-align:center'>
        $firstPageHtml
        $previousPageHtml
        $ahref_html
        $nextPageHtml
        $lastPageHtml
        </td></tr>
        <tr><td></td></tr>
        </tbody></table>";
    return $html;
}

/* for audit_management.php
 * 顯示目前分頁 & 總筆數
 * @param start date 開始日期, format yyyymmdd
 * return html
 */
function showPageListAudit($order, $limit ,$classname = "", $year = "", $studentname = "", $sel_type="", $limit_start = 0, $type = 'up') {
	global $CFG, $DB;
	$where_s = $html = $ahref_html = "";
	$thisPage = $limit_start+1;
	$thisConut = 0;
	// $limit = 10;//每頁顯示筆數
	// $start_date_bak = $start_date=="0"?"":$start_date;
	//where 條件
	// if(strlen($code_number)>0) {
	// 	if(!preg_match('/^([0-9A-Za-z]+)$/', $code_number)) {
	// 		$code_number = "";
	// 	}
	// }
	if(strlen($year)>0) {
		if(!preg_match('/^([0-9]+)$/', $year)) {
			return null;
		}
	}
	// if(strlen($start_date)==8 && preg_match('/[0-9]{8}/', $start_date)) {
	// 	$start_date = substr($start_date, 0, 4).'-'.substr($start_date, 4, 2).'-'.substr($start_date, 6, 2).' 00:00:00';
	// 	$where_s .= "and mt.START_DATE1 < '$start_date'";
	// }
	if($classname!="") {
		$where_s .= "and mt.class_name like '%$classname%'";
	}
	if($year!="") {
		$where_s .= "and mt.year = '$year'";
	}
	if($studentname!="") {
		$where_s .= "and concat(ifnull(mu.lastname, ''), ifnull(mu.firstname,'')) like '%$studentname%'";
	}

	if($sel_type!="") {
		$status = '"'.$sel_type.'"';
	} else {
		$status = '"waitCheck", "auditFailure", "waitPayment", "Approval"';
	}
	$sql = "select count(1) cnt
			from {fet_phy_check_progress} mp
			join {fet_phy_require} mt
			on mp.rid = mt.id
				and mp.del=0
			join mdl_user mu on mp.userid = mu.id
			where mp.`status` in ($status)
			$where_s ";

	$resource = $DB->get_record_sql($sql, array());
	
	if($resource) {
		$thisConut = $resource->cnt;
	}

	if($type=='up') {
		$html .=
			"<table width='100%' border='0'>
			<tbody>
			<tr>
			<td style='text-align:center'>第".$thisPage."頁/共".(ceil($thisConut/$limit))."頁(共".$thisConut."筆)</td>
			</tr>
			</tbody></table>";
	}

	$getUrl = $CFG->wwwroot.'/fetpayment/audit_management.php?number='.$limit;
	if($classname!="") {
		$getUrl .= "&classname=".$classname;
	}
	if($year!="") {
		$getUrl .= "&year=".$year;
	}
	if($studentname!="") {
		$getUrl .= "&studentname=".$studentname;
	}
	if($sel_type!="") {
		$getUrl .= "&sel_type=".$sel_type;
	}
	if($order!=0) {
		$getUrl .= "&order=".$order;
	}

	if($thisPage==1) {
		$firstPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>第一頁</span>";
		$previousPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>上一頁</span>";
	}
	else {
		$firstPageHtml = "<a href='$getUrl' class='pageBtn'>第一頁</a>";
		$previousPageHtml = "<a href='$getUrl&p=".($thisPage-2)."' class='pageBtn'>上一頁</a>";
	}
	if($thisPage==ceil($thisConut/$limit) || $thisConut==0) {
		$nextPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>下一頁</span>";
		$lastPageHtml = "<span class='pageBtn' style='color:silver' disabled=''>最末頁</span>";
	}
	else {
		$nextPageHtml = "<a href='$getUrl&p=".($thisPage)."' class='pageBtn'>下一頁</a>";
		$lastPageHtml = "<a href='$getUrl&p=".(ceil($thisConut/$limit)-1)."' class='pageBtn'>最末頁</a>";
	}
	if($thisPage<10) {//前10筆
		for($i=0;$i<ceil($thisConut/$limit)&&$i<10;$i++) {
			$sUrl = ($i==0?'':'&p='.($i));
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	elseif($thisPage+10>ceil($thisConut/$limit)) {//後10筆
		for($i=ceil($thisConut/$limit)-10;$i<ceil($thisConut/$limit);$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}
	else {//前4後5
		for($i=$thisPage-5;$i<$thisPage+5;$i++) {
			$sUrl = '&p='.$i;
			if($i+1==$thisPage) {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtnS'>".($i+1)."</a>";
			}
			else {
				$ahref_html .= "<a href='$getUrl$sUrl' class='cntBtn'>".($i+1)."</a>";
			}
		}
	}

	if($type=='up') {
		if($limit == 10){
			$s1 = "selected='selected'";
		} elseif($limit == 20){
			$s2 = "selected='selected'";
		} elseif($limit == 30){
			$s3 = "selected='selected'";
		}		
	 	 $html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td><td>每頁顯示<select id='number' name='number' onchange='showPageNumber()'><option value='10' $s1>10</option><option value='20' $s2>20</option><option value='30' $s3>30</option></select>筆</td></tr>
		<tr><td></td></tr>
		</tbody></table></form>";
	} else {
		$html .=
		"<table width='100%'><tbody>
		<tr><td style='text-align:center'>
		$firstPageHtml
		$previousPageHtml
		$ahref_html
		$nextPageHtml
		$lastPageHtml
		</td></tr>
		<tr><td></td></tr>
		</tbody></table></form>";
	}

	// $html .=
	// 	"<table width='100%'><tbody>
	// 	<tr><td style='text-align:center'>
	// 	$firstPageHtml
	// 	$previousPageHtml
	// 	$ahref_html
	// 	$nextPageHtml
	// 	$lastPageHtml
	// 	</td></tr>
	// 	<tr><td></td></tr>
	// 	</tbody></table>";
	return $html;
}
/* for output.php
 * 列印繳費單、會計單據
 * @param id , int
 * return list or null
 */
 function getProgressData($id, $uid) {
 	global $CFG, $DB;
 	$sql = "select mp.userid, concat(mu.lastname, mu.firstname) username,
				concat(mr.class_name, '(', mr.year, '年第', mr.term, '期)') class,
				mp.time3, mp.fee, mp.paymentNo, mu.email
			from {fet_phy_check_progress} mp
			join {fet_phy_require} mr
			on mp.rid = mr.id
				and mp.del=0
			join {user} mu
				on mu.id = mp.userid
			where rid=:rid and status=:status
				and userid=:userid ";
	try {
		$data = $DB->get_record_sql($sql, array('rid'=>$id, 'userid'=>$uid, 'status'=>'waitPayment'));
		if($data) {
			return $data;
		}
		return null;
	}
	catch(Exception $e) {
		error_log("from elearn/fetpayment/lib.php function getProgressData :".$e);
	}
 }
/* for stamp_mng.php
 * 圖章管理資訊 mdl_fet_upload_files where type = 'stamp_set'
 * return list or null
 */
 function getStampData() {
 	global $CFG, $DB;
 	$sql = "select mf.fname, mf.fpath from {fet_upload_files} mf
			where mf.type = :type";
	try {
		$data = $DB->get_records_sql_ng($sql, array('type'=>'stamp_set'));
		if($data) {
			return $data;
		}
		return null;
	}
	catch(Exception $e) {
		error_log("from elearn/fetpayment/lib.php function getStampData :".$e);
	}
 }
 /* for output.php
 * 數字轉大寫
 * @param $money , int
 * return string
 */
 function getChineseNumber($money) {
	$ar = array("零", "壹", "貳", "參", "肆", "伍", "陸", "柒", "捌", "玖") ;
	$cName = array("", "", "拾", "佰", "仟", "萬", "拾", "佰", "仟", "億", "拾", "佰", "仟");
	$conver = "";
	$cLast = "" ;
	$cZero = 0;
	$i = 0;
	for ($j = strlen($money) ; $j >=1 ; $j--) {
		$cNum = intval(substr($money, $i, 1));
		$cunit = $cName[$j]; //取出位數
		if ($cNum == 0) { //判斷取出的數字是否為0,如果是0,則記錄共有幾0
			$cZero++;
			if (strpos($cunit,"萬億") >0 && ($cLast == "")) { // '如果取出的是萬,億,則位數以萬億來補
				$cLast = $cunit ;
			}
		}
		else {
			if ($cZero > 0) {// '如果取出的數字0有n個,則以零代替所有的0
				if (strpos("萬億", substr($conver, strlen($conver)-2)) >0) {
				$conver .= $cLast; //'如果最後一位不是億,萬,則最後一位補上"億萬"
				}
				$conver .=  "零" ;
				$cZero = 0;
				$cLast = "" ;
			}
			$conver = $conver.$ar[$cNum].$cunit; // '如果取出的數字沒有0,則是中文數字+單位
		}
		$i++;
	}
	//'判斷數字的最後一位是否為0,如果最後一位為0,則把萬億補上
	if (strpos("萬億", substr($conver, strlen($conver)-2)) >0) {
		$conver .=$cLast; // '如果最後一位不是億,萬,則最後一位補上"億萬"
	}
	return $conver;
}

 /**
 * 查詢舊繳費紀錄
 * @param $userid , int
 * @param $paymode , (0:after , 1 before ) int
 * return list or null
 */
function getOldList($uid, $y1 = 0, $y2 = 0) { //$paymode = 0(after), 1(before)
	global $CFG, $DB;
	$uu = "";
	if($y2==0) { //查after use USERNAME
		$uu = $uid;
	}
	else { //查brfore use PERSONAL ID
		$sql = "select mp.idno from mdl_fet_pid mp where mp.id = :id ";
		$data = $DB->get_record_sql($sql, array('id'=>$uid));
		if($data) {
			if(!empty($data->idno)) {
				$uu = $data->idno;
			}
		}
	}
 	require_once ("../../getData.php");
	$cls = new getData();
	$data_list = $cls->dataQuery($uu, $y1, $y2);
	if(count($data_list)>0) {
		return $data_list;
	}
	else {
		return null;
	}
}

/**
 * 產生課程清單 for setting_class.php
 * @return list
 */
function fn_getSelectList() {
	global $USER, $DB;
	$sql = "select mfpr.YEAR, mfpr.CLASS_NO, mfpr.CLASS_NAME
			from {fet_phy_require} mfpr
			join {fet_phy_check_progress} mfpcp
			on mfpr.id = mfpcp.rid

			group by mfpr.YEAR, mfpr.CLASS_NO, mfpr.CLASS_NAME
			order by mfpr.YEAR, mfpr.CLASS_NO";//where mfpcp.status = 'Approval'
	$records = $DB->get_records_sql_ng($sql);
	if($records) {
		return $records;
	}
	else {
		return null;
	}
}

/**
 * 產生繳費學員資料for setting_class.php
 * @param $year int
 * @param $classNo string
 * @param $term int
 * @return array or null
 */
function fn_AlreadyPaid($year, $classNo, $term) {
	global $USER, $DB;
	$sql = "select mfpcp.userid,mfpcp.status,mfpu.food_type,mfpu.email as cemail,mfpu.ename,mfpu.phone, mu.firstname realname,mfpcp.del,
				(select v.idno from mdl_fet_pid v where v.uid = mfpcp.userid) idno,
				(select b.data from mdl_user a
					join mdl_user_info_data b
					on a.id = b.userid
					where a.id = mfpcp.userid
						and b.fieldid = 12) gender,
				(select b.data from mdl_user a
					join mdl_user_info_data b
					on a.id = b.userid
					where a.id = mfpcp.userid
						and b.fieldid = 7) birthday,
				mu.email, mu.email,
				(select b.data from mdl_user a
					join mdl_user_info_data b
					on a.id = b.userid
					where a.id = mfpcp.userid
						and b.fieldid = 18) jobtitle,
				(select b.data from mdl_user a
					join mdl_user_info_data b
					on a.id = b.userid
					where a.id = mfpcp.userid
						and b.fieldid = 14) govid
			from mdl_fet_phy_check_progress mfpcp
			join mdl_user mu on mfpcp.userid = mu.id
			LEFT JOIN mdl_fet_phy_userinfo mfpu ON mfpu.userid = mfpcp.userid AND mfpcp.rid = mfpu.rid
			where mfpcp.rid =
				(select tmp.id from mdl_fet_phy_require tmp
					where tmp.year = :year
						and tmp.CLASS_NO = :class_no
						and tmp.TERM = :term) ";//and mfpcp.status = 'Approval'
	$records = $DB->get_records_sql_ng($sql, array("year"=> $year, "class_no"=> $classNo, "term"=> $term));
	if($records) {
		return $records;
	}
	else {
		return null;
	}
}

function checkUserIdentify($uid){
	global $USER, $DB;

	$sql = sprintf("select data from mdl_user_info_data where fieldid = 19 and userid = %s",$uid);

	$info = $DB->get_records_sql_ng($sql);

	if($info) {
		return $info;
	}
	else {
		return null;
	}
}

function checkUserIsOldStudent($uid,$year){
	global $USER, $DB;

	$sql = sprintf("select idno from mdl_fet_pid where uid = %s",$uid);
	$idno = $DB->get_records_sql_ng($sql);

	$y1 = $year - 1;
	$y2 = $year;
	
	if(!empty($idno)){
		$sql = sprintf("SELECT count(1) cnt FROM mdl_fet_payment_old_student WHERE idno = '%s' and year in (%s,%s)",$idno[0]->idno,$y1,$y2);
		$result = $DB->get_records_sql_ng($sql);
	}

	if($result){
		return $result;
	} else {
		return null;
	}
}

function getClassType($year,$class_no,$term){
	global $DB;

	$sql = sprintf("SELECT type FROM mdl_fet_phy_require WHERE year = %s AND class_no = '%s' AND term = %s",$year,$class_no,$term);
	$type = $DB->get_records_sql_ng($sql);

	if($type){
		return $type;
	} else {
		return null;
	}

}

function getPayDetail($id){
	global $DB;

	$sql = sprintf("SELECT * FROM mdl_fet_payment_manage_detail WHERE id = '%s'",$id);
	$detail = $DB->get_records_sql_ng($sql);

	if($detail){
		return $detail;
	} else {
		return null;
	}
}

function updPayType($mode,$id,$cost,$cost_type,$other_info){
	global $DB;

	if($mode == 'old'){
		$sql = sprintf("UPDATE mdl_fet_phy_check_progress SET fee = '%s',fee_type = '%s',old_student = '%s',partner = '' WHERE id = '%s'",$cost,$cost_type,$other_info,$id);

		if($DB->execute($sql)){
			return true;
		}
	} else if($mode == 'partner'){
		$sql = sprintf("UPDATE mdl_fet_phy_check_progress SET fee = '%s',fee_type = '%s',partner = '%s',old_student = '' WHERE id = '%s'",$cost,$cost_type,$other_info,$id);

		if($DB->execute($sql)){
			return true;
		}
	} else if($mode == 'normal'){
		$sql = sprintf("UPDATE mdl_fet_phy_check_progress SET fee = '%s',fee_type = '%s',old_student = '',partner = '' WHERE id = '%s'",$cost,$cost_type,$id);

		if($DB->execute($sql)){
			return true;
		}
	}

	return false;

}
//CO_CLASS_PAYMENT 201106之後繳費紀錄  DB:edaold
// select a.username, a.co_approve_date, a.del_username, a.pay_course as id, a.pay_course, a.amount, a.apply_date, a.pay_way, a.is_pay, a.pay_unit, a.co_majorkey,
	// a.pay_status, b.departmentname, c.pay_id, c.pay_way as payway, c.cnt, a.co_price_comment, ifnull(c.period_end, d.period_end) as period_end,
	// case when DATE_FORMAT(NOW(),'%Y%m%d') > DATE_FORMAT(ifnull(c.period_end, d.period_end),'%Y%m%d') then 'Y' else 'N' end as period_over
// from CO_CLASS_PAYMENT a
// left join CO_UNIT_INFO b on a.pay_unit = b.pay_unit
// left join (
// select a1.username, a1.pay_id, a1.pay_way, min(a2.period_end) as period_end, count(*) as cnt from CO_PAYMENT a1
// left join WM_TERM_COURSE a2 on a1.pay_course = a2.course_id
// where a1.result is null group by a1.username, a1.pay_id, a1.pay_way
// ) c on a.username = c.username and a.pay_id = c.pay_id
// left join WM_TERM_COURSE d on a.pay_course = d.course_id
// where a.username = '$username' order by a.is_pay, a.apply_date desc;

//CO_PAYMENT_HISTORY 201106之前繳費紀錄  DB:edaold

// 建立email 寄送用繳費單
function createPaymentPDF($rid, $uid, $output_type){

	global $CFG;
	require_once($CFG->dirroot.'/functions.php');
	require_once($CFG->dirroot.'/lib/tcpdf/tcpdf.php');	

	$data = getProgressData($rid, $uid);

	if($data) {
		$username = $data->username;
		$classname = $data->class;
		$start_date = $data->time3;
		$fee = $data->fee;
		$paymentNo = $data->paymentno;
		//$email = getPhyApplyEmail($uid, $rid)->email;
	}else{
		return false;
	}

	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetMargins(0,0,0,true);	
	//隱藏頁首 頁尾
	$pdf->SetPrintHeader(false); 
	$pdf->SetPrintFooter(false);
	
	//add a page
	$pdf->AddPage();
	$end_date = 60*60*24*7+$start_date;
	$sdate = $start_date;
	$edate = $end_date;
	$start_date = gmdate("Y-m-d", $start_date);
	$end_date = gmdate("Y-m-d", $end_date);
	
	$Y = gmdate("Y", $sdate);
	$m = gmdate("m", $sdate);
	$d = gmdate("d", $sdate);
	$Y1 = gmdate("Y", $edate);
	$m1 = gmdate("m", $edate);
	$d1 = gmdate("d", $edate);

	$code1 = substr(($Y1-1911).$m1.$d1, 1)."6FA";
	$code2 = $paymentNo;

	$fee_ = $fee;
	for ($i=strlen($fee); $i < 9 ; $i++) { 
		$fee_ = "0".$fee_;
	}
	$code3 = substr($end_date, 5, 2)."".substr($end_date, 8, 2)."00".$fee_;

	if(strlen($code1)!=9) {
		return false;
	}
	if(strlen($code2)!=16) {
		return false;
	}
	if(strlen($code3)!=15) {
		return false;
	}
	$c4 = intval($code1[0])+intval($code1[2])+intval($code1[4])+intval($code1[6])+1//code1 A=1
		+intval($code2[0])+intval($code2[2])+intval($code2[4])+intval($code2[6])+intval($code2[8])//cade2-1
		+intval($code2[10])+intval($code2[12])+intval($code2[14])//cade2-2
		+intval($code3[0])+intval($code3[2])+intval($code3[6])+intval($code3[8])+intval($code3[10])//cade3-1
		+intval($code3[12])+intval($code3[14]);//cade3-2
	$c4 = ($c4%11);
	if($c4==0) {
		$c4 = "A";
	}
	elseif($c4==10) {
		$c4 = "B";
	}
	$c5 = intval($code1[1])+intval($code1[3])+intval($code1[5])+6//code1 F=6
		+intval($code2[1])+intval($code2[3])+intval($code2[5])+intval($code2[7])+intval($code2[9])//cade2-1
		+intval($code2[11])+intval($code2[13])+intval($code2[15])//cade2-2
		+intval($code3[1])+intval($code3[3])+intval($code3[7])+intval($code3[9])//cade3-1
		+intval($code3[11])+intval($code3[13]);//cade3-2
	$c5 = ($c5%11);
	if($c5==0) {
		$c5 = "X";
	}
	elseif($c5==10) {
		$c5 = "Y";
	}
	$code3[4]=$c4;
	$code3[5]=$c5;

	//智慧支付QR code
	/* 2021-05-26 取消智慧支付
	$responseString = '18'.$paymentNo;
   	$checkCode = hash('sha256',$responseString);
   	$checkCode = substr($checkCode, 0, 4);
   	$qr_url = 'https://pay.taipei/qr/18/'.$paymentNo.'/'.$checkCode;
   	$rectangle = '170'."x".'170';
   	$qr_pay_url = "http://chart.googleapis.com/chart?chs=".$rectangle."&cht=qr&chl=".$qr_url."&choe=UTF-8";
    */
   	// 主畫面
   	
		$pdf->Image("http://elearning.taipei/elearn/fetpayment/main.jpg",6 , 0, 0, 0, '', '', '', false, 300, '', false, false, 0);
		//$pdf->Image("http://elearning.taipei/elearn/fetpayment/main_new.jpg",6 , 0, 0, 0, '', '', '', false, 300, '', false, false, 0);	
		//$pdf->Image("http://elearning.taipei/elearn/fetpayment/main.jpg",6 , 0, 0, 0, '', '', '', false, 300, '', false, false, 0);
	

	// 銀行條碼專用區 單據條碼
	$pdf->Image("http://elearning.taipei/api/barcode.php?barcode=$code2&width=430&height=100", 22, 255, 60, 13, '', '', '', false, 300, '', false, false, 0);

	// 超商代收專用區
	$pdf->Image("http://elearning.taipei/api/barcode.php?barcode=$code1&width=430&height=100", 142, 223, 42, 12, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image("http://elearning.taipei/api/barcode.php?barcode=$code2&width=430&height=100", 132, 236, 65, 12, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image("http://elearning.taipei/api/barcode.php?barcode=$code3&width=430&height=100", 132, 250, 60, 12, '', '', '', false, 300, '', false, false, 0);

	// 智慧支付 QR code  //2021-05-26 取消智慧支付
	//$pdf->Image($qr_pay_url, 157, 163.5, 40, 40, '', '', '', false, 300, '', false, false, 0);
	/**
	 * 2023-06 重新顯示智慧支付QR code
	 * https://pay.taipei/qr/{id}/{custom_id}/{qr_check}/{type}
	 * {type} = 2, 為預設值，不需代入
	 */
	
	$ContentString = '18'.$paymentNo;
	$checkCode = hash('sha256', $ContentString);
   	$qr_check  = strtolower(substr($checkCode, 0, 4));//2 Byte
	$qr_url = 'https://pay.taipei/qr/18/'.$paymentNo.'/'.$qr_check;
	$qr_pay_url = "http://chart.googleapis.com/chart?chs=170x170&cht=qr&chl=".$qr_url.'&chld=L|1';
	$pdf->Image($qr_pay_url, 160, 138, 25, 0, 'PNG');

	// 繳費單號
	$pdf->writeHTMLCell(80, 30, 125.5, 131, '<div style="font-size:11px;">'.$code2.'</div>', 0, 0, 0, 0, 'C', false);

	//  報名資訊 
	$pdf->writeHTMLCell(80, 30, 35, 125.5, $username, 0, 0, 0, 0, 'L', false);
	// $pdf->writeHTMLCell(80, 25, 25, 149, $email, 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(200, 15, 35, 146, $classname, 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(120, 15, 35, 152.5, $start_date, 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(120, 15, 22, 152.5, $end_date, 0, 0, 0, 0, 'R', false);
	$pdf->writeHTMLCell(120, 15, -10, 168, $fee, 0, 0, 0, 0, 'R', false);
	$pdf->writeHTMLCell(120, 15, -5.5, 182.5, $fee, 0, 0, 0, 0, 'R', false);
	$pdf->writeHTMLCell(80, 15, 43, 203, $username, 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(80, 15, 43, 216, "21121", 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(80, 15, 43, 223, "190210 (現金收入)", 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(80, 15, 43, 228, "190220 (轉帳收入)", 0, 0, 0, 0, 'L', false);
	$pdf->writeHTMLCell(120, 15, 63, 210, $fee, 0, 0, 0, 0, 'L', false);

	//D for download, I for print file, F for save 
	if ($output_type == "F") {
		$pdf_file_name = "/www/userupload/".$rid.$uid.".pdf";
		$pdf->Output($pdf_file_name, "F"); 
		return $pdf_file_name;		
	}else if ($output_type == "I"){
		$pdf->Output("payment.pdf", "I"); 
	}
}

?>