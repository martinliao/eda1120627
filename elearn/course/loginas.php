<?php
// Allows a teacher/admin to login as another user (in stealth mode).

require_once('../config.php');
require_once('lib.php');

$id       = optional_param('id', SITEID, PARAM_INT);   // course id
$redirect = optional_param('redirect', 0, PARAM_BOOL);

$url = new moodle_url('/course/loginas.php', array('id'=>$id));
$PAGE->set_url($url);

// Reset user back to their real self if needed, for security reasons you need to log out and log in again.
if (\core\session\manager::is_loggedinas()) {
    require_sesskey();
    require_logout();

    setcookie('moodle_loginasuser', '', 0, '/mpage/'); 

    // We can not set wanted URL here because the session is closed.
    redirect(new moodle_url($url, array('redirect'=>1)));
}

if ($redirect) {
    if ($id and $id != SITEID) {
        $SESSION->wantsurl = "$CFG->wwwroot/course/view.php?id=".$id;
    } else {
        $SESSION->wantsurl = "$CFG->wwwroot/";
    }

    redirect(get_login_url());
}

// Try log in as this user.
$userid = required_param('user', PARAM_INT);

require_sesskey();
$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

// User must be logged in.

$systemcontext = context_system::instance();
$coursecontext = context_course::instance($course->id);

require_login();

if (has_capability('moodle/user:loginas', $systemcontext)) {
    if (is_siteadmin($userid)) {
        print_error('nologinas');
    }
    $context = $systemcontext;
    $PAGE->set_context($context);
} else {
    require_login($course);
    require_capability('moodle/user:loginas', $coursecontext);
    if (is_siteadmin($userid)) {
        print_error('nologinas');
    }
    if (!is_enrolled($coursecontext, $userid)) {
        print_error('usernotincourse');
    }
    $context = $coursecontext;

    // Check if course has SEPARATEGROUPS and user is part of that group.
    if (groups_get_course_groupmode($course) == SEPARATEGROUPS &&
            !has_capability('moodle/site:accessallgroups', $context)) {
        $samegroup = false;
        if ($groups = groups_get_all_groups($course->id, $USER->id)) {
            foreach ($groups as $group) {
                if (groups_is_member($group->id, $userid)) {
                    $samegroup = true;
                    break;
                }
            }
        }
        if (!$samegroup) {
            print_error('nologinas');
        }
    }
}

// Login as this user and return to course home page.
\core\session\manager::loginas($userid, $context);
$newfullname = fullname($USER, true);

$strloginas    = get_string('loginas');
$strloggedinas = get_string('loggedinas', '', $newfullname);

$PAGE->set_title($strloggedinas);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strloggedinas);

if(isset($_SESSION['REALUSER'])){
    setcookie('moodle_loginasuser', '', '', '/mpage/'); 
    $loginasdata = new stdClass();
    $text = '<div class="logininfo">[<a href="'.$CFG->wwwroot.'/course/loginas.php?id=1&amp;sesskey='.sesskey().'" title="登入身分是">'.$_SESSION['REALUSER']->firstname.'</a>] 您以<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$USER->id.'" title="瀏覽個人資料">'.$USER->firstname.'</a>登入 (<a href="'.$CFG->wwwroot.'/login/logout.php?sesskey='.sesskey().'">登出</a>)</div>';
    $loginasdata->content = htmlentities($text);
    $loginasdata->loginastime = time();
    setcookie('moodle_loginasuser', json_encode($loginasdata), time()+7200, '/mpage/'); 
}

notice($strloggedinas, "$CFG->wwwroot/courserecord/index.php");

// notice($strloggedinas, "$CFG->wwwroot/course/view.php?id=$course->id");
