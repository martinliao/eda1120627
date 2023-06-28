<?php
/**
 * CSV Import users enrollment PHY class for My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require_once($CFG->dirroot.'/blocks/mytoc/locallib.php');
require_once($CFG->dirroot.'/courserecord/lib.php');

$iid = optional_param('iid', '', PARAM_INT);
$courseid = required_param('cid', PARAM_INT);

require_login();
$context = context_course::instance(SITEID);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/blocks/mytoc/upload.php', array());
$PAGE->set_title(get_string('import_csv','block_mytoc'));

$PAGE->navbar->add(get_string('import_csv','block_mytoc'));

if (empty($iid)) {
    //$args = array('data' => block_mytoc_get_phy_class());
    $args = array('courseid' => $courseid);
    $mform1 = new block_mytoc_upload_form(null, $args);
    
    if ($formdata = $mform1->get_data()) {

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('importresult', 'block_mytoc'));
        // create an import id
        $iid = csv_import_reader::get_new_iid('uploadphyenrol');
        $cir = new csv_import_reader($iid, 'uploadphyenrol');
        // Get content of uploaded file from filepicker
        $content = $mform1->get_file_content('csvfile');
        // 取得csv檔案內容, 並給予編碼與分隔符資訊
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        $cir->init();

        $upt = new block_mytoc_tracker(1);
        $upt->start();
        // We might need extra time and memory depending on the number of rows to preview.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        $linenum = 0;
        $errors = $skipped = $enrol = $unenrol = 0;
        $courseid = $formdata->cid;
        $batchtime = time();

        $userinfo = get_user_info($USER->id);
        while ($row = $cir->next()) {
            $linenum++;
            
            $enroltype = "";
            if($row[0] == 1){ $enroltype = get_string('enrol', 'block_mytoc'); }
            else if($row[0] == 0){ $enroltype = get_string('unenrol', 'block_mytoc'); }
            $row[2] = $idno = strtoupper(trim($row[2]));
            $row[3] = $gender = strtoupper($row[3]);
            $remark = implode(',', $row);
            
            /*
            $upt->track('enrol', $enrol);//報名狀態
            $upt->track('firstname', s($row[1]));//姓名
            $upt->track('idno', $row[2]);//身分證字號
            $upt->track('gender', $gender);//性別
            $upt->track('birthday', $row[4]);//出生日期
            $upt->track('email', $row[5]);//Email
            $upt->track('email2', $row[6]);//公司Email
            $upt->track('bureau', $row[7]);//機關代碼
            $upt->track('company', $row[8]);//外機關名稱全銜
            $upt->track('edu', $row[9]);//學歷
            $upt->track('type', $row[10]);//現職區分
            $upt->track('tel', $row[11]);//公司電話
            $upt->track('fax', $row[12]);//公司傳真
            $upt->track('jobtitle', $row[13]);//職稱
            $upt->track('cellphone', $row[14]);//手機號碼
            $upt->track('result', '');

            if(empty($enrol)){
                $upt->track('status', get_string('error_format', 'block_mytoc', get_string('field_enrol', 'block_mytoc')), 'error');
                $errors++;
                continue;
            }
            
            if(empty($idno) OR !block_mytoc_check_format('idno', $idno)){
                $upt->track('idno', get_string('error_format', 'block_mytoc', get_string('field_idno', 'block_mytoc')), 'error');
                $errors++;
                continue;                
            }

            if($row[0] == 1){
                if(empty($row[1])){
                    $upt->track('firstname', get_string('error_format', 'block_mytoc', get_string('field_firstname', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }

                if(empty($row[3]) OR !block_mytoc_check_format('gender', $gender)){
                    $upt->track('gender', get_string('error_format', 'block_mytoc', get_string('field_gender', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
                if(empty($row[4]) OR !block_mytoc_check_format('date', $row[4])){
                    $upt->track('birthday', get_string('error_format', 'block_mytoc', get_string('field_birthday', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }

                if(empty($row[6]) OR !filter_var($row[6], FILTER_VALIDATE_EMAIL)){
                    $upt->track('email2', get_string('error_format', 'block_mytoc', get_string('field_email2', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }

                if(empty($row[8]) && ($row[7] == "D0004")){
                    $upt->track('company', get_string('error_format', 'block_mytoc', get_string('field_company', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }else if (!empty($row[7]) && !block_mytoc_check_format('bureau', $row[7])){
                    $upt->track('bureau', get_string('error_format', 'block_mytoc', get_string('field_bureau', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
                
                if(empty($row[9]) OR !block_mytoc_check_format('edu', $row[9])){
                    $upt->track('edu', get_string('error_format', 'block_mytoc', get_string('field_edu', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
                if(empty($row[10]) OR !block_mytoc_check_format('type', $row[10])){
                    $upt->track('type', get_string('error_format', 'block_mytoc', get_string('field_type', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
                if(empty($row[11]) OR !block_mytoc_check_format('tel', $row[11])){
                    $upt->track('tel', get_string('error_format', 'block_mytoc', get_string('field_tel', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
                if(empty($row[13])){
                    $upt->track('jobtitle', get_string('error_format', 'block_mytoc', get_string('field_jobtitle', 'block_mytoc')), 'error');
                    $errors++;
                    continue;
                }
            }
            
            if(!empty($row[5]) AND !filter_var($row[5], FILTER_VALIDATE_EMAIL)){
                $upt->track('email', get_string('error_format', 'block_mytoc', get_string('field_email', 'block_mytoc')), 'error');
                $errors++;
                continue;
            }
            
            if(!empty($row[12]) AND !block_mytoc_check_format('fax', $row[12])){
                $upt->track('tel', get_string('error_format', 'block_mytoc', get_string('field_tel', 'block_mytoc')), 'error');
                $errors++;
                continue;
            }
            if(!empty($row[14]) AND !block_mytoc_check_format('cellphone', $row[14])){
                $upt->track('cellphone', get_string('error_format', 'block_mytoc', get_string('field_cellphone', 'block_mytoc')), 'error');
                $errors++;
                continue;
            }
            */

            $status = "";
            $data = array();
            $data['idno'] = $idno;
            $data['firstname'] = s($row[1]);
            $data['enrol'] = $enroltype;
            
            $status .= block_mytoc_import_verify($enroltype, $row);
            if(!empty($status)){
                $upt->output($linenum, false, $status, $data);
                $errors++;
                block_mytoc_error_log($batchtime, $idno, $courseid, $remark, $status);
                continue;
            }

            if($row[0] == 1) {
                //check and create eda account(mdl_fet_pid & mdl_temporary_user)
                if(!$user_id = block_mytoc_get_user_by_idno($idno)){
                    if(!$user_id = block_mytoc_create_user($row)){
                        $status .= get_string('error_eda_user', 'block_mytoc');
                        $upt->output($linenum, false, $status, $data);
                        $errors++;
                        block_mytoc_error_log($batchtime, $idno, $courseid, $remark, $status);
                        continue;
                    }
                    $status .= get_string('create_eda_user', 'block_mytoc');
                }
                
                //check and create phy account(BS_user)
                $phy_exist = block_mytoc_get_phy_user($row, $userinfo->idno);
                
                if($phy_exist != 'TRUE' && $phy_exist != 'CREATE_TRUE'){
                    $status .= get_string('error_api_phy', 'block_mytoc');
                    $upt->output($linenum, false, $status, $data);
                    $errors++;
                    block_mytoc_error_log($batchtime, $idno, $courseid, $remark, $status);
                    continue;
                }
                else if ($phy_exist == 'CREATE_TRUE'){
                    $status .= get_string('create_phy_user', 'block_mytoc');
                }

                $is_enrol = $DB->record_exists('fet_phy_other_city_enrol', array('oc_id' => $courseid, 'uid' => $user_id));
                if(!$is_enrol){
                    //to enrol
                    $tmp              = new stdClass();
                    $tmp->oc_id       = $courseid;
                    $tmp->uid         = $user_id;
                    $tmp->idno        = $idno;
                    $tmp->usercreated = $USER->id;
                    $tmp->timecreated = $batchtime;
                    $DB->insert_record('fet_phy_other_city_enrol', $tmp, false);
                    block_mytoc_sync_phy_enrol('enrol', $userinfo->idno, $userinfo->ecpa, $courseid, $idno, $row[7]);
                    
                    $tmp->status = 1;
                    $tmp->remark = $remark;
                    $DB->insert_record('fet_phy_other_city_log', $tmp);
                    
                    $status .= get_string('success_enrol', 'block_mytoc');
                    $upt->output($linenum, true, $status, $data);
                    $enrol++;
                }
                else {
                    $status .= get_string('error_enrol', 'block_mytoc');
                    $upt->output($linenum, true, $status, $data);
                    $skipped++;
                }
            }
            else {
                
                if(!$user_id = block_mytoc_get_user_by_idno($idno)){
                    $status .= get_string('error_eda_notexist', 'block_mytoc');
                    $upt->output($linenum, false, $status, $data);
                    $errors++;
                    block_mytoc_error_log($batchtime, $idno, $courseid, $remark, $status);
                    continue;
                }

                $is_enrol = $DB->get_record('fet_phy_other_city_enrol', array('oc_id' => $courseid, 'uid' => $user_id));
                if($is_enrol){
                    //to unenrol
                    $DB->delete_records('fet_phy_other_city_enrol', array('id'=>$is_enrol->id));
                    
                    if(!empty($row[7])){
                        block_mytoc_sync_phy_enrol('unenrol', $userinfo->idno, $userinfo->ecpa, $courseid, $idno, $row[7]);
                    }
                    else{
                        $sql = "SELECT c.ecpa FROM {fet_cert_setting} c 
                                LEFT JOIN {fet_pid} p ON c.uid = p.uid
                                WHERE p.idno =:idno";
                        $s_beaurau_id = $DB->get_field_sql($sql, array('idno' => $idno));
                        block_mytoc_sync_phy_enrol('unenrol', $userinfo->idno, $userinfo->ecpa, $courseid, $idno, $s_beaurau_id);
                    }
                
                    unset($is_enrol->id);
                    $is_enrol->status = 0;
                    $is_enrol->usercreated = $USER->id;
                    $is_enrol->remark = $remark;
                    $is_enrol->timecreated = $batchtime;
                    $DB->insert_record('fet_phy_other_city_log', $is_enrol);

                    $status .= get_string('success_unenrol', 'block_mytoc');
                    $upt->output($linenum, true, $status, $data);
                    $unenrol++;
                }
                else {
                    $status .= get_string('error_unenrol', 'block_mytoc');
                    $upt->output($linenum, false, $status, $data);
                    $skipped++;
                }
                
            }
   		}
        
        $upt->finish();
        $upt->results($linenum, $errors, $skipped, $enrol, $unenrol);
        
        $cir->close();
        $cir->cleanup(true);
        
        echo $OUTPUT->continue_button(new moodle_url('/my/index.php?mytoctab=P2')); 
        echo $OUTPUT->footer();
    }
    else {
        echo $OUTPUT->header();
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
}
else {
    $cir = new csv_import_reader($iid, 'uploadphyenrol');
}
