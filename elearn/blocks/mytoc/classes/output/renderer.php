<?php
/**
 * Renderer for the My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class block_mytoc_renderer extends plugin_renderer_base {

    /**
     * Construct contents of course_overview block
     *
     * @param array $courses list of courses in sorted order
     * @param array $overviews list of course overviews
     * @return string html to be displayed in course_overview block
     */
    public function course_overview($tab, $userid) {
        global $CFG, $USER;

        //print tabs
        require($CFG->dirroot . '/blocks/mytoc/tabs.php');
        $content = print_tabs([$top], $tab, null, null, true);
        
        $params['pagenum']  = optional_param('pagenum', 0, PARAM_INT); // page number
        $params['mode']    = optional_param('mode', 0, PARAM_INT); // 模式 預設0精簡, 1完整
        $params['cstatus']    = optional_param('cstatus', 0, PARAM_INT); // 課程狀態 0全部, 1完成, 2未完成
        $params['ssearch'] = optional_param('ssearch', '', PARAM_TEXT); // 課程狀態 0全部, 1完成, 2未完成
        $params['r']   = optional_param('r', 0, PARAM_INT); // 更新學習紀錄
        $params['list']  = optional_param('list', 10, PARAM_INT);
        $params['from_mode'] = optional_param('from_mode', 0, PARAM_INT);
        $params['queryYear'] = optional_param('queryYear', -1, PARAM_INT);

        $pageurl = new moodle_url('/courserecord/index_my.php', $params);

        $content .= html_writer::start_tag('div', array('id'=>'mytoc-other-city-class-list', 'class' => 'block-mytoc block-cards'));
        $content .= html_writer::start_tag('div', array('class' => 'container-fluid p-0'));
        //$content .= file_get_contents($pageurl);
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
        
        return $content;
    }
    
    /**
    * print all phy open other city courses
    * 
    * @param mixed $tab
    */
    public function mytoc_phy_other_city_list($tab, $userid) {
        global $CFG, $USER, $DB;

        //print tabs
        require($CFG->dirroot . '/blocks/mytoc/tabs.php');
        $currentyear = date('Y')-1911;
        $year = optional_param('year', $currentyear, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $class_name = optional_param('class_n', null, PARAM_TEXT);
        $enable_date = optional_param('enable_date', false, PARAM_BOOL);
        $qy = optional_param('q_y', null, PARAM_INT);
        $qm = optional_param('q_m', null, PARAM_INT);
        $qd = optional_param('q_d', null, PARAM_INT);

        
        $other_where = "";
        $checked = false;
        if(!empty($class_name)){
            $other_where .= " AND oc.class_name LIKE '%".$class_name."%'";
        }
        
        if($enable_date){
            $checked = true;
            $start_date = strtotime($qy."-".$qm."-".$qd);
            $other_where .= " AND oc.start_date < ".$start_date;
        }else {
            $start_date = time();
        }
        
        
        $params = array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_PHY_OTHER_CITY_VIEW, 'year'=>$year, 'page'=>$page);
        if($userid != $USER->id){
            $params['id'] = $userid;
            $returnurl = new moodle_url('/user/profile.php', $params);
        }else{
            $returnurl = new moodle_url('/my/index.php', $params);
        }

        $content = print_tabs([$top], $tab, null, null, true);

        $years = $DB->get_records_sql_menu("SELECT DISTINCT year as id , year FROM {fet_phy_other_city} WHERE visible = 1");
        if(!in_array($years, $currentyear)){
            $years[$currentyear] = $currentyear;
        }
        krsort($years);
        $content .= html_writer::start_tag('div', array('id'=>'mytoc-other-city-class-list', 'class' => 'block-mytoc block-cards'));
        $content .= html_writer::start_tag('div', array('class' => 'container-fluid p-0'));
        $content .= html_writer::start_tag('form', array('class' => 'searchform', 'action' => $returnurl, 'method' => 'POST'));
        $content .= html_writer::start_tag('fieldset');
        //$content .= html_writer::label(get_string('searchclass', 'block_mytoc'), 'class_search'); // No : in form labels!
        $content .= html_writer::select($years, 'year', $year, array());
        $content .= html_writer::empty_tag('input', array('id'=>'class_n', 'type'=>'text', 'name'=>'class_n', 'value'=>s($class_name), 'placeholder'=>get_string('class_name', 'block_mytoc')));
        $content .= html_writer::label(get_string('course_date', 'block_mytoc'), 'class_search'); // No : in form labels!
        $content .= html_writer::select_time('years', 'q_y', $start_date);
        $content .= html_writer::select_time('months', 'q_m', $start_date);
        $content .= html_writer::select_time('days', 'q_d', $start_date);
        $content .= html_writer::checkbox('enable_date', true, $checked);
        $content .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('search', 'admin')));
        $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'mytoctab', 'value' => BLOCK_MYTOC_PHY_OTHER_CITY_VIEW));
        $content .= html_writer::end_tag('fieldset');
        $content .= html_writer::end_tag('form');
        
        $content .= html_writer::nonempty_tag('div', get_string('description', 'block_mytoc'));
        
        /*
        //csv import
        $content .= html_writer::start_tag('div', array('class' => 'uploadbutton container-fluid p-0'));
        $uploadurl = new moodle_url('/blocks/mytoc/upload.php');
        $content .= html_writer::start_tag('form', array('action' => $uploadurl, 'method' => 'GET'));
        $content .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('import_csv', 'block_mytoc')));
        $content .= html_writer::end_tag('form');
        $content .= html_writer::end_tag('div');
        */

        $table = new \block_mytoc\output\other_city_table($year, $userid, $other_where, $returnurl);
        $table->collapsible(false);//disable Field hide
        $url->param('year', $year);
        $table->define_baseurl($returnurl);

        $content .= self::other_city_manage_page($table);
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('div');
	
        return $content;
    }
    
    public function other_city_manage_page($table) {
        $config  = get_config('block_mytoc');
        
        ob_start();
        $table->set_attribute('class', '');
        $table->out($config->coursesperpage, true);

        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }


    /**
    * print all phy courses record and files 
    * 
    * @param mixed $tab
    */
    public function mytoc_phy_courses_list($tab, $userid) {
        global $CFG, $USER, $OUTPUT;

        require_once ($CFG->dirroot . '/functions.php');
        require($CFG->dirroot . '/blocks/mytoc/tabs.php');//print tabs

        $config  = get_config('block_mytoc');
        $currentyear = date('Y')-1911;

        $year = optional_param('year', $currentyear, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', $config->coursesperpage , PARAM_INT);
        $sortby = optional_param('tsort', 'start_date1', PARAM_TEXT);
        $sort = optional_param('sort', 4, PARAM_INT);

        $params = array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_PHY_COURSES_VIEW, 'year'=>$year, 'tsort'=>$sortby, 'sort'=>$sort);
        if($userid != $USER->id){
            $params['id'] = $userid;
            $returnurl = new moodle_url('/user/profile.php', $params);
        }else{
            $returnurl = new moodle_url('/my/index.php', $params);
        }

        $idno = getIdno($USER->id);
        //$idno = 'O200067707';//F222939320//O100097909
        //$idno = 'W100379878';//陳O威
        //$idno = 'admin';
        
        //$htmltable = $this->phy_courses_table($year, $idno, $returnurl, $download, $tsort);

        if($sort == SORT_ASC){
            $sortby = $sortby.' ASC';
        }else {
            $sortby = $sortby.' DESC';
        }

        $totalcourses = block_mytoc_phy_courses($year, $idno, $sortby);
        $courses = block_mytoc_phy_courses($year, $idno, $sortby, $page, $perpage);

        $table = new block_mytoc\output\phy_courses_table($totalcourses, $courses);
        $table->define_baseurl($returnurl);

        $content = '';
        $yearoptions = [];
        for($y = $currentyear; $y >= 98; $y--){
            $yearoptions[$y] = $y;
        }

        $content .= print_tabs([$top], $tab, null, null, true);

        $content .= html_writer::start_tag('div', array('id'=>'mytoc-phy-courses-list', 'class' => 'block-mytoc block-cards'));
        $content .= html_writer::start_tag('div', array('class' => 'container-fluid p-0'));
        $content .= html_writer::start_tag('div', array('class' => 'searchform span9'));
        $content .= html_writer::start_tag('form', array('class' => 'searchform', 'action' => $returnurl, 'method' => 'POST'));
        $content .= html_writer::start_tag('fieldset');
        $content .= html_writer::select($yearoptions, 'year', $year, array());
        $content .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('search', 'admin')));
        $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $content .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'mytoctab', 'value' => BLOCK_MYTOC_PHY_COURSES_VIEW));
        $content .= html_writer::end_tag('fieldset');
        $content .= html_writer::end_tag('form');
        $content .= html_writer::end_tag('div');
        /*
        $params['idno'] = $idno;
        $url = new moodle_url('/blocks/mytoc/download.php', $params);
        $download = new single_button($url, get_string('downloadexcel'), 'post');
        $content .= html_writer::start_tag('div', array('class' => 'download span3 pull-right'));
        $content .= $OUTPUT->render($download);
        $content .= html_writer::end_tag('div');
        */
        $content .= html_writer::end_tag('div');

        
        ob_start();
        $table->out($perpage, true);
        $content .= ob_get_contents();
        ob_end_clean();

        $content .= html_writer::end_tag('div');

        return $content;
    }

}
