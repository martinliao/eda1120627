<?php
/**
 * 
 *
 * @package    block_mytoc(My Table Of Content)
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2018 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( '../../config.php' );
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
require_once($CFG->libdir.'/excellib.class.php');

$year  = required_param('year', PARAM_INT);
$idno  = required_param('idno', PARAM_TEXT);
$sortby = optional_param('tsort', '', PARAM_TEXT);

require_login();

$context = context_user::instance($USER->id);
$PAGE->set_context($context);
$returnurl = new moodle_url('/my/index.php');

$courses = block_mytoc_phy_courses($year, $idno, $sortby);

$workbook = new MoodleExcelWorkbook('-');
$strfilename = get_string('filename_phycourses', 'block_mytoc').userdate(time(),'%Y%m%d',99,false);
$workbook->send($strfilename . '.xls');
$worksheet = $workbook->add_worksheet($strfilename);

$formatbc = $workbook->add_format();

$col = 0;
$worksheet->write(0, $col++, get_string('stdno', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('field_firstname', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('year', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('class_name', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('term', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('jobtitle', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('company', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('company_enroll', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('room', 'block_mytoc'));
$worksheet->write(0, $col++, get_string('course_open', 'block_mytoc'));

$row = 1;
foreach ($courses  as $seq_no => $data) {
    $col = 0;

    $worksheet->write($row, $col++, $data['st_no']);
    $worksheet->write($row, $col++, $data['pname']);
    $worksheet->write($row, $col++, $data['year']);
    $worksheet->write($row, $col++, $data['class_name']);
    $worksheet->write($row, $col++, $data['term']);
    $worksheet->write($row, $col++, $data['name']);
    $worksheet->write($row, $col++, $data['bname']);
    $worksheet->write($row, $col++, $data['unit_name']);
    $worksheet->write($row, $col++, $data['room_code']);
    $worksheet->write($row, $col++, date('Y-m-d',strtotime($data['start_date1'])));

    $row++;
}

$workbook->close();
  
die();