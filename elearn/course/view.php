<?php


//  Display the course home page.

    require_once('../config.php');
    require_once('lib.php');
    require_once($CFG->libdir.'/completionlib.php');
    ///dgs
    require_once('../functions.php');
    require_once('../courseinfo/lib.php');
    $id           = optional_param('id', 0, PARAM_INT);
    $name         = optional_param('name', '', PARAM_RAW);
    $edit         = optional_param('edit', -1, PARAM_BOOL);
    $hide         = optional_param('hide', 0, PARAM_INT);
    $show         = optional_param('show', 0, PARAM_INT);
    $idnumber     = optional_param('idnumber', '', PARAM_RAW);
    $sectionid    = optional_param('sectionid', 0, PARAM_INT);
    $section      = optional_param('section', 0, PARAM_INT);
    $move         = optional_param('move', 0, PARAM_INT);
    $marker       = optional_param('marker',-1 , PARAM_INT);
    $switchrole   = optional_param('switchrole',-1, PARAM_INT); // Deprecated, use course/switchrole.php instead.
    $modchooser   = optional_param('modchooser', -1, PARAM_BOOL);
    $return       = optional_param('return', 0, PARAM_LOCALURL);
    $dgs_mode     = optional_param('mode',-1 , PARAM_INT);//mode <> -1 then is feedback
    $fet_act      = optional_param('act', '', PARAM_TEXT);
    $from_mode = optional_param('from_mode', 0, PARAM_INT);

    if($from_mode == '1') {
        $sql = "SELECT
                    mdl_course_modules.id
                FROM
                    mdl_course_modules mdl_course_modules
                JOIN mdl_feedback_completed mdl_feedback_completed ON mdl_feedback_completed.feedback = mdl_course_modules.instance
                WHERE
                    mdl_course_modules.course = ?
                AND mdl_course_modules.module = 7
                AND mdl_course_modules.visible = 1
                AND mdl_feedback_completed.userid = ?";

        $check_feedback = $DB->get_records_sql_ng($sql, array($id,$USER->id));
        
        if(empty($check_feedback)){
            $sql = "SELECT id FROM mdl_course_modules WHERE course = ? and visible = 1 and module = 7";
            $feedback_id = $DB->get_records_sql_ng($sql, array($id));

            $feedback_url = 'http://elearning.taipei/elearn/show_feedback_link.php?id='.$feedback_id[0]->id.'&mode=1';
            echo "<script>window.open('".$feedback_url."', 'feedback', config='height=100,width=500');</script>";
        }
    }


    //查資料-----------------------------
    $params = array();
    if (!empty($name)) {
        $params = array('shortname' => $name);
    } else if (!empty($idnumber)) {
        $params = array('idnumber' => $idnumber);
    } else if (!empty($id)) {
        $params = array('id' => $id);
    }else {
        print_error('unspecifycourseid', 'error');
    }

    $course = $DB->get_record('course', $params, '*', MUST_EXIST);

    $expirationDate=($course->startdate <= time()&& time() < $course->enddate+60*60*24)?true:false;//看看這們課是否在保存期限內，是則TRUE，否則FALSE

    if($expirationDate){
        if($course->visible == '1'){
            $expirationDate = true;
        } else {
            $expirationDate = false;
        }
    }


    // ======================如果過期了就直接導轉 (這個功能暫時停用了) ===============================
    // 
    // if(!$expirationDate)
    // {
    //     if(isset($_SERVER['HTTP_REFERER']))
    //         redirect($_SERVER['HTTP_REFERER']);//如果不在上課時間之內，直接切回上一頁
    //     else
    //         redirect('/elearn/course');
    // }
    //
    // ===============================================================================================


    if(($USER->id==21 || preg_match("/guest/",$USER->username))) {
        if(!chkCourseGuest($id)) {
            redirect('/elearn/courseinfo/index.php?courseid='.$id);
        }
    }
    // if(chkCourseLock($id) && ) { //導轉驗證密碼
    //     //redirect('/elearn/enrol/index.php?id='.$id);
    // }
    else
    { //註冊
        // if($expirationDate && $fet_act=="reg" && !Course_Sign($id, $USER->id))
        // {
        //     if(!chkCourseLock($id)) //查詢:報名此課程是否需要密碼 (不用則註冊並直接轉跳)
        //     {
        //         autoEnrolCourse($id, $USER->id);
        //         redirect('/elearn/course/regSucceed.php/?courseid='.$id); //移轉到報名成功
        //     }
        //     else //要密碼則轉跳到輸入頁
        //     {
        //         redirect('/elearn/enrol/index.php?id='.$id);
        //     }//-----------------------------------------
            
        // }

        /* 
            使用者(非遊客)：
               |=>註冊過了=> end:那甚麼都跟你沒關係，你可以放心瀏覽，繼續跑view
               |
               |=>還沒註冊=>時間外 => end:回選課中心
                         |
                         |=>時間內=> 不註冊=> end:不註冊就算了，繼續跑view
                                 |
                                 |=> 要註冊=> 不需選課密碼=> end:註冊，然後轉到regSucceed.php
                                          |
                                          |=> 需要選課密碼=> end:轉到 /elearn/enrol/index.php
            */
        //修正具有可看到隱藏課程權限的人可以跳過--Posboss-2021-0204
        $coursecontext = \context_course::instance($course->id); //20210204
        //if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {   //20210204

            if(!Course_Sign2($id, $USER->id))
            { 
                if($expirationDate)
                {
                    if($fet_act=="reg") //想要註冊
                    {

                        if (isExistPocket($id)){
                            echo '<script>';
                            echo 'alert("該課程已放到選課口袋，請到選課口袋進行選課。");';
                            echo 'location.href = "/elearn/courseinfo/index.php?courseid='.addslashes($id).'"';
                            echo '</script>';
                            exit;
                        }
                          

                        if(!chkCourseLock($id)) //查詢:報名此課程是否需要密碼 (不用則註冊並直接轉跳)
                        {
                            autoEnrolCourse($id, $USER->id);
                            redirect('/elearn/course/regSucceed.php/?courseid='.$id); //移轉到報名成功
                        }
                        else //要密碼則轉跳到輸入頁
                            {redirect('/elearn/enrol/index.php?id='.$id);}
                    }
                }
                else//還沒註冊過，而且現在也已經超過了上課時間的人
                {
                    echo '<script>';
                    echo 'alert("該課程未開放無法註冊課程");';
                    echo 'location.href = "http://elearning.taipei/elearn/course/index.php"';
                    echo '</script>';
                    exit;
                    // redirect('/elearn/course');//回到選課中心
                }
            }
        //}  //20210204


    }
//  Display Course Outline
    $URL=$pre_url=$_SERVER['HTTP_REFERER'];    //讀取前一頁透過連結過來的網址

    $showoutline = $_GET['outlineDisplay'];
    // if($_SERVER["REMOTE_ADDR"]!="") {
    //     if(strstr($URL,'elearn/course/index.php') || strstr($URL,'elearn/courserecord/index.php') || $URL == ($CFG->wwwroot."/") ){
    //         redirect('/elearn/courseinfo/index.php/?courseid='.$id);
    //     }
    // }


    $urlparams = array('id' => $course->id);

    // Sectionid should get priority over section number
    if ($sectionid) {
        $section = $DB->get_field('course_sections', 'section', array('id' => $sectionid, 'course' => $course->id), MUST_EXIST);
    }
    if ($section) {
        $urlparams['section'] = $section;
    }
    $PAGE->set_url('/course/view.php', $urlparams); // Defined here to avoid notices on errors etc
    ///fet-dgs 增加新按鈕客製js
    $PAGE->requires->js('/lib/jquery/fet_course_view.js', true);
    // Prevent caching of this page to stop confusion when changing page after making AJAX changes
    $PAGE->set_cacheable(false);
    ///dgs
    //if (!has_capability('moodle/course:view', $coursecontext)) {   //2021-07-23 加入管理員判斷不寫入history資料表
    insertUpdateHistory($USER->id, $course->id, 0); //fet-dgs 註冊課程寫入history資料表
    //}
    updateQuizGrade($course->id, $USER->id);//更新history.quizgrade
    updateHistoryComplete($USER->id, $course->id);//更新history.timecomplete
    context_helper::preload_course($course->id);
    $context = context_course::instance($course->id, MUST_EXIST);

    // Remove any switched roles before checking login
    if ($switchrole == 0 && confirm_sesskey()) {
        role_switch($switchrole, $context);
    }

    require_login($course);

    // Switchrole - sanity check in cost-order...
    $reset_user_allowed_editing = false;
    if ($switchrole > 0 && confirm_sesskey() &&
        has_capability('elearn/role:switchroles', $context)) {
        // is this role assignable in this context?
        // inquiring minds want to know...
        $aroles = get_switchable_roles($context);
        if (is_array($aroles) && isset($aroles[$switchrole])) {
            role_switch($switchrole, $context);
            // Double check that this role is allowed here
            require_login($course);
        }
        // reset course page state - this prevents some weird problems ;-)
        $USER->activitycopy = false;
        $USER->activitycopycourse = NULL;
        unset($USER->activitycopyname);
        unset($SESSION->modform);
        $USER->editing = 0;
        $reset_user_allowed_editing = true;
    }

    //If course is hosted on an external server, redirect to corresponding
    //url with appropriate authentication attached as parameter
    if (file_exists($CFG->dirroot .'/course/externservercourse.php')) {
        include $CFG->dirroot .'/course/externservercourse.php';
        if (function_exists('extern_server_course')) {
            if ($extern_url = extern_server_course($course)) {
                redirect($extern_url);
            }
        }
    }

    require_once($CFG->dirroot.'/calendar/lib.php');    /// This is after login because it needs $USER

    // Must set layout before gettting section info. See MDL-47555.
    $PAGE->set_pagelayout('course');

    if ($section and $section > 0) {

        // Get section details and check it exists.
        $modinfo = get_fast_modinfo($course);
        $coursesections = $modinfo->get_section_info($section, MUST_EXIST);

        // Check user is allowed to see it.
        if (!$coursesections->uservisible) {
            // Note: We actually already know they don't have this capability
            // or uservisible would have been true; this is just to get the
            // correct error message shown.
            require_capability('moodle/course:viewhiddensections', $context);
        }
    }

    // Fix course format if it is no longer installed
    $course->format = course_get_format($course)->get_format();

    $PAGE->set_pagetype('course-view-' . $course->format);
    $PAGE->set_other_editing_capability('moodle/course:update');
    $PAGE->set_other_editing_capability('moodle/course:manageactivities');
    $PAGE->set_other_editing_capability('moodle/course:activityvisibility');
    if (course_format_uses_sections($course->format)) {
        $PAGE->set_other_editing_capability('moodle/course:sectionvisibility');
        $PAGE->set_other_editing_capability('moodle/course:movesections');
    }

    // Preload course format renderer before output starts.
    // This is a little hacky but necessary since
    // format.php is not included until after output starts
    if (file_exists($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php')) {
        require_once($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php');
        if (class_exists('format_'.$course->format.'_renderer')) {
            // call get_renderer only if renderer is defined in format plugin
            // otherwise an exception would be thrown
            $PAGE->get_renderer('format_'. $course->format);
        }
    }

    if ($reset_user_allowed_editing) {
        // ugly hack
        unset($PAGE->_user_allowed_editing);
    }

    if (!isset($USER->editing)) {
        $USER->editing = 0;
    }
    if ($PAGE->user_allowed_editing()) {
        if (($edit == 1) and confirm_sesskey()) {
            $USER->editing = 1;
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else if (!empty($return)) {
                redirect($CFG->wwwroot . $return);
            } else {
                $url = new moodle_url($PAGE->url, array('notifyeditingon' => 1));
                redirect($url);
            }
        } else if (($edit == 0) and confirm_sesskey()) {
            $USER->editing = 0;
            if(!empty($USER->activitycopy) && $USER->activitycopycourse == $course->id) {
                $USER->activitycopy       = false;
                $USER->activitycopycourse = NULL;
            }
            // Redirect to site root if Editing is toggled on frontpage
            if ($course->id == SITEID) {
                redirect($CFG->wwwroot .'/?redirect=0');
            } else if (!empty($return)) {
                redirect($CFG->wwwroot . $return);
            } else {
                redirect($PAGE->url);
            }
        }
        if (($modchooser == 1) && confirm_sesskey()) {
            set_user_preference('usemodchooser', $modchooser);
        } else if (($modchooser == 0) && confirm_sesskey()) {
            set_user_preference('usemodchooser', $modchooser);
        }

        if (has_capability('moodle/course:sectionvisibility', $context)) {
            if ($hide && confirm_sesskey()) {
                set_section_visible($course->id, $hide, '0');
                redirect($PAGE->url);
            }

            if ($show && confirm_sesskey()) {
                set_section_visible($course->id, $show, '1');
                redirect($PAGE->url);
            }
        }

        if (!empty($section) && !empty($move) &&
                has_capability('moodle/course:movesections', $context) && confirm_sesskey()) {
            $destsection = $section + $move;
            if (move_section_to($course, $section, $destsection)) {
                if ($course->id == SITEID) {
                    redirect($CFG->wwwroot . '/?redirect=0');
                } else {
                    redirect(course_get_url($course));
                }
            } else {
                echo $OUTPUT->notification('An error occurred while moving a section');
            }
        }
    } else {
        $USER->editing = 0;
    }

    $SESSION->fromdiscussion = $PAGE->url->out(false);


    if ($course->id == SITEID) {
        // This course is not a real course.
        redirect($CFG->wwwroot .'/');
    }

    $completion = new completion_info($course);
    if ($completion->is_enabled()) {
        $PAGE->requires->string_for_js('completion-title-manual-y', 'completion');
        $PAGE->requires->string_for_js('completion-title-manual-n', 'completion');
        $PAGE->requires->string_for_js('completion-alt-manual-y', 'completion');
        $PAGE->requires->string_for_js('completion-alt-manual-n', 'completion');

        $PAGE->requires->js_init_call('M.core_completion.init');
        
    }

    // We are currently keeping the button here from 1.x to help new teachers figure out
    // what to do, even though the link also appears in the course admin block.  It also
    // means you can back out of a situation where you removed the admin block. :)
    if ($PAGE->user_allowed_editing()) {        
        $buttons = $OUTPUT->edit_button($PAGE->url);
        $PAGE->set_button($buttons);
        
    }
    // If viewing a section, make the title more specific
    if ($section and $section > 0 and course_format_uses_sections($course->format)) {
        $sectionname = get_string('sectionname', "format_$course->format");

        $sectiontitle = get_section_name($course, $section);
        $PAGE->set_title(get_string('coursesectiontitle', 'moodle', array('course' => $course->fullname, 'sectiontitle' => $sectiontitle, 'sectionname' => $sectionname)));
    } else {
        $PAGE->set_title(get_string('coursetitle', 'moodle', array('course' => $course->fullname)));
    }

    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    if ($completion->is_enabled()) {
        // This value tracks whether there has been a dynamic change to the page.
        // It is used so that if a user does this - (a) set some tickmarks, (b)
        // go to another page, (c) clicks Back button - the page will
        // automatically reload. Otherwise it would start with the wrong tick
        // values.
        echo html_writer::start_tag('form', array('action'=>'.', 'method'=>'get'));
        echo html_writer::start_tag('div');
        echo html_writer::empty_tag('input', array('type'=>'hidden', 'id'=>'completion_dynamic_change', 'name'=>'completion_dynamic_change', 'value'=>'0'));
        echo html_writer::end_tag('div');
        
        echo html_writer::end_tag('form');
    }
                    
    // Sam : 修改當編輯的時候可以看到行動版，一班模式看不到
    if(!$USER->editing){

    echo "
    <script type='text/javascript'>
    $(function() { ";
      $sql = "SELECT a.id
            FROM mdl_course_modules a 
            JOIN mdl_fet_module_setting b on b.mid = a.id
            WHERE b.ismobile = 1 and a.course = ". $course->id ."";
    $rs2 = $DB->get_recordset_sql($sql);
    $lineNum = 1;
    foreach ($rs2 as $column) {
        echo "$('#module-$column->id').hide();";

    }
    echo "      });
    </script>";
    }
        

    ///dgs 判斷套裝課程
    if($course->format!='hvpack')
    {
        // Course wrapper start.
        echo html_writer::start_tag('div', array('class'=>'course-content'));
        //from blocks course_status
        
            $getTimes = optional_param('t', 1, PARAM_INT);
            $script_html = 
            "<script>
            var isRun = $getTimes;
            if(isRun>0) {
                $(document).ready(function() {
                    setTimeout(getTime, $getTimes*3000);
                });
            }
             
            function getTime() {
                $.get('$CFG->wwwroot/service_api.php?fnc=getUserCourseSecondsHistory&uid=$USER->id&cid=$COURSE->id', function(data) {
                    $('#span_times_n').html(data);
                });
            }
            </script>";
            $sql = "SELECT mfcd.hours, mfcd.nodecompleterate, mfcd.timecompleterate FROM {fet_course_data} mfcd WHERE mfcd.courseid=:cid ";
            $data_obj = $DB->get_record_sql($sql, array("cid"=> $COURSE->id));
            $getHours = $getNodecompleterate = $getTimecompleterate = 0;
            if($data_obj) {
                $getHours = $data_obj->hours;
                $getNodecompleterate = $data_obj->nodecompleterate;
                $getTimecompleterate = $data_obj->timecompleterate;
            }
            $font_style = "style='font-size:20px;font-weight:bolder;color:#CC6666'";
            $line = "<font $font_style>完成條件為:閱讀時間達".($getHours*60*($getTimecompleterate/100))."分鐘以上</font><br>";
            if($getNodecompleterate>0) {
                if($getNodecompleterate>100) {
                    $getNodecompleterate = 100;
                }
                #$s_fRate = "";
                $fRate = queryNodefinishRate($USER->id, $COURSE->id);
                $s_fRate = "(已完成:$fRate%)";
                $line2 = "<font $font_style>課程節點閱讀需達".$getNodecompleterate."% $s_fRate</font><br>";
            }
            //測驗分數 沒有題目就pass
            $sql = "SELECT id FROM {course_modules} WHERE course=:course and module = (SELECT v.id FROM mdl_modules v WHERE v.name='quiz') AND instance NOT IN (
                    SELECT
                        quizid
                    FROM
                        mdl_fet_pre_quiz
                    WHERE
                        pre_quiz = '1'
                ) ";
            $data = $DB->get_record_sql($sql, array("course"=>$COURSE->id));
            $quId = 0;
            if($data->id) {
                $quId = $data->id;
            }
            
                $sql = "SELECT mi.gradepass, mi.grademax 
                FROM {grade_items} mi
                WHERE mi.courseid = :courseid
                    AND mi.itemmodule = 'quiz' 
                    AND mi.iteminstance NOT IN (
                        SELECT
                            quizid
                        FROM
                            {fet_pre_quiz}
                        WHERE
                            pre_quiz = '1'
                    )";
            // } else {
            //     $sql = "SELECT mi.gradepass, mi.grademax 
            //     FROM {grade_items} mi
            //     WHERE mi.courseid = :courseid
            //         AND mi.itemmodule = 'quiz' ";
            // }
            
            $data = $DB->get_record_sql($sql, array("courseid"=>$COURSE->id));
            $q_sum = updateQuizGrade($COURSE->id, $USER->id, 1/*value:q=1 查詢*/);
            if(!$data) {
                $str_num = "0";
                $quizgrade="";
            }
            else {
                $str_num = ceil($data->gradepass);
                if($q_sum>0) {
                    $quizgrade="測驗分數 : <a href='/elearn/mod/quiz/view.php?id=$quId'>$q_sum</a><br>";
                }
                else {
                    $quizgrade="測驗分數 : <a href='/elearn/mod/quiz/view.php?id=$quId'>未完成</a><br>";
                }
            }
            $line3 = "";
            if($str_num>0) {
                $line3 = "<font $font_style>測驗分數達$str_num 分以上</font><br>";
            }
            $line4 = "";
            //feedback
            $sql = "SELECT id FROM {course_modules} WHERE course=:course and module = (SELECT v.id FROM mdl_modules v WHERE v.name='feedback')";
            $data = $DB->get_record_sql($sql, array("course"=>$COURSE->id));
            $fbId = 0;
            if($data->id) {
                $fbId = $data->id;
            }
            $feedback = "";
            if(checkfeedback($COURSE->id)) {
                $feedback = feedbackIsComplete($USER->id, $COURSE->id)==true?"已完成":"<a href='/elearn/mod/feedback/view.php?id=$fbId'>未完成</a>";
                $line4 = "<font $font_style>問卷需完成</font><br>";
                $feedback = "問卷 : $feedback<br>";
            }
            // else {
            //     $feedback = feedbackIsComplete($USER->id, $COURSE->id)==true?"已完成":"<a href=''>未完成</a>";
            // }
            $certhour = queryCourseCertHour($COURSE->id);
            $sql = "SELECT mfch.certhour, mfch.gothours, mfch.quizgrade, ifnull(mfch.timetotalstudy, 0)+ ifnull(fah.ts, 0) timetotalstudy
                    , mfch.timecomplete, mfch.uploadstatus 
                        FROM {fet_course_history} mfch 
                        LEFT OUTER JOIN mdl_fet_artificial_hour fah
                    ON mfch.userid = fah.userid 
                    AND mfch.courseid = fah.courseid
                    WHERE mfch.courseid = :courseid AND mfch.userid = :userid";
            $data = $DB->get_record_sql($sql, array("courseid"=> $COURSE->id, "userid"=> $USER->id));
            if($data->uploadstatus==1) {
                $uploadstatus = "認證時數是否已上傳(終身學習) : 已上傳";
            }
            else {
                $uploadstatus = "認證時數是否已上傳(終身學習) : 未上傳";
            }

            $quiz_html = '';
            
            $sql = sprintf("select count(1) cnt from mdl_role_assignments where userid = %s and roleid in (2,19,20,21,22,23,24,28)",$USER->id);
            $checkIsTeacher = array();
            $checkIsTeacher = $DB->get_records_sql_ng($sql);

            if($checkIsTeacher[0]->cnt > 0){
                $sql = sprintf("SELECT quizid FROM mdl_fet_pre_quiz WHERE courseid = %s AND pre_quiz = 1",$COURSE->id);
                $quizid = array();
                $quizid = $DB->get_records_sql_ng($sql);

                if(!empty($quizid)){
                    $quiz_html = '<br><a href="http://elearning.taipei/elearn/course/quiz_compare_export.php?quiz='.$quizid[0]->quizid.'&cid='.$COURSE->id.'">下載前後測對照表</a>';
                }
            }
            

            if($USER->id != 21) {
echo <<<EOF
<div class="content"><br><br>
<div class="summary"></div>
<ul class="section img-text">
    <li class="activity feedback modtype_feedback " id="module-91">
        <div>
            <div class="mod-indent-outer">
            <div class="mod-indent"></div>
            <div><div class="activityinstance">
                <span class="instancename">$line$line2$line3$line4
                修課時間 : <span id='span_times_n'><img src='http://elearning.taipei/mpage/system/application/assets/new/images/hourglass.svg' alt='修課時間'></span><br>
                $quizgrade
                $feedback
                認證時數 : $certhour<br>
                $uploadstatus
                $quiz_html
                </span>
            </div>
        </div></div></div>
    </li>
</ul>
</div>
$script_html
EOF;
}
        // }
        //from blocks course_status end
        // make sure that section 0 exists (this function will create one if it is missing)
        course_create_sections_if_missing($course, 0);

        // get information about course modules and existing module types
        // format.php in course formats may rely on presence of these variables
        $modinfo = get_fast_modinfo($course);
        

        $modnames = get_module_types_names();
        $modnamesplural = get_module_types_names(true);
        $modnamesused = $modinfo->get_used_module_names();
        $mods = $modinfo->get_cms();
        
        
        
        $sections = $modinfo->get_section_info_all();

         
        // CAUTION, hacky fundamental variable defintion to follow!
        // Note that because of the way course fromats are constructed though
        // inclusion we pass parameters around this way..
        $displaysection = $section;

        // Include the actual course format.
        require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
        // Content wrapper end.

        echo html_writer::end_tag('div');
    }
    else
    {
        if($dgs_mode==-1) {
            //引入套裝課程
            require($CFG->dirroot .'/course/format/'. $course->format .'/format.php');
        }
        else {
            //引入問卷
            require($CFG->dirroot .'/course/format/'. $course->format .'/feedback.php');
        }
    }
    //dgs 判斷套裝課程 end

    // Trigger course viewed event.
    // We don't trust $context here. Course format inclusion above executes in the global space. We can't assume
    // anything after that point.
    course_view(context_course::instance($course->id), $section);

    //dgs 論壇文章討論區
    // 該門課程最新的三則回文
    // $coursedata = getCourseData($course->id);

    // foreach ($coursedata as $key => $value){
    //     $coursedata = $coursedata[$key];
    // }

    // echo html_writer::start_tag('div');
    // echo html_writer::start_tag('fieldset');
    // echo html_writer::tag('legend', '<img src="http://elearning.taipei/mpage/system/application/assets/new/images/article.png" alt="圖">');
   
    
    // if(is_null($coursedata->articleid))
    // {
            // echo html_writer::tag('big', '本課程未設定課程論壇討論區');
            // echo "</br>";
    // }else{

    //         //------------------
    //         $dbhost = '210.69.61.109';
    //         $dbuser = 'root';
    //         $dbpass = 'fet12345';
    //         $dbname = 'ultrax';
    //         $conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
    //         mysql_query("SET NAMES 'utf8'");
    //         mysql_select_db($dbname);
    //         //------------------
    //         $sql = "select *
    //                 from pre_forum_post
    //                 WHERE tid = " .$coursedata->articleid . "
    //                 ORDER BY pid DESC
    //                 LIMIT 3
    //                 ;";
    //         $result = mysql_query($sql) or die('MySQL query error');
    //         $lineNum = 1;

    //         while($row = mysql_fetch_array($result)){
    //             $page = ($row['position']/10);
    //             $page = ceil($page);
    //             $article = preg_replace("/\[[^\]]+\]/", "", $row['message']);
 
    //             echo html_writer::tag('lastli', html_writer::tag('lastli', $lineNum.': &nbsp; <a href="http://elearning.taipei/sso/to.php?sitelink=forum_post&tid='. $row['tid'].'&page='.$page.'" class="link-as-button">'. $article.'</a>'));
    //             echo "</br>";
    //             $lineNum++ ;
    //         }
    //         mysql_close($conn);
    // }

    // echo html_writer::end_tag('fieldset');
    // echo html_writer::end_tag('div');
    //
    // echo html_writer::start_tag('div');
    // echo html_writer::start_tag('fieldset');
    // echo html_writer::tag('legend', '');
    // echo html_writer::end_tag('fieldset');
    // echo html_writer::end_tag('div');

    //dgs 新增大數據推薦課程
    echo html_writer::start_tag('div');
    echo html_writer::start_tag('fieldset');
    //echo html_writer::tag('br');
    echo html_writer::tag('legend', '<br><img src="http://elearning.taipei/mpage/system/application/assets/new/images/course.png" alt="推薦課程">');
      $sql = "SELECT *
                FROM mdl_course
                WHERE id in (
                SELECT propose_course_id
                FROM mdl_fet_propose_course
                WHERE course_id = ". $course->id ."
                )";
        $rs2 = $DB->get_recordset_sql($sql);
        $lineNum = 1;
        foreach ($rs2 as $column) {
            $fullname = $column->fullname;
            // if(strlen($column->fullname)>120) {
                // $fullname = utf8_substr($column->fullname, 0, 120)."...";
            // }      
            echo html_writer::tag('lastli', html_writer::tag('lastli', $lineNum.': &nbsp;  <a href="/elearn/courseinfo/index.php?courseid='.$column->id.'" class="link-as-button">'.$fullname.'</a>')); 
            echo "</br>";
            $lineNum++ ;
        }
    echo html_writer::end_tag('fieldset');
    echo html_writer::end_tag('div');
    
    //dgs 大數據推薦課程end

    // Include course AJAX
    include_course_ajax($course, $modnamesused);

    echo $OUTPUT->footer();
