<?php
/**
 * Enrollment PHY courses for My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/mytoc/locallib.php');
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require_once($CFG->dirroot.'/courserecord/lib.php');

$year = required_param('year', PARAM_INT);
$courseid = required_param('cid', PARAM_INT);
$userid = required_param('uid', PARAM_INT);
$status = required_param('status', PARAM_INT);
$returnurl = required_param('tourl', PARAM_URL);

require_login();
if (confirm_sesskey()) { 
    $msg = "";

    $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
    $userfullname = $user->lastname.''.$user->firstname;
    $is_enrolled = $DB->get_record('fet_phy_other_city_enrol', array('oc_id'=>$courseid, 'uid'=>$userid));

    $userinfo = get_user_info($userid);
    switch($status) {
        case 0://unenrol self
            if($is_enrolled){
                $DB->delete_records('fet_phy_other_city_enrol', array('id'=>$is_enrolled->id));
                block_mytoc_sync_phy_enrol('unenrol', $userinfo->idno, $userinfo->ecpa, $courseid, $userinfo->idno, $userinfo->ecpa);
                
                unset($is_enrolled->id);
                $is_enrolled->status = 0;
                $is_enrolled->usercreated = $USER->id;
                $is_enrolled->timecreated = time();
                $DB->insert_record('fet_phy_other_city_log', $is_enrolled);
            }
            else {$msg = get_string('notify_notenrolled', 'block_mytoc', $userfullname);}
            break;
        case 1://enrol self
            if(!$is_enrolled){

                if(block_mytoc_check_phy_user($userinfo->idno)){
                    $data              = new stdClass();
                    $data->oc_id       = $courseid;
                    $data->uid         = $userid;
                    $data->idno        = $userinfo->idno;
                    $data->usercreated = $USER->id;
                    $data->timecreated = time();
                    $DB->insert_record('fet_phy_other_city_enrol', $data, false);
                    block_mytoc_sync_phy_enrol('enrol', $userinfo->idno, $userinfo->ecpa, $courseid, $userinfo->idno, $userinfo->ecpa);
                    
                    $data->status = 1;
                    $DB->insert_record('fet_phy_other_city_log', $data);

                    $msg = get_string('success_enrol', 'block_mytoc');
                }
                else {
                    //$msg = get_string('notify_phy_not_exist', 'block_mytoc');
                    $returnurl = new moodle_url('/blocks/mytoc/register.php', array('sesskey' => sesskey(), 'cid'=>$courseid, 'uid'=>$userid, 'year'=>$year));
                    redirect($returnurl);
                }
            }
            else {
                $msg = get_string('notify_enrolled', 'block_mytoc', $userfullname);
            }
            break;
        case 2://export csv
            block_mytoc_export_csv($courseid);
            break;
        default:
            break;
    }
    
    //redirect($returnurl, $msg, null, \core\output\notification::NOTIFY_MESSAGE);
    if(!empty($msg)){
        echo $OUTPUT->header();
        echo $OUTPUT->container($msg, 'important', 'notice');
        echo $OUTPUT->continue_button($returnurl); 
        echo $OUTPUT->footer();
    }else {
        redirect($returnurl);
    }
}