<?php
/**
 * PHY open other city enrol class table for the My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace block_mytoc\output;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require_once($CFG->dirroot.'/courserecord/lib.php');

use stdClass;
use table_sql;
use moodle_url;
use single_button;
use context_system;

class other_city_table extends table_sql {
    public function __construct($year = null, $userid = null, $other_where, $returnurl) {
        parent::__construct('block_mytoc_other_city_table');
        global $DB, $USER;

        if(empty($userid)){
           $this->userid = $USER->id; 
        }else {
            $this->userid = $userid;
        }
        $this->returnurl = $returnurl;
        
        // Define columns.
        $this->define_columns(array(
            'year',
            'class_no',
            'class_name',
            'term',
            'course_date',
            'apply_date',
            'worker',
            'action'
        ));
        $this->define_headers(array(
            get_string('year', 'block_mytoc'),
            get_string('class_no', 'block_mytoc'),
            get_string('class_name', 'block_mytoc'),
            get_string('term', 'block_mytoc'),
            get_string('course_date', 'block_mytoc'),
            get_string('apply_date', 'block_mytoc'),
            get_string('worker', 'block_mytoc'),
            get_string('action', 'block_mytoc'),
        ));
        
        // Define SQL.
        $sqlparams = array();
        $sqlfrom = '{fet_phy_other_city} oc
                    LEFT JOIN {fet_pid} fp ON oc.worker = fp.idno
                    LEFT JOIN {user} u ON u.id = fp.uid
                    LEFT JOIN {fet_phy_other_city_enrol} oe ON oe.uid = :userid AND oe.oc_id = oc.id';
        $sqlparams = array('userid' => $this->userid);
        $this->sql = new stdClass();
        $this->sql->fields = 'oc.*, u.firstname, u.lastname, oe.id as enrolled';
        $this->sql->from = $sqlfrom;
        $this->sql->where = ' oc.visible = 1 AND year = :year '.$other_where;
        $this->sql->params = array_merge(array('year' => $year), $sqlparams);

        // Define various table settings.
        $this->sortable(true, 'oc.start_date', SORT_DESC);
        $this->no_sorting('course_date');
        $this->no_sorting('apply_date');
        $this->no_sorting('worker');
        $this->no_sorting('action');

        $this->column_style_all('text-align','center');
        $this->column_style_all('vertical-align','middle');
        $this->column_style('class_name', 'text-align','left');
        
        $this->collapsible(false);
    }

    public function col_worker($row) {        
        return $row->lastname.''.$row->firstname;
    }
    public function col_course_date($row) {
        return date("Y-m-d", $row->start_date).'<br>|<br>'.date("Y-m-d", $row->end_date);
    }
    public function col_apply_date($row) {
        $content = date("Y-m-d", $row->apply_sdate1).'<br>|<br>'.date("Y-m-d", $row->apply_edate1);
        if(!empty($row->apply_sdate2) && !empty($row->apply_edate2)){
            $content .= "<br>".date("Y-m-d", $row->apply_sdate2).'<br>|<br>'.date("Y-m-d", $row->apply_edate2);
        }
        
        return $content;
    }
    
    public function col_action($row) {
        global $OUTPUT, $DB;

        $range1 = array('options' => array('min_range' => $row->apply_sdate1, 'max_range' => $row->apply_edate1));
        if (filter_var(time(), FILTER_VALIDATE_INT, $range1) == false){
            if(!empty($row->apply_sdate2) && !empty($row->apply_sdate2)){
                $range2 = array('options' => array('min_range' => $row->apply_sdate2, 'max_range' => $row->apply_edate2));
                if (filter_var(time(), FILTER_VALIDATE_INT, $range2) == false){
                    return get_string('notify_not_open', 'block_mytoc');
                }
            }else {
                return get_string('notify_not_open', 'block_mytoc');
            }
        }

        $msg = "";
        $userinfo = get_user_info($this->userid);
        if($row->only_servant){
            $msg = get_string('notify_only_servant', 'block_mytoc')."<br>";
            if(empty($userinfo->ecpa) OR empty($userinfo->idno)){
                return $msg;
            }
        }
        
        if(empty($row->enrolled)){
            //single enroll
            $url = new moodle_url('/blocks/mytoc/enrolself.php', array('sesskey' => sesskey(), 'status'=>'1','uid'=>$this->userid, 'cid'=>$row->id, 'year'=>$row->year, 'tourl'=>$this->returnurl));
            $btn1 = new single_button($url, get_string('enroll', 'block_mytoc'), 'post');
            $btn1->add_confirm_action(get_string('enrollconfirm', 'block_mytoc', $row->class_name));
            $button = $OUTPUT->render($btn1);
        }
        else {
            //single unenroll
            $url = new moodle_url('/blocks/mytoc/enrolself.php', array('sesskey' => sesskey(), 'status'=>'0','uid'=>$this->userid, 'cid'=>$row->id, 'year'=>$row->year, 'tourl'=>$this->returnurl));
            $btn1 = new single_button($url, get_string('unenroll', 'block_mytoc'), 'post');
            $btn1->add_confirm_action(get_string('unenrollconfirm', 'block_mytoc', $row->class_name));
            $button = $OUTPUT->render($btn1);
        }

        //csv import
        $url = new moodle_url('/blocks/mytoc/upload.php', array('cid'=>$row->id));
        $btn2 = new single_button($url, get_string('import_csv', 'block_mytoc'));
        $button .= $OUTPUT->render($btn2);

        $context = context_system::instance();
        if(has_capability('block/mytoc:manage', $context)){
            $url = new moodle_url('/blocks/mytoc/enrolself.php', array('sesskey' => sesskey(), 'status'=>'2','uid'=>$this->userid, 'cid'=>$row->id, 'year'=>$row->year, 'tourl'=>$this->returnurl));
            $btn3 = new single_button($url, get_string('export_csv', 'block_mytoc'), 'post');
            $button .= $OUTPUT->render($btn3);
        }
        return $msg.$button;
    }
    
    /**
     * This function is not part of the public api.
     */
    function print_nothing_to_display() {
        global $OUTPUT;

        // Render button to allow user to reset table preferences.
        //echo $this->render_reset_button();

        $this->print_initials_bar();
                                                       
        echo get_string('nodata', 'block_mytoc');
    }   
}
