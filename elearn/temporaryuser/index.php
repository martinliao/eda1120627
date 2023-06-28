<?php


require_once("../config.php");
require_once($CFG->dirroot. '/fetpayment/lib.php');
require_once($CFG->dirroot. '/functions.php');
require($CFG->dirroot."/helper/page_helper.php");
require($CFG->dirroot."/helper/fetpayment_helper.php");
require($CFG->dirroot."/temporaryuser/lib/elearningUser.php");

require_login();


ini_set('display_errors','1');
error_reporting(E_ALL);

$page_helper = new page_helper();
$fetpayment_helper = new fetpayment_helper();

// error_reporting(E_ALL);

$site = get_site();

$PAGE->set_title($SITE->fullname. ': e大帳號管理');
$PAGE->set_context(context_system::instance());

$PAGE->set_pagelayout('standard');
$PAGE->set_heading($site->fullname);


$html_content = $ahref_html = $after_html = "";
if (isCsSt() === false){
    throw new Exception('此功能僅有客服人員可以使用');
};
// 限制訪客存取
if($USER->username=='guest'|| $USER->id==21){
	throw new Exception('不允許訪客存取，請登入');
}

$queryString = filterQueryString($_SERVER['QUERY_STRING']);

$isETAdmin = isETAdmin() === false ? false : true;

if ($_SERVER['REQUEST_METHOD'] === "POST"){
    $id = required_param('tempusrid', PARAM_INT);
    if ($_POST['action'] == "edit"){
        $newTempUser = [
            'name' => required_param('name', PARAM_TEXT),
            'passwd' => required_param('passwd', PARAM_TEXT),
        ];

        if (empty($newTempUser['name'])){
            die('姓名不可為空');
        }

        $result = ElearningUser::update($id, $newTempUser, false);
        if ($result['status']){
            echo "<script>alert('編輯成功');location=\"/elearn/temporaryuser/index.php?".$queryString."\";</script>";
        }else{
            echo "<script>alert('".$result['message']."');location=\"/elearn/temporaryuser/index.php?".$queryString."\";</script>";            
        }
        die;        
    }elseif ($_POST['action'] == "delete"){
        if ($isETAdmin){
            deleteTempUser($id);
            echo "<script>alert('刪除成功');location=\"/elearn/temporaryuser/index.php?".$queryString."\";</script>";
        }else{
            echo "<script>alert('權限不足');location=\"/elearn/temporaryuser/index.php?".$queryString."\";</script>";            
        }
        die();
    }
}

$queryData = array();
$queryData['tmpusr_name'] = optional_param('tmpusr_name', null, PARAM_TEXT); 
$queryData['tmpusr_idno'] = optional_param('tmpusr_idno', null, PARAM_TEXT);
$queryData['sign_date_start'] = optional_param('sign_date_start', null, PARAM_TEXT); 
$queryData['sign_date_end'] = optional_param('sign_date_end', null, PARAM_TEXT); 
$queryData['page'] = optional_param('page', 1, PARAM_INT);

$tmpusers = getTempUsers($queryData);
$tmpuserCount = getTempUsers($queryData, true);

echo $OUTPUT->header();

function isETAdmin()
{
    global $DB, $USER;
    $sql = "select *
        FROM mdl_role_assignments rs
        JOIN mdl_role r on rs.roleid = r.id 
        where r.id = 11 AND userid = :id";
    $params = ['id' => $USER->id];
    return $DB->get_record_sql($sql, $params); 
}

function isCsSt()
{
    global $DB, $USER;
    $sql = "select *
        FROM mdl_role_assignments rs
        JOIN mdl_role r on rs.roleid = r.id 
        where r.id = 12 AND userid = :id";
    $params = ['id' => $USER->id];
    return $DB->get_record_sql($sql, $params);
}

function getTempUsers($queryData, $getCount = false)
{
    global $DB;
    $params = array();
    if ($getCount){
        $select = "SELECT count(*) count";
    }else{
        $select = "SELECT *";
    }

    $sql = $select." FROM mdl_temporary_user";
    $where = array();

    if (!empty($queryData['tmpusr_name'])){
        $params['tmpusr_name'] = "%".$queryData['tmpusr_name']."%";
        $where[] = "name LIKE :tmpusr_name";
    }

    if (!empty($queryData['tmpusr_idno'])){
        $params['tmpusr_idno'] = "%".$queryData['tmpusr_idno']."%";
        $where[] = "idno LIKE :tmpusr_idno";
    }   

    if (!empty($queryData['sign_date_start'])){
        $params['sign_date_start'] = $queryData['sign_date_start'];
        $where[] = "sign_date >= :sign_date_start";
    }   
 
    if (!empty($queryData['sign_date_end'])){
        $params['sign_date_end'] = $queryData['sign_date_end'];
        $where[] = "sign_date <= :sign_date_end";
    } 

    if (count($where) > 0){
        $sql .= ' WHERE '.implode(" AND ", $where);
    }

    if ($getCount){
        return $DB->get_record_sql($sql, $params);
    }else{
        return $DB->get_records_sql($sql, $params, ($queryData['page']-1) * 10 , 10);
    }
    
}

function outputHtml($content)
{
    return htmlspecialchars($content, ENT_HTML5|ENT_QUOTES);
}

function hiddenIdno($idno)
{
    return substr($idno, 0, 3)."****".substr($idno, -3, 3);
}

function filterQueryString($queryString)
{
    $newString = [];
    parse_str($queryString, $parseString);
    foreach ($parseString as $key => $value){
        $newString[] = $key."=".urlencode($value);
    }
    return implode("&",$newString);
}

function deleteTempUser($id){
    global $DB;

    try {
        $transaction = $DB->start_delegated_transaction();

        $sql = "SELECT mdl_fet_pid.uid, mdl_temporary_user.idno
        FROM mdl_temporary_user
        JOIN mdl_fet_pid ON mdl_fet_pid.idno = mdl_temporary_user.idno
        WHERE mdl_temporary_user.id = :id";
        $params = ['id' => $id];
        $tmpUser = $DB->get_record_sql($sql, $params);

        // 移除 mdl_temporary_user [臨時帳號資料]
        $sql = "DELETE FROM mdl_temporary_user WHERE id = :id";
        $params = ['id' => $id];
        $DB->execute($sql, $params);

        // 若無 fet_pid 資料代表未登入過不會有以下資料
        if ($tmpUser !== false){
            // 移除 mdl_fet_pid [使用者身分證資料]
            $sql = "DELETE FROM mdl_fet_pid WHERE idno = :idno";
            $params = ['idno' => $tmpUser->idno]; 
            $DB->execute($sql, $params);

            // 移除 mdl_user [使用者資料]
            $sql = "DELETE FROM mdl_user WHERE id = :id";
            $params = ['id' => $tmpUser->uid];
            $DB->execute($sql, $params);

            // 移除 [學習紀錄] [上傳紀錄] 包含今年以前的紀錄
            for($year = 2016; $year<=intVal(date('Y'));$year++){
                $yearString = $year;
                if ($yearString == intVal(date('Y'))){
                    $yearString = "";
                }else{
                    $yearString = "_".$yearString;
                }

                // 移除 mdl_user_enrolments [課程報名紀錄] 2017, 2018 無表格跳過
                if (!in_array($year, [2017, 2018])){
                    $sql = "DELETE FROM mdl_user_enrolments".$yearString." WHERE userid = :userid";
                    $params = ['userid' => $tmpUser->uid];          
                    $DB->execute($sql, $params);                 
                }
                
                // 移除 mdl_fet_course_history [學習紀錄]
                $sql = "DELETE FROM mdl_fet_course_history".$yearString." WHERE userid = :userid";
                $params = ['userid' => $tmpUser->uid];
                $DB->execute($sql, $params);   
                // 移除 mdl_fet_upload_hours [上傳紀錄]
                $sql = "DELETE FROM mdl_fet_upload_hours".$yearString." WHERE uid = :uid";
                $params = ['uid' => $tmpUser->uid];  
                $DB->execute($sql, $params);
                
            }
            
            // 移除 mdl_feedback_completed [問卷填答註記]
            $sql = "DELETE FROM mdl_feedback_completed WHERE userid = :userid";
            $params = ['userid' => $tmpUser->uid];  
            $DB->execute($sql, $params);

            // 移除 mdl_quiz_attempts [測驗資料]
            $sql = "DELETE FROM mdl_quiz_attempts WHERE userid = :userid";
            $params = ['userid' => $tmpUser->uid];  
            $DB->execute($sql, $params);

            // 移除 mdl_quiz_grades [測驗資料]
            $sql = "DELETE FROM mdl_quiz_grades WHERE userid = :userid";
            $params = ['userid' => $tmpUser->uid];  
            $DB->execute($sql, $params);
            
            // 移除 mdl_role_assignments [權限資料]
            $sql = "DELETE FROM mdl_role_assignments WHERE userid = :userid";
            $params = ['userid' => $tmpUser->uid];  
            $DB->execute($sql, $params);
        }

        $transaction->allow_commit();

    } catch (Exception $e) {
        //extra cleanup steps
        $transaction->rollback($e); // rethrows exception
    }
}

$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

?>

<style>
.dialog{
    width:400px;
    background-color:#FFF;
    position: fixed;
    top:30%;
    left:40%;
    border: 3px solid #2894FF;
    z-index:9001;
}    
.dialog_title{
    padding: 5px;
    background-color: #00AEAE;
    height: 11%;
    max-height: 20px;
}
.dialog_content{
    padding: 15px;
    padding-left: 25px;
    height: 70%;
    word-break: break-word;
    /* max-height: 400px; */
    /* overflow: scroll;     */
}
.dialog_footer{
    padding: 10px;
    height: 20%;
}
</style>

<form>
<div>
    <label for="tmpusr_name">
        姓名：
        <input type="text" id="tmpusr_name" name="tmpusr_name" value="<?=outputHtml($queryData['tmpusr_name'])?>">
    </label>
    <label for="tmpusr_idno">
        身分證字號：
        <input type="text" id="tmpusr_idno" name="tmpusr_idno" value="<?=outputHtml($queryData['tmpusr_idno'])?>">
    </label>    
    <label>
        註冊日期：
        <input type="date" name="sign_date_start" value="<?=outputHtml($queryData['sign_date_start'])?>">
        到
        <input type="date" name="sign_date_end" value="<?=outputHtml($queryData['sign_date_end'])?>">
        日
        <input type="submit" value="搜尋">
    </label>   
<div>
</form>

<table border="1" style="width:100%;text-align:center;">
    <thead>
        <th>序號</th>
        <th>姓名</th>
        <th>帳號</th>
        <th>註冊時間</th>
        <th>功能</th>
    </thead>
    <tbody>
        <?php foreach($tmpusers as $tmpuser): ?>
        <tr>
            <td><?=outputHtml($tmpuser->id)?></td>
            <td><?=$tmpuser->name?></td>
            <td><?=outputHtml(hiddenIdno($tmpuser->idno))?></td>
            <td><?=outputHtml($tmpuser->sign_date)?></td>
            <td>
                <button type="button" name="action" value="edit" class="btn" onclick="getTempUser(<?=$tmpuser->id?>)">編輯</button>
                <?php if ($isETAdmin): ?>
                <button type="button" name="action" value="delete" class="btn" onclick="deleteTempUser(<?=$tmpuser->id?>)">刪除</button>
                <?php endif ?>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<form method="POST" id="deleteTmpUserForm">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="tempusrid" value="">
</form>
<hr/>


<div id="dialog" class="dialog" style="display:none;">
<form method="POST" onsubmit="return checkUpadteUserForm(this)">
  <div class="dialog_title">編輯e大帳號</div>
  <div id="dialog_content" class="dialog_content">

  </div>
  <div class="dialog_footer">
    <input type="hidden" name="action" value="edit">
    <button type="submit" class="btn btn-primary" id="tempUsersubmit">送出</button>
    <button type="button" class="btn btn-primary" onclick="editCanel()">取消</button>   
  </div>    
</form>
</div>
 
<div id="dialog-bg" style="height:100%;width:100%; background-color:#000;position: fixed;top:0px;left:0px;opacity:0.5;z-index:9000;display:none">&nbsp</div>

<?php $page_helper->paginate($tmpuserCount->count,$queryData['page'],$url,10); ?>
<!-- 下分頁區塊-->
<?=$OUTPUT->footer()?>

<script type="text/javascript">
    function getTempUser(id){
        $("#dialog").css("display", "");
        $("#dialog-bg").css("display", "");        
        $("#dialog_content").html("取得最新資料中...");        
        $.ajax({
            url : '/elearn/temporaryuser/getTempUser.php',
            data : {
                id: id
            }
        }).done(function(response){

            if (response.tempUser === null){
                $("#dialog_content").html("找不到該學員資訊");  
                $("#tempUsersubmit").attr("disabled", true);      
                return false;
            }

            $("#dialog_content").html("");     
            let id = document.createElement('label');
            id.innerText = "序號：" + response.tempUser.id;
            let idInput = document.createElement('input');
            idInput.name = "tempusrid";
            idInput.value = response.tempUser.id;
            idInput.type = "hidden";
            
            let name = document.createElement('label');
            name.append("姓名：");
            let nameInput = document.createElement('input');
            nameInput.type = "text";
            nameInput.name = "name";
            nameInput.value = response.tempUser.name;
            nameInput.autocomplete = "off";
            nameInput.required = "required";
            name.append(nameInput);

            let email = document.createElement('label');
            if (response.tempUser.email === null){
                email.innerText = "電子信箱：";
            }
            else {
                email.innerText = "電子信箱：" + response.tempUser.email;
            }

            let idno = document.createElement('label');
            idno.innerText = "帳號：" + response.tempUser.idno;

            let passwd = document.createElement('label');
            passwd.append("密碼：");
            let passwdInput = document.createElement('input');
            passwdInput.type = "text";
            passwdInput.name = "passwd";
            passwdInput.autocomplete = "off";
            passwd.append(passwdInput);

            let sign_date = document.createElement('label');
            sign_date.innerText = "註冊時間：" + response.tempUser.sign_date;            

            $("#dialog_content").append(id);
            $("#dialog_content").append(idInput);            
            $("#dialog_content").append(name);
            $("#dialog_content").append(email);
            $("#dialog_content").append(idno);
            $("#dialog_content").append(passwd);
            $("#dialog_content").append(sign_date);
            $("#tempUsersubmit").removeAttr("disabled", true);
        });
    }

    function editCanel(){
        $("#dialog_content").html("");
        $("#tempUsersubmit").attr("disabled", true);
        $("#dialog").css("display", "none");
        $("#dialog-bg").css("display", "none");
    }

    function deleteTempUser(id){
        if (confirm("刪除功能會一併刪除本筆帳號的所有學習紀錄，請問是否確定要刪除？")){
            $("#deleteTmpUserForm input[name=tempusrid]").val(id);
            $("#deleteTmpUserForm").submit();
        }
    }
    
    function checkUpadteUserForm(form)
    {
        let pwCheck = /^[A-Za-z\d]{1,20}$/;
        let passwd = $(form).find("input[name=passwd]").val();

        if (passwd != "" && pwCheck.test(passwd) === false){
            alert("密碼限制只能輸入英文字母及數字");
            return false;
        }
        return true;
    }
</script>