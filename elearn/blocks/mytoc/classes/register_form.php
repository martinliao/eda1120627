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

class block_mytoc_register_form extends moodleform {
    function definition () {

        $mform = $this->_form;
        $userdata = $this->_customdata['data'];

        $mform->addElement('html', get_string('register_description', 'block_mytoc'));

        $mform->addElement('text', 'name', get_string('userfullname', 'block_mytoc'), array('maxlength' => 25));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('missinguserfullname', 'block_mytoc'), 'required', null, 'client');

        $mform->addElement('text', 'en_name', get_string('userename', 'block_mytoc'), array('maxlength' => 128));
        $mform->setType('en_name', PARAM_TEXT);
        //$mform->addRule('en_name', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');

        $genderarray = array();
        $genderarray[] = $mform->createElement('static', 'genderinfo');
        $genderarray[] = $mform->createElement('radio', 'gender', '', get_string('male', 'block_mytoc'), 'M');
        $genderarray[] = $mform->createElement('radio', 'gender', '', get_string('female', 'block_mytoc'), 'F');
        $mform->addGroup($genderarray, 'gendergroup', get_string('gender', 'block_mytoc'), array(' ', ' '), false);
        $mform->setDefault('gender', 'M');

        /*
        $mform->addElement('hidden', 'idno');
        $mform->setType('idno', PARAM_INT);

        $mform->addElement('text', 'idno2', get_string('idno'));
        $mform->setType('idno2', PARAM_TEXT);
        $mform->disabledIf('idno2', 'idno', 'neq', '');
        */
        $elem = $mform->addElement('text', 'idno2', get_string('idno', 'block_mytoc'));
        $elem->freeze();

        $mform->addElement('date_selector', 'birthday', get_string('birthday', 'block_mytoc'));
        $mform->setDefault('birthday', time());

        $mform->addElement('text', 'office_email', get_string('office_email', 'block_mytoc'));
        $mform->setType('office_email', PARAM_EMAIL);
        $mform->addRule('office_email', get_string('missingofficeemail', 'block_mytoc'), 'required', null, 'client');

        $mform->addElement('text', 'email', get_string('email', 'block_mytoc'));
        $mform->setType('email', PARAM_EMAIL);

        $mform->addElement('text', 'co_empdb_poftel', get_string('co_empdb_poftel', 'block_mytoc'), array('maxlength' => 64));
        $mform->setType('co_empdb_poftel', PARAM_TEXT);
        $mform->addRule('co_empdb_poftel', get_string('missingco_empdb_poftel', 'block_mytoc'), 'required', null, 'client');

        $mform->addElement('text', 'office_fax', get_string('office_fax', 'block_mytoc'), array('maxlength' => 100));
        $mform->setType('office_fax', PARAM_TEXT);

        $mform->addElement('text', 'cellphone', get_string('cellphone', 'block_mytoc'), array('maxlength' => 100));
        $mform->setType('cellphone', PARAM_TEXT);
/*
        $mform->addElement('static', 'bureau_help', '', get_string('bureau_help', 'block_mytoc'));
        $mform->addElement('text', 'bureau_id', get_string('bureau_name', 'block_mytoc'), array('maxlength' => 10, 'size' => 50));
        $mform->setType('bureau_id', PARAM_TEXT);
        $mform->addRule('bureau_id', get_string('maximumchars', '', 10), 'maxlength', 10, 'client');
        $mform->addRule('bureau_id', get_string('missingbureau_name', 'block_mytoc'), 'required', null, 'client');
*/
        $bureauarray = array();
        $bureauarray[] = $mform->createElement('static', 'bureauinfo');
        $bureauarray[] = $mform->createElement('text', 'bureau_id', get_string('bureau_name', 'block_mytoc'));
        $bureauarray[] = $mform->createElement('static', 'bureau_help', '', get_string('bureau_help', 'block_mytoc'));
        $mform->addGroup($bureauarray, 'bureaugroup', get_string('bureau_name', 'block_mytoc'), array(' ', ' '), false);
        $mform->setType('bureau_id', PARAM_TEXT);
        $mform->addRule('bureaugroup', get_string('missingbureau_name', 'block_mytoc'), 'required', null, 'client');
        $bureaugrules = array();
        $bureaugrules['bureau_id'][] = array(get_string('missingbureau_name', 'block_mytoc'), 'required', null, 'client');
        $mform->addGroupRule('bureaugroup', $bureaugrules);

        $mform->addElement('text', 'out_gov_name', get_string('out_gov_name', 'block_mytoc'), array('maxlength' => 128));
        $mform->setType('out_gov_name', PARAM_TEXT);       

        $options = array('20'=>'國(初)中以下', '30'=>'高中(職)', '40'=>'專科', '50'=>'大學', '60'=>'碩士', '70'=>'博士');
        $mform->addElement('select', 'education', get_string('education', 'block_mytoc'), $options);

        $options = array('1'=>'首長', '2'=>'副首長', '3'=>'一級主管', '4'=>'二級主管', '5'=>'三級主管', '6'=>'四級以下主管', '7'=>'一級副主管', '8'=>'二級副主管', '9'=>'三級副主管', 'A'=>'幕僚長', 'B'=>'副幕僚長');
        $mform->addElement('select', 'supervisor_id', get_string('supervisor', 'block_mytoc'), $options);     
        $mform->setDefault('supervisor_id', '6');

        /*
        $mform->addElement('text', 'job_level_id', get_string('job_level', 'block_mytoc'), array('maxlength' => 100, 'size' => 50));
        $mform->setType('job_level_id', PARAM_TEXT);
        $mform->addRule('job_level_id', get_string('missingjoblevel', 'block_mytoc'), 'required', null, 'client');
        */
        $levelarray = array();
        $levelarray[] = $mform->createElement('static', 'joblevelinfo');
        $levelarray[] = $mform->createElement('text', 'job_level_id', get_string('job_level', 'block_mytoc'));
        $url = new moodle_url('/blocks/mytoc/template/現支官職等代碼表.pdf');
        $levelarray[] = $mform->createElement('static', 'job_level_help', '', get_string('job_level_help', 'block_mytoc', $url->out(false)));
        $mform->addGroup($levelarray, 'levelgroup', get_string('job_level', 'block_mytoc'), array(' ', ' '), false);
        $mform->setType('job_level_id', PARAM_TEXT);
        $mform->addRule('levelgroup', get_string('missingjoblevel', 'block_mytoc'), 'required', null, 'client');
        $levelrules = array();
        $levelrules['job_level_id'][] = array(get_string('missingjoblevel', 'block_mytoc'), 'required', null, 'client');
        $mform->addGroupRule('levelgroup', $levelrules);

        /*
        $mform->addElement('text', 'job_title_name', get_string('job_title', 'block_mytoc'), array('maxlength' => 100));
        $mform->setType('job_title_name', PARAM_TEXT);
        $mform->addRule('job_title_name', get_string('missingjob_title', 'block_mytoc'), 'required', null, 'client');
        */
        $titlearray = array();
        $titlearray[] = $mform->createElement('static', 'jobtitleinfo');
        $titlearray[] = $mform->createElement('text', 'job_title_id', get_string('job_title', 'block_mytoc'));
        $url = new moodle_url('/blocks/mytoc/template/職稱代碼表.pdf');
        $titlearray[] = $mform->createElement('static', 'job_title_help', '', get_string('job_title_help', 'block_mytoc', $url->out(false)));
        $mform->addGroup($titlearray, 'titlegroup', get_string('job_title', 'block_mytoc'), array(' ', ' '), false);
        $mform->setType('job_title_id', PARAM_TEXT);
        $mform->addRule('titlegroup', get_string('missingjobtitle', 'block_mytoc'), 'required', null, 'client');
        $titlerules = array();
        $titlerules['job_title_id'][] = array(get_string('missingjobtitle', 'block_mytoc'), 'required', null, 'client');
        $mform->addGroupRule('titlegroup', $titlerules);

        $options = array('01'=>'簡任主管', '02'=>'簡任非主管', '03'=>'荐任主管', '04'=>'荐任非主管', '05'=>'委任主管', '06'=>'委任非主管', '07'=>'警察消防主管', '08'=>'警察消防非主管', '09'=>'約聘僱人員', '10'=>'技工工友', '11'=>'其他');
        $mform->addElement('select', 'job_distinguish', get_string('job_distinguish', 'block_mytoc'), $options);
        $mform->setDefault('job_distinguish', '11');

        $options = array('1' =>'否', '0'=>'是');
        $mform->addElement('select', 'departure', get_string('departure', 'block_mytoc'), $options);
        $mform->addElement('select', 'retirement', get_string('retirement', 'block_mytoc'), $options);
        $mform->addElement('select', 'showretirement', get_string('showretirement', 'block_mytoc'), $options);

        $mform->addElement('static', 'errormsg', '', '');

        $this->add_action_buttons(true, get_string('submit'));
        $mform->addElement('hidden', 'idno');
        $mform->setType('idno', PARAM_TEXT);
        $mform->addElement('hidden', 'uid');
        $mform->setType('uid', PARAM_INT);
        $mform->addElement('hidden', 'cid');
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'year');
        $mform->setType('year', PARAM_INT);

        $this->set_data($userdata);
        
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $row = array();
        //csv format
        $row[0] = 1;
        $row[1] = s($data['name']);
        $row[2] = strtoupper(trim($data['idno2']));
        $row[3] = $data['gender'];
        $row[4] = date('Y/m/d', $data['birthday']);
        $row[5] = $data['email'];
        $row[6] = $data['office_email'];
        $row[7] = $data['bureau_id'];
        $row[8] = $data['out_gov_name'];
        $row[9] = $data['education'];
        $row[10] = $data['job_distinguish'];
        $row[11] = $data['co_empdb_poftel'];
        $row[12] = $data['office_fax'];
        $row[13] = $data['job_title_id'];
        $row[14] = $data['cellphone'];
        //form format
        $row[15] = $data['en_name'];
        $row[16] = $data['supervisor_id'];
        $row[17] = $data['job_level_id'];
        $row[18] = $data['departure'];
        $row[19] = $data['retirement'];
        $row[20] = $data['showretirement'];
        
        $status = block_mytoc_import_verify('enrol', $row);
        if(!empty($status)){
            $errors['errormsg'] = $status;
        }

        return $errors;
    }

}