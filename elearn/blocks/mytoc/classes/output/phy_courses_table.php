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
 * PHY courses table.
 * 
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_mytoc\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use html_writer;
use moodle_url;
use single_button;
use paging_bar;
use renderer_base;
use flexible_table;

class phy_courses_table extends flexible_table {

    protected $totalcourses;
    protected $courses;

    /**
     * Constructor.
     *
     */
    public function __construct($totalcourses, $courses) {
        parent::__construct('block-mytoc-phy-courses-table');

        $this->totalcourses = $totalcourses;
        $this->courses = $courses;

        // Define columns, and headers.
        //$columns = array('stdno', 'year', 'r.class_name', 'r.term', 'jobtitle', 'company', 'b2.name', 'ru.room_id', 'r.start_date1', 'certificate_upload', 'files_upload');
        $columns = array('stdno', 'year', 'class_name', 'term', 'jobtitle', 'company', 'name', 'room_id', 'start_date1', 'certificate_upload', 'files_upload');
        $headers = array(get_string('stdno', 'block_mytoc'), get_string('year', 'block_mytoc'), get_string('class_name', 'block_mytoc'), get_string('term', 'block_mytoc')
                        , get_string('jobtitle', 'block_mytoc'), get_string('company', 'block_mytoc'), get_string('company_enroll', 'block_mytoc'), get_string('room', 'block_mytoc')
                        , get_string('course_open', 'block_mytoc'), get_string('certificate_upload', 'block_mytoc'), get_string('files_upload', 'block_mytoc'));
        $this->define_columns($columns);
        $this->define_headers($headers);

        // Define various table settings.
        //$this->sortable(true, 'r.start_date1', SORT_DESC);
        $this->sortable(true, 'start_date1', SORT_DESC);
        $nosorting = array('stdno', 'year', 'jobtitle', 'company', 'certificate_upload', 'files_upload');
        foreach ($nosorting as $column) {
            $this->no_sorting($column);
        }

        $this->collapsible(false);
        $this->set_attribute('class', 'block_mytoc-phy-courses-table generaltable');

        //$this->show_download_buttons_at(array(TABLE_P_BOTTOM));
        
    }

    /**
     * Output the table.
     */
    public function out($pagesize) {
        global $OUTPUT;
        //debugbreak();
        $this->setup();

        //$this->start_output();
        // Compute where to start from.
        if (!empty($this->courses)) {
            $this->pagesize($pagesize, count($this->totalcourses));
        }

        foreach ($this->courses as $seq_no => $data) {
            $row = array();
            $class = '';

            $row['stdno'] = $data['st_no'];
            //$row[] = $data['pname'];//姓名
            $row['year'] = $data['year'];
            $row['class_name'] = $data['class_name'];
            $row['term'] = $data['term'];
            $row['jobtitle'] = $data['name'];//職稱
            $row['company'] = $data['bname'];//就職機關
            $row['company_enroll'] = $data['unit_name'];//報名機關

            //class schedule
            $print_schedule = 'https://dcsdcourse.taipei.gov.tw/base/admin/create_class/print_schedule/print/'.$seq_no;
            $turntourl = new moodle_url('/sso/phy.php', array('to'=>$print_schedule));
            $print_schedule = html_writer::link($turntourl, get_string('print_schedule', 'block_mytoc'), array('onclick'=>'this.target="_blank"'));         
            //student list
            //$student_list = 'https://dcsdcourse.taipei.gov.tw/base/admin/student_list_pdf.php?uid='.$data['uid'].'&year='.$data['year'].'&class_no='.$data['class_id'].'&term='.$data['term'].'&tmp_seq=0&ShowRetirement=1';
            //$turntourl = new moodle_url('/sso/phy.php', array('to'=>$student_list));
            //$student_list = html_writer::link($turntourl, get_string('student_list', 'block_mytoc'), array('onclick'=>'this.target="_blank"'));

            $row['room'] = $data['room_code'] . '<br/>'. $print_schedule;
            $row['course_open'] = date('Y-m-d',strtotime($data['start_date1']));
            
            //上傳 : "../student/class_record/modify_upload/<?=htmlspecialchars($row['seq_no'],ENT_HTML5|ENT_QUOTES)
            //FilePath /var/www/html/base/admin/files/upload_modify/
            //https://dcsdcourse.taipei.gov.tw/base/admin/files/upload_modify/1682391445_28042_167809.png
            //if(!empty($data['filename']) && !empty($data['path'])) {
                /*
                $fileurl = 'https://dcsdcourse.taipei.gov.tw/base/admin/student/class_record/download/'.$seq_no;
                $turntourl = new moodle_url('/sso/phy.php', array('to'=>$fileurl));
                $row['modify_upload'] = html_writer::link($turntourl, $data['filename'], array('onclick'=>'this.target="_blank"'));
                */
            //    $tmp = explode("/", $data['path']);
            //    $url = 'https://dcsdcourse.taipei.gov.tw/base/admin/files/upload_modify/'.end($tmp);;
            //    $row['modify_upload'] = html_writer::link($url, $data['filename'], array('onclick'=>'this.target="_blank"'));
            //}
            //else {
            //    $row['modify_upload'] = '';
            //}
            
            //certificate
            //FilePath /var/www/html/base/admin/files/certificate/
            $certlist = '';
            if(isset($data['certs'])){
                foreach($data['certs'] as $certs){
                    if($certs['category'] == '1'){
                        $fileurl = 'https://dcsdcourse.taipei.gov.tw/base/admin/management/certificate_list/download_cer_pdf/'.$certs['id'];
                        $turntourl = new moodle_url('/sso/phy.php', array('to'=>$fileurl));
                        $certlist .= html_writer::tag('div', html_writer::link($turntourl, $certs['cer_name'], array('onclick'=>'this.target="_blank"')));
                    }
                    else if ($certs['category'] == '2'){
                        $fileurl = 'https://dcsdcourse.taipei.gov.tw/base/admin/management/certificate_list/download_en_cer_pdf/'.$certs['id'];
                        $turntourl = new moodle_url('/sso/phy.php', array('to'=>$fileurl));
                        $certlist .= html_writer::tag('div', html_writer::link($turntourl, $certs['cer_name'], array('onclick'=>'this.target="_blank"')));
                    }
                }
            }
            //other certificate
            //FilePath /var/www/html/base/admin/files/upload_cert_other/
            if(isset($data['othercerts'])){
                foreach($data['othercerts'] as $ocerts){
                    $extension = pathinfo($ocerts['other_cer_name'], PATHINFO_EXTENSION);
                    /*
                    $fileurl = 'https://dcsdcourse.taipei.gov.tw/base/admin/files/upload_cert_other/'.$ocerts['id'].'_'.$ocerts['other_id'].'.'.$extension;
                    $turntourl = new moodle_url('/sso/phy.php', array('to'=>$fileurl));
                    $certlist .= html_writer::link($turntourl, '書證', array('onclick'=>'this.target="_blank"'));
                    */
                    $url = 'https://dcsdcourse.taipei.gov.tw/base/admin/files/upload_cert_other/'.$ocerts['id'].'_'.$ocerts['other_id'].'.'.$extension;
                    $certlist .= html_writer::tag('div', html_writer::link($url, '書證', array('onclick'=>'this.target="_blank"')));
                }
            }
            $row['certificate_upload'] = $certlist;

            //files upload
            //FilePath /var/www/resource/ (is link path)
            $filelist = '';
            if(isset($data['files'])){
                foreach($data['files'] as $file){
                    $tmp = explode("/", $file['file_path']);
                    $url = 'https://dcsdcourse.taipei.gov.tw/base/media/'.$file['file_path'];
                    $filelist .= html_writer::tag('div', html_writer::link($url, $tmp[2], array('onclick'=>'this.target="_blank"')));
                }
            }

            $row['files_upload'] = $filelist;

            $this->add_data($row, $class);
        }

        $this->finish_output();
    }

    /**
     * Start HTML.
     *
     * Complete override to suppress some features.
     *
     * @return void
     */
    public function start_html() {
        $this->wrap_html_start();
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::start_tag('table', $this->attributes);
    }

    /**
     * Generate the HTML for the sort link. This is a helper method used by {@link print_headers()}.
     * @param string $text the text for the link.
     * @param string $column the column name, may be a fake column like 'firstname' or a real one.
     * @param bool $isprimary whether the is column is the current primary sort column.
     * @param int $order SORT_ASC or SORT_DESC
     * @return string HTML fragment.
     */
    protected function sort_link($text, $column, $isprimary, $order) {
        return html_writer::link($this->baseurl->out(false,
                array($this->request[TABLE_VAR_SORT] => $column, 'sort'=>$order)),
                $text . get_accesshide(get_string('sortby') . ' ' .
                $text . ' ' . $this->sort_order_name($isprimary, $order))) . ' ' .
                $this->sort_icon($isprimary, $order);
    }

    /**
     * Finish HTML.
     *
     * @return void
     */
    public function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
            $this->print_nothing_to_display();
        }
        else {
            echo html_writer::end_tag('tbody');
            echo html_writer::end_tag('table');
            echo html_writer::end_tag('div');
            $this->wrap_html_finish();
            // End copy from parent method.

            // Paging bar
            if(in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                echo $this->download_buttons();
            }
            if ($this->use_pages) {
                // Paging bar as per parent method.
                $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
                $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
                echo $OUTPUT->render($pagingbar);
            }
        }
    }

    /**
     * Override to rephrase.
     *
     * @return void
     */
    public function print_nothing_to_display() {
        echo \html_writer::div(get_string('nothingtodisplay'),
            '',
            ['style' => 'margin: 1em 0']
        );
    }

}
