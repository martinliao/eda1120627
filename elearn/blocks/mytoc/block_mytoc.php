<?php
/**
 * My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/mytoc/locallib.php');
require_once($CFG->dirroot.'/blocks/mytoc/lib.php');
class block_mytoc extends block_base {

    /**
     * Init.
     */
    public function init() {
        //$this->title = get_string('pluginname', 'block_mytoc');
        $this->title = '';
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        global $CFG, $DB, $USER, $OUTPUT;

        if (isset($this->content)) {
            return $this->content;
        }

        if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real users
        }

        $userid = optional_param('id', $USER->id, PARAM_INT);

        // Check if the tab to select wasn't passed in the URL, if so see if the user has any preference.
        $tab = optional_param('mytoctab', null, PARAM_TEXT);
        $renderer = $this->page->get_renderer('block_mytoc');

        if ($preference = $DB->get_field('user_preferences', 'value', array('userid' => $userid, 'name' => 'mytoc_tab'))) {
            if(empty($tab)){
                $tab = $preference;
            }
            else if($preference != $tab){
                block_mytoc_update_mytab($tab, $userid);
            }
        }
        else{
            $config = get_config('block_mytoc');
            $tab = $config->defaulttab;
            block_mytoc_update_mytab($tab, $userid);
        }
        
        $this->content = new stdClass();
        $this->content->text = '';
        if($tab == BLOCK_MYTOC_ENROLCOURSES_VIEW){
            //$this->content->text = $renderer->course_overview($tab, $userid);
            redirect(new moodle_url('/courserecord/index.php'));
        }
        else if($tab == BLOCK_MYTOC_PHY_PAYMENT_VIEW){
            //$this->content->text = $renderer->mytoc_phy_payment_list($tab, $userid);
        }
        else if($tab == BLOCK_MYTOC_PHY_OTHER_CITY_VIEW){
            $this->content->text = $renderer->mytoc_phy_other_city_list($tab, $userid);
        }
        else if($tab == BLOCK_MYTOC_PHY_COURSES_VIEW){
            $this->content->text = $renderer->mytoc_phy_courses_list($tab, $userid);
        }
        
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true, 'user-profile' => true);
    }

    /**
     * This block does contain a configuration settings.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}
