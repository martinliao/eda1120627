<?php
/**
 * Tab panel for the My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
global $USER;
$top = array();

$userid = optional_param('id', $USER->id, PARAM_INT);
if($userid != $USER->id){
    $urlpath = '/user/profile.php?id='.$userid;
}else{
    $urlpath = '/my/index.php';
}

$url = new moodle_url($urlpath, array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_ENROLCOURSES_VIEW));
$top[] = new tabobject(BLOCK_MYTOC_ENROLCOURSES_VIEW, $url, get_string('enrolcourses', 'block_mytoc'));

$url = new moodle_url($urlpath, array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_PHY_PAYMENT_VIEW));
//$top[] = new tabobject(BLOCK_MYTOC_PHY_PAYMENT_VIEW, $url, get_string('phy_payment', 'block_mytoc'));

$url = new moodle_url($urlpath, array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_PHY_COURSES_VIEW));
$top[] = new tabobject(BLOCK_MYTOC_PHY_COURSES_VIEW, $url, get_string('phy_courses', 'block_mytoc'));

$url = new moodle_url($urlpath, array('sesskey' => sesskey(), 'mytoctab'=>BLOCK_MYTOC_PHY_OTHER_CITY_VIEW));
$top[] = new tabobject(BLOCK_MYTOC_PHY_OTHER_CITY_VIEW, $url, get_string('phy_ohtercity', 'block_mytoc'));
