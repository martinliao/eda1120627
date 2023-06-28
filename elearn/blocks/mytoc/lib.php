<?php
/**
 * Lib for the My Table Of Content block.
 *
 * @package    block_mytoc
 * @author     Maria Tan <maria@click-ap.com>
 * @copyright  2023 Click-AP {@link https://www.click-ap.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('BLOCK_MYTOC_ENROLCOURSES_VIEW', '2');
define('BLOCK_MYTOC_PHY_PAYMENT_VIEW', 'P1');
define('BLOCK_MYTOC_PHY_OTHER_CITY_VIEW', 'P2');
define('BLOCK_MYTOC_PHY_COURSES_VIEW', 'P3');
define("KEY_PHY","AAFD3DDSF");

function block_mytoc_get_phy_bureaus($bureau_id){
    
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_othercity.php';
    $url = 'http://172.25.154.98/api_othercity.php';
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'bureau', 'accesskey'=>KEY_PHY));

        $results = curl_exec($ch);
        curl_close($ch);

        //insert phy user
        if(!empty($results)){
            $bureaus = json_decode($results, true);
            
            if(in_array($bureau_id,$bureaus)){
                return true;
            }
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The get PHY user fail...');
    }

    return false;  
}

function block_mytoc_check_phy_user($idno, $needata = false){
    
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_othercity.php'; 
    $url = 'http://172.25.154.98/api_othercity.php';
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'user', 'accesskey'=>KEY_PHY, 'idno' => $idno));
            
        $results = curl_exec($ch);
        curl_close($ch);

        //insert phy user
        if($results != "FALSE"){
            if($needata){
                return $results;
            }
            return true;
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The get PHY user fail...');
    }

    return false;  
}

function block_mytoc_get_phy_user($data, $idno){
    
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_othercity.php';
    $url = 'http://172.25.154.98/api_othercity.php'; 
    try {
        $data = implode(',', $data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'users', 'accesskey'=>KEY_PHY, 'cre_user' => $idno, 'data'=>json_encode($data)));
            
        $results = curl_exec($ch);
        curl_close($ch);

        //insert phy user
        if(!empty($results) && ($results == 'TRUE' || $results == 'CREATE_TRUE')){
            return $results;
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The get PHY user fail...');
    }

    return false;  
}

function block_mytoc_sync_phy_enrol($action, $createuser, $createuser_beaurau, $cid, $idno, $beaurau){
    global $DB;    

    set_time_limit(0);
    raise_memory_limit(MEMORY_EXTRA);
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_othercity.php';
    $url = 'http://172.25.154.98/api_othercity.php'; 
    try {
        
        $tmp = $DB->get_record('fet_phy_other_city', array('id'=>$cid));
        $data = array('cre_user' => $createuser, 'cre_user_beaurau' => $createuser_beaurau, 'seq_no'=>$tmp->seq_no, 'year'=>$tmp->year, 'class_no'=>$tmp->class_no, 'term'=>$tmp->term, 'idno'=>$idno, 'beaurau'=>$beaurau);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'sync', 'accesskey'=>KEY_PHY, 'action' => $action, 'data'=>json_encode($data)));
        
        $results = curl_exec($ch);
        curl_close($ch);

        //insert phy user
        if(!empty($results) && ($results == 'PHYOK')){
            return $results;
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The get PHY user fail...');
    }

    return false;  
}

function block_mytoc_sync_phy_class(){
    global $DB;

    set_time_limit(0);
    raise_memory_limit(MEMORY_EXTRA);

    $year = date('Y')-1911;
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_othercity.php';
    $url = 'http://172.25.154.98/api_othercity.php'; 
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'courses', 'accesskey'=>KEY_PHY, 'year'=>$year));
        //$header = array('Content-Type: application/json');
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            
        $results = curl_exec($ch);
        curl_close($ch);

        //insert phy_other_city
        if(!empty($results)){
            $courses = json_decode($results, true);

            $sql = "SELECT concat(year, '-', term, '-', class_no, '-', seq_no) as id, id as poid FROM {fet_phy_other_city} 
                    WHERE year = :year";
            $org = $DB->get_records_sql_menu($sql, array('year'=>$year));

            $add = array_diff_key($courses, $org);
            $del = array_diff_key($org, $courses);

            if($add){
                foreach ($add as $key => $val){
                    $tmp = explode('-', $key);

                    $data = new stdClass();
                    $data->seq_no            = $courses[$key]['seq_no'];
                    $data->year              = $courses[$key]['year'];
                    $data->term              = $courses[$key]['term'];
                    $data->class_no          = $courses[$key]['class_no'];
                    $data->class_name        = $courses[$key]['class_name'];
                    $data->reason            = $courses[$key]['reason'];
                    $data->start_date        = $courses[$key]['start_date'];
                    $data->end_date          = $courses[$key]['end_date'] + DAYSECS - 1;
                    $data->worker            = !empty($courses[$key]['worker']) ? $courses[$key]['worker'] : "";
                    $data->apply_sdate1      = $courses[$key]['apply_sdate1'];
                    $data->apply_edate1      = $courses[$key]['apply_edate1'] + DAYSECS - 1;
                    $data->apply_sdate2      = !empty($courses[$key]['apply_sdate2']) ? $courses[$key]['apply_sdate2'] : "";
                    $data->apply_edate2      = !empty($courses[$key]['apply_edate2']) ? $courses[$key]['apply_edate2'] + DAYSECS - 1 : "";
                    $data->only_servant      = $courses[$key]['only_servant'];
                    $data->visible           = 1;

                    if($class = $DB->get_record('fet_phy_other_city', array('year'=>$tmp[0], 'term'=>$tmp[1], 'class_no'=>$tmp[2], 'seq_no'=>$tmp[3]))){
                        //update visible
                        $data->id = $class->id;
                        $data->timemodified = time();
                        $DB->update_record('fet_phy_other_city', $data);
                    }else {
                        //insert
                        $data->timecreated = time();
                        $DB->insert_record('fet_phy_other_city', $data);
                    }

                    unset($courses[$key]);
                }
            }

            //update 
            foreach ($courses as $key => $val){
                $tmp = explode('-', $key);

                $data = new stdClass();
                $data->seq_no            = $courses[$key]['seq_no'];
                $data->year              = $courses[$key]['year'];
                $data->term              = $courses[$key]['term'];
                $data->class_no          = $courses[$key]['class_no'];
                $data->class_name        = $courses[$key]['class_name'];
                $data->reason            = $courses[$key]['reason'];
                $data->start_date        = $courses[$key]['start_date'];
                $data->end_date          = $courses[$key]['end_date'] + DAYSECS - 1;
                $data->worker            = !empty($courses[$key]['worker']) ? $courses[$key]['worker'] : "";
                $data->apply_sdate1      = $courses[$key]['apply_sdate1'];
                $data->apply_edate1      = $courses[$key]['apply_edate1'] + DAYSECS - 1;
                $data->apply_sdate2      = !empty($courses[$key]['apply_sdate2']) ? $courses[$key]['apply_sdate2'] : "";
                $data->apply_edate2      = !empty($courses[$key]['apply_edate2']) ? $courses[$key]['apply_edate2'] + DAYSECS - 1 : "";
                $data->only_servant      = $courses[$key]['only_servant'];
                $data->visible           = 1;

                if($class = $DB->get_record('fet_phy_other_city', array('year'=>$tmp[0], 'term'=>$tmp[1], 'class_no'=>$tmp[2], 'seq_no'=>$tmp[3]))){
                    //update visible
                    $data->id = $class->id;
                    $data->timemodified = time();
                    $DB->update_record('fet_phy_other_city', $data);
                }
            }
            
            if($del){
                foreach ($del as $key => $val){
                    $tmp = explode('-', $key);

                    $data = new stdClass();
                    if($class = $DB->get_record('fet_phy_other_city', array('year'=>$tmp[0], 'term'=>$tmp[1], 'class_no'=>$tmp[2], 'seq_no'=>$tmp[3], 'visible'=>1))){
                        $data->id = $class->id;
                        $data->visible = 0;
                        $data->timemodified = time();
                        $DB->update_record('fet_phy_other_city', $data);
                    }
                }
            }
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The Sync PHY other city class service seems fail...');
    }

    return true;  
}

function block_mytoc_import_verify($enrol, $row) {
    if(empty($enrol)){
        return get_string('error_format', 'block_mytoc', get_string('field_enrol', 'block_mytoc'));
    }
    
    if(empty($row[2]) OR !block_mytoc_check_format('idno', $row[2])){
        return get_string('error_format', 'block_mytoc', get_string('field_idno', 'block_mytoc'));              
    }

    if($row[0] == 1){
        if(empty($row[1])){
            return get_string('error_format', 'block_mytoc', get_string('field_firstname', 'block_mytoc'));
        }

        if(empty($row[3]) OR !block_mytoc_check_format('gender', $row[3])){
            return get_string('error_format', 'block_mytoc', get_string('field_gender', 'block_mytoc'));
        }

        if(empty($row[4]) OR !block_mytoc_check_format('date', $row[4])){
            return get_string('error_format', 'block_mytoc', get_string('field_birthday', 'block_mytoc'));
        }

        if(empty($row[6]) OR !filter_var($row[6], FILTER_VALIDATE_EMAIL)){
            return get_string('error_format', 'block_mytoc', get_string('field_email2', 'block_mytoc'));
        }

        if(empty($row[7])){
            return get_string('error_format', 'block_mytoc', get_string('field_bureau', 'block_mytoc'));
        } else if(empty($row[8]) && ($row[7] == "D0004")){
            return get_string('error_format', 'block_mytoc', get_string('out_gov_name', 'block_mytoc'));
        } else if (!empty($row[7]) && !block_mytoc_check_format('bureau', $row[7])){
            return get_string('error_format', 'block_mytoc', get_string('field_bureau', 'block_mytoc'));
        }
        
        if(empty($row[9]) OR !block_mytoc_check_format('edu', $row[9])){
            return get_string('error_format', 'block_mytoc', get_string('field_edu', 'block_mytoc'));
        }

        if(empty($row[10]) OR !block_mytoc_check_format('type', $row[10])){
            return get_string('error_format', 'block_mytoc', get_string('field_type', 'block_mytoc'));
        }

        if(empty($row[11]) OR !block_mytoc_check_format('tel', $row[11])){
            return get_string('error_format', 'block_mytoc', get_string('field_tel', 'block_mytoc'));
        }

        if(empty($row[13])){
            return get_string('error_format', 'block_mytoc', get_string('field_jobtitle', 'block_mytoc'));
        }
    }
    
    if(!empty($row[5]) AND !filter_var($row[5], FILTER_VALIDATE_EMAIL)){
        return get_string('error_format', 'block_mytoc', get_string('field_email', 'block_mytoc'));
    }
    
    if(!empty($row[12]) AND !block_mytoc_check_format('fax', $row[12])){
        return get_string('error_format', 'block_mytoc', get_string('field_fax', 'block_mytoc'));
    }
    if(!empty($row[14]) AND !block_mytoc_check_format('cellphone', $row[14])){
        return get_string('error_format', 'block_mytoc', get_string('field_cellphone', 'block_mytoc'));
    }

    return "";
}

function block_mytoc_check_format($field, $data){
    
    if($field == "idno") {
        // 英文字母與數值對照表
        $alphabetTable = [
            'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16,
            'H' => 17, 'I' => 34, 'J' => 18, 'K' => 19, 'L' => 20, 'M' => 21, 'N' => 22,
            'O' => 35, 'P' => 23, 'Q' => 24, 'R' => 25, 'S' => 26, 'T' => 27, 'U' => 28,
            'V' => 29, 'X' => 30, 'Y' => 31, 'Z' => 33
        ];
        
        // 檢查身份證字號格式
        // ps. 第二碼的例外條件ABCD，在這裡未實作，僅提供需要的人參考，實作方式是A對應10，只取個位數0去加權即可
        // 臺灣地區無戶籍國民、大陸地區人民、港澳居民：
        // 男性使用A、女性使用B
        // 外國人：
        // 男性使用C、女性使用D
        if (!preg_match("/^[A-Z]{1}[12ABCD]{1}[0-9]{8}$/", $data)){
            // ^ 是開始符號
            // $ 是結束符號
            // [] 中括號內是正則條件
            // {} 是要重複執行幾次
            return false; 
        }

        // 切開字串
        $idArray = str_split($data);

        // 英文字母加權數值
        $alphabet = $alphabetTable[$idArray[0]];
        $point = substr($alphabet, 0, 1) * 1 + substr($alphabet, 1, 1) * 9;

        // 數字部分加權數值
        for ($i = 1; $i <= 8; $i++) {
            $point += $idArray[$i] * (9 - $i);
        }
        $point = $point + $idArray[9];

        return $point % 10 == 0 ? true : false;
    }
    else if($field == 'gender'){
        if($data == "F" OR $data == "M"){
            return true;
        }
    }
    else if($field == 'date'){
        $format = 'Y/m/d';
        $d = DateTime::createFromFormat($format, $data);
        return $d && $d->format($format) === $data;
    }
    else if($field == 'bureau') {
        /*
        if(strlen($data)>0 && strlen($data)<40){
            return true;
        }
        */
        if(block_mytoc_get_phy_bureaus($data)){
            return true;
        }
    }
    else if($field == 'edu'){
        $codes = array(20,30,40,50,60,70);
        if(in_array($data, $codes)){
            return true;
        }
    }
    else if($field == 'type'){
        $codes = array(1,2,3,4,5,6,7,8,9,10,11);
        if(in_array($data, $codes)){
            return true;
        }
    }
    else if($field == 'tel' || $field == 'fax'){
        if(preg_match("/^[0-9]{2}-[0-9]{8}$/", $data)) {
            return true;
        }
    }
    else if($field == 'cellphone') {
        if(preg_match("/^[0-9]{4}-[0-9]{3}-[0-9]{3}$/", $data)) {
            return true;
        }
    }

    return false;
}

function block_mytoc_phy_courses($year, $idno, $sortby='', $page=0, $perpage=0){
    global $DB;

    set_time_limit(0);
    raise_memory_limit(MEMORY_EXTRA);
    //$url = 'https://dcsdcourse.taipei.gov.tw/api_courses.php';
    $url = 'http://172.25.154.98/api_courses.php'; 
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('fn'=>'courses', 'accesskey'=>KEY_PHY, 'year' => $year, 'idno' => $idno, 'sort' => $sortby, 'page' => $page, 'perpage' => $perpage));
        
        $results = curl_exec($ch);
        curl_close($ch);

        if(!empty($results)){
            return json_decode($results, true);
        }
    }
    catch(SoapFault $e){
        //var_dump($e);
        error_log($e);
        throw new coding_exception('The get PHY courses fail...');
    }

    return false;  
}
