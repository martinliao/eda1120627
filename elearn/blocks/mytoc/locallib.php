<?php
/**
 * Local lib for the My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Gets user course sorting preference in course_overview block
 *
 * @return array list of course ids
 */
function block_mytoc_get_myorder() {
    $order = array();
    //todo Heart
    return $order;
}

/**
 * Sets user preference for tab
 *
 * @param int tab
 */
function block_mytoc_update_mytab($tab, $userid) {
    set_user_preference('mytoc_tab', $tab, $userid);
}

function block_mytoc_get_sorted_courses($userid, $showroles = 2, $showall=false, $page = 0, $perpage = 10) {
    $courses = block_mytoc_get_my_courses($userid, 'startdate DESC, sortorder DESC', $showroles, $showall, $page, $perpage);
    
    if (array_key_exists(SITEID,$courses)) {
        unset($courses[SITEID]);
    }

    // Get remote courses.
    $remotecourses = array();
    if (is_enabled_auth('mnet')) {
        $remotecourses = get_my_remotecourses($userid);
    }
    // Remote courses will have -ve remoteid as key, so it can be differentiated from normal courses
    foreach ($remotecourses as $id => $val) {
        $remoteid = $val->remoteid * -1;
        $val->id = $remoteid;
        $courses[$remoteid] = $val;
    }

    return $courses;
}

/**
 * Returns list of courses current $USER is enrolled in and can access
 *
 * - $fields is an array of field names to ADD
 *   so name the fields you really need, which will
 *   be added and uniq'd
 *
 * @param string|array $fields
 * @param string $sort
 * @param int $limit max number of courses
 * @return array
 */
function block_mytoc_get_my_courses($userid, $sort = 'startdate DESC, sortorder DESC', $showroles, $showall=false, $page=0, $perpage=5) {
    global $DB, $USER;
    
    // Guest account does not have any courses
    if (isguestuser() or !isloggedin()) {
        return(array());
    }

    $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce', 'cacherev', 'enablecompletion');

    if (empty($fields)) {
        $fields = $basefields;
    }
    else if (is_string($fields)) {
        // turn the fields from a string to an array
        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_unique(array_merge($basefields, $fields));
    }
    else if (is_array($fields)) {
        $fields = array_unique(array_merge($basefields, $fields));
    }
    else {
        throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
    }
    if (in_array('*', $fields)) {
        $fields = array('*');
    }
    
    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);

    $coursefields = 'c.' .join(',c.', $fields);
    $ccselect = ', ca.id as categoryid, ca.name as categoryname, cc.timecompleted, ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = " LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                LEFT JOIN {course_categories} ca ON ca.id = c.category";
    $params['contextlevel'] = CONTEXT_COURSE;

    //only display select roles enrol courses
    $roles = $enrols = "";
    $config = get_config('block_mytoc');
    if($showroles == BLOCK_MYTOC_TEACHCOURSES_VIEW){
        $roles = $config->teachrole; 
    }
    else if($showroles == BLOCK_MYTOC_ENROLCOURSES_VIEW
            OR $showroles == BLOCK_MYTOC_ENROLCOURSES_COMMON_VIEW
            OR $showroles == BLOCK_MYTOC_ENROLCOURSES_ASSIGN_VIEW){
        $roles = $config->enrolrole;
        $wheres[] = "c.visible = 1 ";
        
        if($showroles == BLOCK_MYTOC_ENROLCOURSES_COMMON_VIEW){
            //enrol method : cohort
            $enrols = "'cohort'";
        }
        else if($showroles == BLOCK_MYTOC_ENROLCOURSES_ASSIGN_VIEW){
           //enrol method : profile, trainplan
           if(!empty($enrols)){
               $enrols .= ",'profile','trainplan'"; 
           }else{
               $enrols = "'profile','trainplan'"; 
           }
        }
    }
    if(!empty($roles)){
        $ccjoin .= " LEFT JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.userid = :rauserid";
        $wheres[] = "ra.roleid IN ($roles) ";
        $params['rauserid']  = $userid;
    }
    
    $dbman = $DB->get_manager();
    //order by favorite course
    if($dbman->table_exists(new xmldb_table('clickap_favorite'))){
        if ($DB->record_exists('clickap_favorite', array('user' => $userid))) {
            $ccjoin .= " LEFT JOIN {clickap_favorite} fav ON fav.course = c.id AND fav.user = :favuserid";
            //$ccselect .= ', fav.timemodified ';
            $params['favuserid']  = $userid;
            $favoritesort = 'fav.timemodified DESC, ';
        }
    }
    if($dbman->table_exists(new xmldb_table('clickap_course_info'))){
        $ccjoin .= " LEFT JOIN {clickap_course_info} ci ON ci.courseid = c.id";
        if($dbman->table_exists(new xmldb_table('clickap_course_origin'))){
            $ccjoin .= " LEFT JOIN {clickap_course_origin} co ON co.id = ci.originid";
            $ccselect .= " , co.id AS originid , co.name AS originname";
        }
    }
    
    $orderby = "";
    $sort    = trim($sort);
    if (!empty($sort)) {
        $rawsorts = explode(',', $sort);
        $sorts = array();
        foreach ($rawsorts as $rawsort) {
            $rawsort = trim($rawsort);
            if (strpos($rawsort, 'c.') === 0) {
                $rawsort = substr($rawsort, 2);
            }
            $sorts[] = trim($rawsort);
        }
        $sort = 'c.enablecompletion DESC, cc.timecompleted ASC, c.'.implode(',c.', $sorts);
        if(isset($favoritesort)){
            $orderby = "ORDER BY $favoritesort $sort";
        }
        else{
            $orderby = "ORDER BY $sort";
        }
    }

    $wheres = implode(" AND ", $wheres);
    //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
    $sql = "SELECT DISTINCT $coursefields $ccselect
              FROM {course} c
              JOIN (SELECT DISTINCT e.courseid
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                     WHERE ue.status = :active AND e.status = :enabled
                     AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)";
    if(!empty($enrols)){
        $sql .= " AND e.enrol IN ($enrols)";
    }
    $sql .= " ) en ON (en.courseid = c.id)
           $ccjoin
           LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = ra.userid
             WHERE $wheres
          $orderby";
    $params['userid']  = $userid;
    $params['active']  = ENROL_USER_ACTIVE;
    $params['enabled'] = ENROL_INSTANCE_ENABLED;
    $params['now1']    = round(time(), -2); // improves db caching
    $params['now2']    = $params['now1'];

    if(!$showall){
        $courses = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
    }
    else{
        $courses = $DB->get_records_sql($sql, $params);
    }

    /*
    // preload contexts and check visibility
    foreach ($courses as $id=>$course) {
        context_helper::preload_from_record($course);
        if (!$course->visible) {
            if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                unset($courses[$id]);
                continue;
            }
            if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                unset($courses[$id]);
                continue;
            }
        }
        $courses[$id] = $course;
    }
    */
    //wow! Is that really all? :-D
    return $courses;
}

function block_mytoc_get_other_city_courses($userid, $showroles = 2, $showall=false, $page = 0, $perpage = 10) {
    global $DB;
    
    if($showall){
        $courses = $DB->get_records('fet_phy_other_city', array('visible'=>1,'year'=>$year));
    }else {
        $courses = $DB->get_records('fet_phy_other_city', array('visible'=>1,'year'=>$year), 'start_date desc, apply_sdate1 desc', '*', $page, $perpage);
    }
    
    return $courses;
}

function block_mytoc_get_phy_class(){
    global $CFG, $DB, $USER;
    require_once($CFG->dirroot.'/courserecord/lib.php');

    $userinfo = get_user_info($USER->id);
    $sql = "SELECT id, seq_no, year, class_no, class_name FROM {fet_phy_other_city} 
            WHERE visible = 1 
            AND ((apply_sdate1 <= :sd1 AND apply_edate1 >= :ed1) OR (apply_sdate2 <= :sd2 AND apply_edate2 >= :ed2))";
    if(empty($userinfo->ecpa) OR empty($userinfo->idno)){
        $sql .= " AND only_servant = 0";
    }

    $data = array();
    if($results = $DB->get_records_sql($sql, array('sd1'=>time(),'ed1'=>time(),'sd2'=>time(),'ed2'=>time()))){
        foreach($results as $rs) {
            if(!in_array($rs->year, $data)){
                $data[0][$rs->year] = $rs->year;
            }
            $data[1][$rs->year][$rs->id] = $rs->class_name."_".$rs->seq_no;
        }
        
    }
    return $data;
}

function block_mytoc_get_user_by_idno($idno) {
    global $DB;
    
    if ($user = $DB->get_record('fet_pid', array('idno'=>$idno))){
        return $user->uid;
    }
    return false;
}

function block_mytoc_create_user($data) {
    global $CFG, $DB;
    
    $idno = $data[2];
    $username = strtoupper($data[2]);
    $firstname = addslashes(s($data[1]));
    $email = addslashes($data[6]);
    $ecpa = addslashes($data[7]);
    $passwd = 'Aa123456';

    $sql = "SELECT * FROM {temporary_user} WHERE idno = :idno OR email = :email";
    if ($DB->get_record_sql($sql, array('idno'=>$idno, 'email' =>$email))){
        return false;
    }
    
    //user
    $userdata = new stdClass();
    $userdata->username = $username;
    $userdata->passwd = password_hash($passwd, PASSWORD_DEFAULT);
    $userdata->mnethostid = $CFG->mnet_localhost_id;
    $userdata->confirmed = 1;
    $userdata->firstname = $firstname;
    $userdata->country = 'TW';
    $userdata->lang = 'zh_tw';
    $userdata->calendartype = 'gregorian';
    $userdata->timezone = 'Asia/Taipei';
    $userdata->timecreated = time();
    //temporary_user
    $userdata->idno = $idno;
    $userdata->name = $firstname;
    $userdata->email = $email;
    $userdata->sign_date = date('Y-m-d', time());

    $DB->insert_record('temporary_user', $userdata);
    $user_id = $DB->insert_record('user', $userdata, true);
    //eda account
    $pidata = new stdClass();
    $pidata->uid = $user_id;
    $pidata->idno = $idno;
    $pidata->temp = 1;
    $DB->insert_record('fet_pid', $pidata);
    //ecpa
    $certdata = new stdClass();
    $certdata->uid = $user_id;
    $certdata->ecpa = $ecpa;
    $certdata->createtime = time();
    $certdata->modifytime = time();
    $DB->insert_record('fet_cert_setting', $certdata);
    
    return $user_id;
}

function block_mytoc_error_log($batchtime, $idno, $courseid, $remark, $status){
    global $DB, $USER;
    
    $log = new stdClass();
    $log->oc_id = $courseid;
    $log->idno = $idno;
    $log->status = 2;
    $log->usercreated = $USER->id;
    $log->remark = $remark."-".$status;
    $log->timecreated = $batchtime;
    $DB->insert_record('fet_phy_other_city_log', $log);
}


function block_mytoc_export_csv($cid){
    global $CFG, $DB;
    require_once($CFG->dirroot.'/blocks/mytoc/lib.php');

    $courses = $DB->get_record('fet_phy_other_city', array('id'=>$cid));
    $strfilename = get_string('strfilename', 'block_mytoc').''.$courses->year.'-'.$courses->class_no.'-'.$courses->term.'.csv';
    
    header( "Content-Type: text/csv;charset=utf-8" );
    header( "Content-Disposition: attachment;filename=\"$strfilename\"" );
    header("Pragma: no-cache");
    header("Expires: 0");
    // 轉為UTF-8 BOM 使Excel開啟能正常顯示中文
    echo "\xEF\xBB\xBF";
    $fp= fopen('php://output', 'w');
    $head = array("姓名(必填)", "身分證字號(必填)", "性別(必填)", "出生日期(必填)", Email, "公司Email(必填)", "機關代碼(必填)", "外機關名稱全銜", "學歷(必填)", "現職區分(必填)", "公司電話(必填)", "公司傳真", "職稱(必填)", "手機號碼");
    fputcsv($fp, $head);

    $resutls = $DB->get_records('fet_phy_other_city_enrol', array('oc_id'=>$cid), 'idno');
    foreach ($resutls as $enrol) {
        $tmp = block_mytoc_check_phy_user($enrol->idno, true);
        $user = json_decode($tmp, true); //return array
        
        fputcsv($fp, $user);
    }
    fclose($fp);
    die;
}























function block_mytoc_get_collection_courses($userid, $showall=false, $page = 0, $perpage = 10) {
    global $DB;
    
    $wheres = array("c.id <> :siteid");
    $params = array('siteid'=>SITEID);
    $params['userid'] = $userid;
    $params['contextlevel'] = CONTEXT_COURSE;
    $sql = "SELECT DISTINCT c.*
            FROM {clickap_course_collection} cc
            LEFT JOIN {course} c ON cc.courseid = c.id 
            LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
            LEFT JOIN {user_enrolments} ue ON ue.userid = cc.userid AND ue.enrolid IN (SELECT id FROM {enrol} WHERE courseid = c.id)
            WHERE c.id <> :siteid AND c.visible = 1 AND cc.userid = :userid AND ue.id IS NULL
            ORDER BY c.startdate DESC, cc.timemodified DESC";
    
    if(!$showall){
        $courses = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);
    }
    else{
        $courses = $DB->get_records_sql($sql, $params);
    }

    return $courses;
}

/**
 * Returns maximum number of courses which will be displayed in course_overview block
 *
 * @param bool $showallcourses if set true all courses will be visible.
 * @return int maximum number of courses
 */
function block_mytoc_get_max_user_courses($showallcourses = false) {
    // Get block configuration
    $config = get_config('block_mytoc');
    $limit = $config->defaultmaxcourses;

    // If max course is not set then try get user preference
    if (empty($config->forcedefaultmaxcourses)) {
        if ($showallcourses) {
            $limit = 0;
        }
    }
    return $limit;
}

function block_mytoc_get_user_activity_completion($course, $userid, $cmid){
    global $DB;
    
    $sql = "SELECT cmc.completionstate
            FROM {course_modules cm}
            INNER JOIN {course_modules_completion} cmc ON cm.id=cmc.coursemoduleid
            WHERE cm.course= :courseid 
            AND cmc.userid = :userid AND cmc.coursemoduleid = :cmid";
    
    return $DB->get_field_sql($sql, array('courseid'=>$course->id, 'userid'=>$userid, 'cmid'=>$cmid));
}



function block_mytoc_overview_by_course($courses, $userid){
    global $DB, $CFG, $PAGE;
    require_once($CFG->dirroot.'/report/loggedinpro/addon/stayhours/locallib.php');
    
    $plugins = get_plugin_list('mod');
    
    $renderer = $PAGE->get_renderer('block_mytoc');
    
    $table = new html_table();
    $table->attributes = array('class'=>'admintable generaltable','style'=>'display: table;table-layout:fixed;');
    $table->head = array('&nbsp;', get_string('fullnamecourse'), get_string('categories')
                    , get_string('origin', 'clickap_course_origin'), get_string('completionstatus', 'block_mytoc')
                    , get_string('activityhits', 'loggedinaddon_stayhours'), get_string('coursestayhours', 'loggedinaddon_stayhours'));
    if(array_key_exists("videos", $plugins)){
        $table->head[] = get_string('videosviewhours', 'loggedinaddon_stayhours');
    }
    $table->align = array('center', 'left', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size  = array('5%', '20%', '20%', '10%');
    $cnt = 0;
    foreach ($courses as $course) {
        $list = array();
        $list[] = ++$cnt;
        
        $courseurl = new moodle_url('/course/view.php', array('id'=>$course->id));
        $list[] = '<a href = "'.$courseurl.'" target="_blank">'.$course->fullname.'</a>';
        $list[] = $course->categoryname;
        $list[] = !empty($course->originname) ? $course->originname : '- -';

        $statsdata = array('activityhits'=>0, 'coursestayhours'=>0);
        if(array_key_exists("videos", $plugins)){
            $statsdata['videosviewhours'] = 0;
        }
        loggedinaddon_stayhours_get_user_overview_detail_simple($course, $userid, $statsdata);
        
        $list[] = $renderer->mytoc_get_user_course_completion_status($course, $userid);
        $list[] = $statsdata['activityhits'];
        $list[] = $statsdata['coursestayhours'];
        if(array_key_exists("videos", $plugins)){
            $list[] = $statsdata['videosviewhours'];
        }
        $table->data[] = new html_table_row($list); 
    }
    
    return html_writer::table($table);
}

function block_mytoc_get_user_category_overview($courses, $userid){
    global $CFG;
    require_once($CFG->dirroot.'/report/loggedinpro/addon/stayhours/locallib.php');
    require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
    
    $categories = block_mytoc_get_user_category_overview_data($courses, $userid);
    
    $table = new html_table();
    $table->attributes = array('class'=>'admintable generaltable','style'=>'display: table;table-layout:fixed;');
    $table->head = array('&nbsp;', get_string('categories'), get_string('numberofcourses')
                    , get_string('coursecompletioncount', 'block_mytoc')
                    , get_string('activityhits', 'loggedinaddon_stayhours'), get_string('coursestayhours', 'loggedinaddon_stayhours'));
    if(array_key_exists("videos", get_plugin_list('mod'))){
        $table->head[] = get_string('videosviewhours', 'loggedinaddon_stayhours');
    }
    $table->align = array('center', 'left', 'center', 'center', 'center', 'center', 'center');
    $table->size  = array('5%', '20%');
    
    $cnt = 0;
    $total = array('', get_string('total', 'block_mytoc'), 'course'=>0, 'completion'=>0, 'activityhits'=>0, 'coursestayhours'=>0);
    if(array_key_exists("videos", get_plugin_list('mod'))){
        $total['videosviewhours'] = 0;
    }
    foreach($categories as $category){
        $list = array();
        $list[] = ++$cnt;
        $list[] = $category['name'];
        $list[] = $category['course'];
        $list[] = $category['completion'];
        $list[] = $category['activityhits'];
        $list[] = $category['coursestayhours'];
        
        $total['course'] += $category['course'];
        $total['completion'] += $category['completion'];
        $total['activityhits'] += $category['activityhits'];
        $total['coursestayhours'] += $category['coursestayhours'];
        if(array_key_exists("videos", get_plugin_list('mod'))){
            $list[] = $category['videosviewhours'];
            $total['videosviewhours'] += $category['videosviewhours'];
        }
        $table->data[] = new html_table_row($list);
    }
    $table->data[] = new html_table_row($total);
    
    return html_writer::table($table);
}

