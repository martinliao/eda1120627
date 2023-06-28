<?php
/**
 * Batch import course enrolment , and user register (eda and phy website).
 *
 * @package    block_mytoc
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.click-ap.com/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class block_mytoc_upload_form extends moodleform {
    function definition () {
        global $CFG;

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];

        $mform->addElement('header', 'settingsheader', get_string('import_csv', 'block_mytoc'));

        /*
        $attributes = array('size' => '10');
        $sel =& $mform->addElement('hierselect', 'year',  get_string('filter-class', 'block_mytoc'), $attributes);
        $sel->setMainOptions($class[0]);
        $sel->setSecOptions($class[1]);
        */
        
        $filepath = $CFG->dirroot. '/blocks/mytoc/template/example.csv';
        if(file_exists($filepath)){
            $fileurl = new moodle_url('/blocks/mytoc/template/example.csv');
            $templatefile = '<font size="4"><a href="'.$fileurl.'">'.get_string('templatefile', 'block_mytoc').'</a></font>';
            //$mform->addElement('static', 'templatefile', get_string('templatefile', 'block_mytoc'), $templatefile);
            //$mform->addHelpButton('templatefile', 'templatefile', 'block_mytoc');

            $mform->addElement('html', get_string('templatefile_help', 'block_mytoc').$templatefile);
        }

        $options = array();
        $options['accepted_types'] = array('.csv');
        $mform->addElement('filepicker', 'csvfile', get_string('file'), null, $options);
        $mform->addRule('csvfile', null, 'required');
        
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('hidden', 'delimiter_name', 'comma');
        
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'block_mytoc'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $mform->addElement('hidden', 'cid', $courseid);

        $this->add_action_buttons(false, get_string('upload'));
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        return $errors;
    }
}