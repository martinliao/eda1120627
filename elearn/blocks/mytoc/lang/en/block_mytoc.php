<?php
/**
 * My Table Of Content block english language.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['mytoc:addinstance'] = 'Add a new My Courses block';
$string['mytoc:myaddinstance'] = 'Add a new My Courses block to Dashboard';
$string['mytoc:manage'] = 'Manage My Courses block';
$string['defaulttab'] = 'Default tab';
$string['defaulttab_desc'] = 'The tab that will be displayed when a user first views their My Courses.';
$string['pluginname'] = 'My Courses';
$string['coursesperpage'] = 'Courses per page';
$string['coursesperpage_desc'] = 'The maximum number of courses to display in a course listing.';
$string['teachcourses'] = 'Teach Courses';
$string['enrolcourses'] = 'Enrol Courses';
$string['phy_payment'] = 'Payment';
$string['phy_ohtercity'] = 'PHY other city';
$string['phy_courses'] = 'PHY courses';
$string['enrolrole'] = 'Enrol Role';
$string['enrolrole_desc'] = 'In the \'Enrol Courses\' tab, display participating courses for select roles.';
$string['nocourses'] = 'No course information to show.';
$string['nodata'] = 'No data.';
$string['syncphyclasstask'] = 'Sync PHY class task';
//other city class list
$string['year'] = 'Year';
$string['class_no'] = 'Class no';
$string['class_name'] = 'Class name';
$string['term'] = 'Term';
$string['course_date'] = 'Course date';
$string['apply_date'] = 'Apply date';
$string['worker'] = 'Worker';
$string['action'] = 'Action';
$string['enroll'] = 'Enroll';
$string['unenroll'] = 'Unenrol';
$string['import_csv'] = 'Batch import';
$string['notify_not_open'] = 'Not open';
$string['notify_only_servant'] = 'Gov employee only';
$string['notify_notenrolled'] = '{$a} is not enrolled in this course.';
$string['notify_enrolled'] = '{$a} is enrolled in this course.';
$string['notify_phy_not_exist'] = 'You have not registered PHY, please use batch import mothod to enrol class.';
$string['enrollconfirm'] = 'Do you really want to enrol "{$a}"?';
$string['unenrollconfirm'] = 'Do you really want to unenrol "{$a}"?';
$string['description'] = 'If you have any questions, please contact customer service at +886-2-29320212#341';
$string['filter-class'] = 'Filter Class';
$string['templatefile'] = 'Template file download';
$string['templatefile_help'] = '匯入範本檔請上傳CSV格式檔案，檔案的格式需遵循下列要求:<br/>
* 第一行為欄位名稱。<br/>
* 每一行只包含一筆紀錄。<br/>
* 每一筆紀錄是以逗點隔開的一系列資料。<br/>
* 欄位包含："報名狀態(必填)", "姓名(必填)", "身分證字號(必填)", "性別(必填)", "出生日期(必填)", Email, "公司Email(必填)", "機關代碼(必填)", "私立機關名稱", "學歷(必填)", "現職區分(必填)", "公司電話(必填)", "公司傳真", "職稱(必填)", "手機號碼"<br/>
* 報名狀態：1-加選, 0-退選；如此列資料報名狀態為"退選"時，則僅須填寫"報名狀態(必填)","身分證字號(必填)"資料。<br/>
* 性別格式：M、F<br/>
* 日期格式：yyyy/mm/dd<br/>
* 機關代碼：代碼查詢網址 https://svrorg.dgpa.gov.tw/cpacode/UC3/UC3-2/UC3-2-01-001.aspx (如為外機關請填：D0004)<br/>
* 私立機關名稱：查無機關代碼者必填本欄，例如"xx股份有限公司"<br/>
* 學歷請填代碼：20-國(初)中以下, 30-高中(職), 40-專科, 50-大學, 60-碩士, 70-博士<br/>
* 現職區分請填代碼：1-簡任主管, 2-簡任非主管, 3-荐任主管, 4-荐任非主管, 5-委任主管, 6-委任非主管, 7-警察消防主管, 8-警察消防非主管, 9-約聘僱人員, 10-技工工友, 11-其他<br/>
* 公司電話、公司傳真格式：02-12345678<br/>
* 手機號碼格式：0912-345-678<br/>
';
$string['encoding'] = 'Encoding';
$string['error_format'] = 'The data is empty or format error. field = {$a}';
$string['field_enrol'] = '報名狀態';
$string['field_firstname'] = '姓名';
$string['field_idno'] = '身分證字號';
$string['field_gender'] = '性別';
$string['field_birthday'] = '出生日期';
$string['field_email'] = 'e-mail';
$string['field_email2'] = '公司e-mail';
$string['field_bureau'] = '機關代碼';
$string['field_company'] = '私立機關名稱';
$string['field_edu'] = '學歷';
$string['field_type'] = '現職區分';
$string['field_tel'] = '公司電話';
$string['field_fax'] = '公司傳真';
$string['field_jobtitle'] = '職稱';
$string['field_cellphone'] = '手機號碼';
$string['field_status'] = '狀態';
$string['importresult'] = 'Import resluts';
$string['import_total'] = 'total：{$a}';
$string['import_success'] = 'success：{$a}';
$string['import_errors'] = 'error：{$a}';
$string['import_skipped'] = 'skipped：{$a}';
$string['import_enrol'] = 'enrol：{$a}';
$string['import_unenrol'] = 'unenrol：{$a}';
$string['csvline'] = 'Line';
$string['enrol'] = 'enrol';
$string['unenrol'] = 'unenrol';
$string['create_eda_user'] = 'moodle user create success.';
$string['create_phy_user'] = 'PHY user create success.';
$string['success_enrol'] = 'Enrol success.';
$string['success_unenrol'] = 'Unenrol success.';
$string['error_eda_user'] = 'moodle user create error, the idno or e-mail already in use.';
$string['error_api_phy'] = 'Get PHY user API request error.';
$string['error_enrol'] = 'This idno is enrol for this class.';
$string['error_eda_notexist'] = 'This idno does not exist on eda.';
$string['error_unenrol'] = 'This idno is not enrol for this class.';
$string['export_csv'] = 'Export CSV';
$string['strfilename'] = 'StudentDetail_';

//phy courses
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
$string['register_phy'] = 'Register';
$string['register_description'] = '<span style="color:blue;font-weight:bold">※您在實體班期系統未建立帳號，請先填寫以下基本資料建立帳號，儲存後即可報名實體班期課程。<br/>
※若該學員服務於私立機關，請輸入私立機關名稱。<br/>
※若同時輸入私立機關名稱與局處名稱，將以私立機關名稱為主。<br/>
※星號欄位資料係介接WebHR人力資源管理系統。</span>
';
$string['bureau_help'] = '<a href="https://svrorg.dgpa.gov.tw/" target="_blank" class="btn btn-secondary">機關代碼查詢</a>';
$string['job_level_help'] = '<a href="{$a}" target="_blank" class="btn btn-secondary">現支官職等代碼查詢</a>';
$string['job_title_help'] = '<a href="{$a}" target="_blank" class="btn btn-secondary">職稱代碼查詢</a>';
$string['userfullname'] = '姓名';
$string['userename'] = '英文姓名';
$string['gender'] = '性別';
$string['male'] = '男';
$string['female'] = '女';
$string['idno'] = '身分證字號';
$string['birthday'] = '出生日期';
$string['office_email'] = '公司Email';
$string['email'] = '私人Email';
$string['co_empdb_poftel'] = '公司電話(格式：02-12345678)';
$string['cellphone'] = '手機號碼(格式：0912-345-678)';
$string['office_fax'] = '公司傳真(格式：02-12345678)';
$string['bureau_name'] = '局處名稱代碼(如為私立機關請填：D0004)';
$string['out_gov_name'] = '私立機關名稱(局處名稱代碼為D0004時必填本欄，例如”xx股份有限公司”)';
$string['education'] = '學歷';
$string['supervisor'] = '主管級別';
$string['job_level'] = '現支官職等(請輸入代碼)';
$string['job_title'] = '職稱(請輸入代碼)';
$string['job_distinguish'] = '現職區分';
$string['departure'] = '離職';
$string['retirement'] = '退休';
$string['showretirement'] = '不顯示退休狀態';
$string['missinguserfullname'] = '缺少姓名';
$string['missingofficeemail'] = '缺少公司Email';
$string['missingco_empdb_poftel'] = '缺少公司電話';
$string['missingbureau_name'] = '缺少局處名稱';
$string['missingjoblevel'] = '缺少現支官職等';
$string['missingjobtitle'] = '缺少職稱';
