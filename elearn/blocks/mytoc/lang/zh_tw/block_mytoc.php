<?php
/**
 * My Table Of Content block tw language.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['mytoc:addinstance'] = '新增我的課程區塊';
$string['mytoc:myaddinstance'] = '在儀表板新增我的課程區塊';
$string['defaulttab'] = '預設分頁';
$string['defaulttab_desc'] = '第一次進入我的課程區塊時預設顯示的分頁.';
$string['pluginname'] = '我的課程';

$string['teachcourses'] = '授課課程';
$string['enrolcourses'] = '數位課程學習紀錄';
$string['phy_payment'] = '語言自費班期';
$string['phy_ohtercity'] = '研討/活動報名';
$string['phy_courses'] = '實體班期上課紀錄';
$string['enrolrole'] = '學生角色';
$string['enrolrole_desc'] = '在\'學習紀錄\'分頁內，要顯示此課程角色的選課課程清單.';
$string['nocourses'] = '無可顯示的資料.';
$string['nodata'] = '無可顯示的資料.';
$string['syncphyclasstask'] = '同步開放外縣市報名工作';
//other city class list
$string['year'] = '年度';
$string['class_no'] = '班期代碼';
$string['class_name'] = '班期名稱';
$string['term'] = '期別';
$string['course_date'] = '上課時間';
$string['apply_date'] = '報名時間';
$string['worker'] = '承辦人';
$string['action'] = '';
$string['enroll'] = '個人報名';
$string['unenroll'] = '取消報名';
$string['import_csv'] = '批次匯入';
$string['notify_not_open'] = '未開放';
$string['notify_only_servant'] = '限公務員報名';
$string['notify_notenrolled'] = '{$a} 未報名此班期無法退選.';
$string['notify_enrolled'] = '{$a} 已報名此班期.';
$string['notify_phy_not_exist'] = '您的帳號尚未註冊於實體班期系統, 第一次請使用\'批次匯入\'鈕進行班期報名.';
$string['enrollconfirm'] = '您確認要報名"{$a}"?';
$string['unenrollconfirm'] = '您確認要取消報名"{$a}"?';
$string['description'] = '<p><font size="3" color="red">如果您非實體班期學員時，第一次使用「個人報名」時，需填寫學員註冊資料.</font></p>
<p>如果您有任何問題，可聯繫客服人員：02-29320212轉341</p>';
$string['filter-class'] = '請選擇年度及班期名稱';
$string['templatefile'] = '範本檔下載';
$string['templatefile_help'] = '匯入範本檔請上傳CSV格式檔案，檔案的格式需遵循下列要求:<br/>
* 第一行為欄位名稱。<br/>
* 每一行只包含一筆紀錄。<br/>
* 每一筆紀錄是以逗點隔開的一系列資料。<br/>
* 欄位包含："報名狀態(必填)", "姓名(必填)", "身分證字號(必填)", "性別(必填)", "出生日期(必填)", Email, "公司Email(必填)", "機關代碼(必填)", "外機關名稱全銜", "學歷(必填)", "現職區分(必填)", "公司電話(必填)", "公司傳真", "職稱(必填)", "手機號碼"<br/>
* 報名狀態：1-加選, 0-退選；如此列資料報名狀態為<font color="red">"退選"</font>時，則僅需填寫"報名狀態(必填)","身分證字號(必填)"資料。<br/>
* 性別格式：M、F<br/>
* 日期格式：yyyy/mm/dd<br/>
* 機關代碼：<a href="https://svrorg.dgpa.gov.tw/cpacode/UC3/UC3-2/UC3-2-01-001.aspx" target="_blank"><font color="1d9d74">代碼查詢網址</font></a> (如為外機關請填：D0004)<br/>
* 外機關名稱全銜：查無機關代碼者必填本欄，例如"xx股份有限公司"<br/>
* 學歷請填代碼：20-國(初)中以下, 30-高中(職), 40-專科, 50-大學, 60-碩士, 70-博士<br/>
* 現職區分請填代碼：1-簡任主管, 2-簡任非主管, 3-荐任主管, 4-荐任非主管, 5-委任主管, 6-委任非主管, 7-警察消防主管, 8-警察消防非主管, 9-約聘僱人員, 10-技工工友, 11-其他<br/>
* 公司電話、公司傳真格式：02-12345678<br/>
* 手機號碼格式：0912-345-678<br/>
';
$string['encoding'] = '編碼';
$string['error_format'] = '未輸入資料或資料格式有誤. 欄位: {$a}';
$string['field_enrol'] = '報名狀態';
$string['field_firstname'] = '姓名';
$string['field_idno'] = '身分證字號';
$string['field_gender'] = '性別';
$string['field_birthday'] = '出生日期';
$string['field_email'] = 'e-mail';
$string['field_email2'] = '公司e-mail';
$string['field_bureau'] = '機關代碼';
$string['field_company'] = '外機關名稱全銜';
$string['field_edu'] = '學歷';
$string['field_type'] = '現職區分';
$string['field_tel'] = '公司電話';
$string['field_fax'] = '公司傳真';
$string['field_jobtitle'] = '職稱';
$string['field_cellphone'] = '手機號碼';
$string['field_status'] = '狀態';
$string['importresult'] = '批次匯入結果';
$string['import_total'] = '匯入總數：{$a}';
$string['import_success'] = '成功數：{$a}';
$string['import_errors'] = '錯誤數：{$a}';
$string['import_skipped'] = '忽略數：{$a}';
$string['import_enrol'] = '報名數：{$a}';
$string['import_unenrol'] = '取消報名數：{$a}';
$string['csvline'] = '行';
$string['enrol'] = '報名';
$string['unenrol'] = '取消報名';

$string['error_phy_user'] = '實體班期系統學生資料建立失敗.';
$string['create_eda_user'] = 'e大帳號建立成功.';
$string['create_phy_user'] = '實體班期系統學生資料建立成功.';
$string['success_enrol'] = '報名成功.';
$string['success_unenrol'] = '取消報名成功.';
$string['error_eda_user'] = 'e大帳號建立失敗,身分證或e-mail已被使用.';
$string['error_api_phy'] = '取得實體班期系統學生資料API異常.';
$string['error_enrol'] = '已報名過此課程.';
$string['error_eda_notexist'] = '此身分證不存在e大,無法取消報名.';
$string['error_unenrol'] = '未報名此班期,無法取消報名.';
$string['export_csv'] = '匯出';
$string['strfilename'] = '學員資料_';

$string['stdno'] = '學號';
$string['jobtitle'] = '職稱';
$string['company'] = '就職機關';
$string['company_enroll'] = '報名機關';
$string['room'] = '教室(課程表)';
$string['course_open'] = '開課日期';
$string['modify_upload'] = '異動表';
$string['certificate_upload'] = '證書下載';
$string['files_upload'] = '講義下載';
$string['print_schedule'] = '課程表';
$string['student_list'] = '名冊';
$string['filename_phycourses'] = '實體班期學習紀錄';
$string['register_phy'] = '學員資料註冊';
