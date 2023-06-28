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
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require_once($CFG->dirroot.'/blocks/mytoc/locallib.php');
require_once($CFG->dirroot.'/courserecord/lib.php');

$courseid = required_param('cid', PARAM_INT);//phy
$userid   = required_param('uid', PARAM_INT);
$year     = required_param('year', PARAM_INT);

$params = array('cid'=>$courseid, 'uid'=>$userid);

require_login();
$context = context_course::instance(SITEID);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/blocks/mytoc/register.php', $params);

$title = get_string('register_phy', 'block_mytoc');
$PAGE->set_title($title);
$PAGE->set_heading($title);

require_sesskey();
$userinfo = get_user_info($userid);
$userdata = array();
$userdata['year'] = $year;
$userdata['uid'] = $userid;
$userdata['cid'] = $courseid;
$userdata['name'] = $userinfo->firstname;
$userdata['idno'] = $userinfo->idno;
$userdata['idno2'] = $userinfo->idno;
$userdata['bureau_id'] = $userinfo->ecpa;
$userdata['office_email'] = $userinfo->email;

$args = array('data' => $userdata);
$mform1 = new block_mytoc_register_form(null, $args);

$returnurl = new moodle_url('/my/index.php', array('mytoctab' => 'P2', 'year'=>$year, 'sesskey' => sesskey()));
if ($mform1->is_cancelled()) {
    redirect($returnurl); // Back to course view.
}
else if ($formdata = $mform1->get_data()) {
    $enroltime = time();
    $errors = 0;
    $status = '';

    $row = array();
    //csv format
    $row[0] = 1;
    $row[1] = s($formdata->name);
    $row[2] = $idno = strtoupper(trim($formdata->idno));
    $row[3] = $formdata->gender;
    $row[4] = date('Y/m/d', $formdata->birthday);
    $row[5] = $formdata->email;
    $row[6] = $formdata->office_email;
    $row[7] = $formdata->bureau_id;
    $row[8] = $formdata->out_gov_name;
    $row[9] = $formdata->education;
    $row[10] = $formdata->job_distinguish;
    $row[11] = $formdata->co_empdb_poftel;
    $row[12] = $formdata->office_fax;
    $row[13] = trim($formdata->job_title_id);
    $row[14] = $formdata->cellphone;
    //form format
    $row[15] = $formdata->en_name;
    $row[16] = $formdata->supervisor_id;
    $row[17] = trim($formdata->job_level_id);
    $row[18] = $formdata->departure;
    $row[19] = $formdata->retirement;
    $row[20] = $formdata->showretirement;

    $remark = implode(',', $row);

    //check and create phy account(BS_user)
    $phy_exist = block_mytoc_get_phy_user($row, $idno);
    if($phy_exist != 'TRUE' && $phy_exist != 'CREATE_TRUE'){
        block_mytoc_error_log($enroltime, $idno, $courseid, $remark, $status);

        redirect($returnurl, get_string('error_api_phy', 'block_mytoc'));
    }

    $is_enrol = $DB->record_exists('fet_phy_other_city_enrol', array('oc_id' => $courseid, 'uid' => $userid));
    if(!$is_enrol){
        //to enrol
        $tmp              = new stdClass();
        $tmp->oc_id       = $courseid;
        $tmp->uid         = $userid;
        $tmp->idno        = $idno;
        $tmp->usercreated = $USER->id;
        $tmp->timecreated = $enroltime;
        $DB->insert_record('fet_phy_other_city_enrol', $tmp, false);
        block_mytoc_sync_phy_enrol('enrol', $userinfo->idno, $userinfo->ecpa, $courseid, $idno, $row[7]);
        
        $tmp->status = 1;
        $tmp->remark = $remark;
        $DB->insert_record('fet_phy_other_city_log', $tmp);

        redirect($returnurl, get_string('success_enrol', 'block_mytoc'));
    }
    else {
        redirect($returnurl, get_string('error_enrol', 'block_mytoc'));
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform1->display();
echo $OUTPUT->footer();
die;
