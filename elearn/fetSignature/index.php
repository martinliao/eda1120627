<?php 

require_once("../config.php");
require('export.php');
require('helper/helper.php');
require($CFG->dirroot."/helper/page_helper.php");

// 權限檢查
require_login();
$userRole = checkSignatureRole();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	// 刪除
	if (isset($_POST['action'])){
		if ($_POST['action'] == 'delete' && !empty($_POST['deleteId']) && in_array(30, $userRole)){

			$result = deleteSignature($_POST['deleteId']);

			if ($result){
				$message = "刪除成功";
				echo "<script type=\"text/javascript\">
						alert('$message');
						location.href='/elearn/fetSignature/index.php';
					  </script>";				
			}
		}elseif ($_POST['action'] == 'uploadFile' && !empty($_POST['signature_id'])){

			// 上傳附檔
			if (!empty($_FILES["attach_file"]["name"])){
				uploadSginatureAttachFile($_POST['signature_id'], $_FILES["attach_file"]);
			}
		}
	}	
}
$queryData = [
	'speech_sdate', 'speech_edate', 'title', 'authorize_user', 'other_limit', 'other_limit_not_empty', 'is_paid', 'is_share', 'is_download'
];

$tmp = [];

foreach ($queryData as $field){
	if (isset($_GET[$field])){
		$tmp[$field] = $_GET[$field];
	}else{
		$tmp[$field] = null;
	}
}

$queryData = $tmp;
unset($tmp);

$page = (isset($_GET['page']) && $_GET['page']>0) ? $_GET['page'] : 1;

if (isset($_GET['action'])){
	if ($_GET['action'] == 'exportList'){
		$signatures = getSignatures($queryData, null, false, false);
		exportList($signatures, $queryData);
	}elseif ($_GET['action'] == 'exportSignatures'){
		$signatures = getSignatures($queryData, null, false, false);

		exportSignatures($signatures, $queryData);
	}	
}


$page_count = 10;
$signatures = getSignatures($queryData, $page, false, true, $page_count);
$dataCount = getSignatures($queryData, $page, true, false, $page_count);
$page_helper = new page_helper();

$isPaids = [
	 0 =>'無償', 1 => '有償'
];

$isShares = [
	 0 =>'不分享', 1 => '分享'
];

$isDownloads = [
	0 =>'不開放下載', 1 => '開放下載'
];

function getSignatures($queryData, $page, $reutrnCount = false, $paginate = true, $page_count = 10)
{
	global $DB;
	
	$where = [];

	if (!empty($queryData['speech_sdate']) && !empty($queryData['speech_edate'])){
		$where[] = '(speech_date BETWEEN :speech_sdate AND :speech_edate)';
		$params['speech_sdate'] = str_replace('/', '-', $queryData['speech_sdate']);
		$params['speech_edate'] = str_replace('/', '-', $queryData['speech_edate']);
	}

	if (!empty($queryData['title'])){
		$where[] = '(title LIKE :title)';
		$params['title'] = '%'.$queryData['title'].'%';
	}

	if (!empty($queryData['authorize_user'])){
		$where[] = '(authorize_user LIKE :authorize_user)';
		$params['authorize_user'] = '%'.$queryData['authorize_user'].'%';
	}

	if (!empty($queryData['other_limit'])){
		$where[] = '(other_limit LIKE :other_limit)';
		$params['other_limit'] = '%'.$queryData['other_limit'].'%';
	}

	if (!empty($queryData['other_limit_not_empty'])){
		$where[] = "(other_limit <> '' AND other_limit is not null)";
	}
	
	if (!empty($queryData['is_paid']) || $queryData['is_paid'] === '0'){
		$where[] = '(is_paid = :is_paid)';
		$params['is_paid'] = $queryData['is_paid'];
	}

	if (!empty($queryData['is_share']) || $queryData['is_share'] === '0'){
		$where[] = '(is_share = :is_share)';
		$params['is_share'] = $queryData['is_share'];
	}
	
	if (!empty($queryData['is_download']) || $queryData['is_download'] === '0'){
		$where[] = '(is_download = :is_download)';
		$params['is_download'] = $queryData['is_download'];
	}

	if (!empty($where)){
		$where = 'WHERE '.join(' AND ', $where);
	}else{
		$where = '';
	}

	if ($reutrnCount){
		$sql = "SELECT count(*) as countnum FROM mdl_fet_signature {$where}";

		return (int)$DB->get_record_sql($sql, $params)->countnum;
	}

	$sql = "SELECT * FROM mdl_fet_signature {$where} ORDER BY id desc";

	if ($paginate){
		$sql .= " LIMIT ".(($page-1)*$page_count).", ".$page_count;
	}

	return $DB->get_records_sql($sql, $params);
}


function deleteSignature($signatureId){
	global $DB;
	$sql = "DELETE FROM mdl_fet_signature WHERE id = :id";
	$params = ['id' => $signatureId];

	return $DB->execute($sql, $params);
}

function uploadSginatureAttachFile($signatureId, $file){
	global $DB;

	$signature = getSignature($signatureId);

	$fileType = ['image/png', 'image/jpeg', 'application/pdf'];

	if (!in_array($fileType, $file['type'])){
		if (empty($signature->attach_file) && empty($signature->signature)){
			$filename = $signatureId.'_'.date("YmdHis").'.'.pathinfo($file["name"], PATHINFO_EXTENSION);
			$file_path = "/www/userupload/elearn/signature/attach_file/".$filename;
			$upload_status = move_uploaded_file($file["tmp_name"], $file_path);
			if ($upload_status == false){
				$message = '上傳失敗';
			}else{
				$sql = "UPDATE mdl_fet_signature 
						SET attach_file = :attach_file, upload_date = :upload_date 
						WHERE id = :id ";	
				$params = ['id' => $signatureId, 'attach_file' => $filename, 'upload_date' => date('Y-m-d')];
				$result = $DB->execute($sql, $params);			
				if ($result){
					$message = '上傳成功';
				}else{
					$message = '上傳失敗';
				}
			}				
		}else{
			$message = '該序號已上傳附檔';
		}		
	}else{
		$message = "檔案格式錯誤，限定上傳JPG、PNG或PDF檔。";
	}

	echo "<script type=\"text/javascript\">
			alert('$message');
			location.href='/elearn/fetSignature/index.php';
		  </script>";	

}

function getSignature($id)
{
	global $DB;
	$sql = "SELECT * FROM mdl_fet_signature WHERE id = :id";
	$params = ['id' => $id];
	return $DB->get_record_sql($sql, $params);
}

// moodle 網頁模板
$site = get_site();

$PAGE->set_title($SITE->fullname. ': 電子簽章');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($site->fullname);

$isPaids = ['' => '', "0" => '無償', "1" =>'有償'];
$isShares = ['' => '', "0" => '不分享', "1" =>'分享'];
$isDownloads = ['' => '', "0" => '不開放下載', "1" =>'開放下載'];

echo $OUTPUT->header();
?>

<style type="text/css">
	table{
		border: 1px solid #000;
	}

	td{
		border: 1px solid #000;
	}
	th{
		border: 1px solid #000;
	}	
</style>
<div>
	<form id="search_form" onsubmit="return searchChek();">
		<div>
			<div style="float:left">
				<label>演講日期(起)</label>
				<input type="date" name="speech_sdate" value="<?=$queryData['speech_sdate']?>" onkeypress="return false">		
			</div>
			<div style="margin-left: 250px;">
				<label>演講日期(迄)</label>
				<input type="date" name="speech_edate" value="<?=$queryData['speech_edate']?>" onkeypress="return false">					
			</div>
		</div>

		<div>
			<div style="float:left">
				<label>演講題目</label>
				<input type="text" name="title" value="<?=$queryData['title']?>">		
			</div>
			<div style="margin-left: 250px;">
				<label>授權人</label>
				<input type="text" name="authorize_user" value="<?=$queryData['authorize_user']?>">					
			</div>
		</div>

		<div>
			<div style="float:left">
				<label>授權限制</label>
				<input type="text" name="other_limit" value="<?=$queryData['other_limit']?>">		
			</div>
			<div style="margin-left: 250px;height: 65px">
				<label>&nbsp</label>
				<input type="checkbox" name="other_limit_not_empty" value="1" <?=($queryData['other_limit_not_empty'] == 1)? 'checked':''?>>
				<label>僅查詢有輸入文字的簽名</label>				
			</div>
		</div>		

		<div>
			<div style="float:left;">
				<label>有償無償</label>
				<select name='is_paid'>
					<?php foreach($isPaids as $code => $isPaid): ?>
					<option value="<?=$code?>" <?=(strval($code) === $queryData['is_paid'])? 'selected' : '' ?> ><?=$isPaid?></option>
					<?php endforeach ?>
				</select>
			</div>
			<div style="margin-left: 250px;">
				<div style="float:left;">
					<label>同意分享</label>
					<select name='is_share'>
						<?php foreach($isShares as $code => $isShare): ?>
						<option value="<?=$code?>" <?=(strval($code) === $queryData['is_share'])? 'selected' : '' ?> ><?=$isShare?></option>
						<?php endforeach ?>
					</select>				
				</div>
				<div style="margin-left: 250px;">
					<label>開放下載</label>
					<select name='is_download'>
						<?php foreach($isDownloads as $code => $isDownload): ?>
						<option value="<?=$code?>" <?=(strval($code) === $queryData['is_download'])? 'selected' : '' ?> ><?=$isDownload?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
		</div>
		<div>
			<button type="submit" name="action" value="search">搜尋</button>
			<a href="/elearn/fetSignature/create.php"><button type="button">新增</button></a>
			<button type="submit" name="action" value="exportList">匯出授權列表</button>
			<button type="submit" name="action" value="exportSignatures">匯出簽名檔</button>
		</div>		
	</form>
</div>

<div>
	<table border="1" style="width:100%">
		<thead style="background-color:#D3E4ED">
			<th>序號</th>
			<th>演講日期</th>
			<th>演講題目</th>
			<th>授權人</th>
			<th>有償/無償</th>
			<th>同不同意分享</th>
			<th>同不同意開放下載</th>
			<th>其他授權限制</th>
			<th>同意授權簽名</th>
		</thead>
		<tbody>
			<?php foreach($signatures as $signature): ?>
			<tr>
				<td><?=$signature->id?></td>
				<td><?=$signature->speech_date?></td>
				<td><?=$signature->title?></td>
				<td><?=$signature->authorize_user?></td>
				<td><?=$isPaids[$signature->is_paid]?></td>
				<td><?=$isShares[$signature->is_share]?></td>
				<td><?=$isDownloads[$signature->is_download]?></td>
				<td><?=$signature->other_limit?></td>
				<td>
					<a href="/elearn/fetSignature/signature.php?id=<?=$signature->tmp_key?>"><button type="button" <?=(!empty($signature->signature) ? 'disabled' : '')?> 
					<?=!empty($signature->attach_file) ? 'disabled' : ''?> >簽名上傳</button></a>
					<a href="/elearn/fetSignature/download.php?id=<?=$signature->id?>"><button type="button" <?=(!empty($signature->signature) ? '' : 'disabled')?> >下載</button></a>
					<a href="/elearn/fetSignature/edit.php?id=<?=$signature->id?>"><button type="button">編輯</button></a>

					<a href="/elearn/fetSignature/editLog.php?id=<?=$signature->id?>"><button type="button">編輯紀錄</button></a>
					<? if (in_array(30, $userRole)): ?>
					<button type="button" onclick="deleteSignature(<?=$signature->id?>)">刪除</button>
					<? endif ?>
					<?php if(empty($signature->attach_file) && empty($signature->signature)): ?>
						<form method="POST" enctype="multipart/form-data" onsubmit="return uploadAttachFileCheck(<?=$signature->id?>)">
							<input id="attach_file<?=$signature->id?>" type="file" name="attach_file" accept=".jpg, .png, .pdf">
							<input type="hidden" name="action" value="uploadFile">
							<input type="hidden" name="signature_id" value="<?=$signature->id?>">
							<button type="submit">上傳附檔</button>
						</form>
					<?php endif ?>
					<?php if(!empty($signature->attach_file)): ?>
						<a href="/elearn/fetSignature/downloadAttachFile.php?id=<?=$signature->id?>" download><button type="button">下載附檔</button></a>
					<?php endif ?>
				</td>				
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>


<form method="POST" id="deleteForm">
	<input type="hidden" name="action" value="delete">
	<input type="hidden" name="deleteId">	
</form>

<script type="text/javascript">
	function deleteSignature(id)
	{
		if (confirm('確定刪除序號 ' + id + ' 的電子簽章?')){
			document.getElementsByName('deleteId')[0].value = id;
			document.getElementById('deleteForm').submit();			
		}
	}

	function uploadAttachFileCheck(id)
	{
		if (document.getElementById('attach_file' + id.toString()).value == ''){
			alert('請選擇檔案後再進行上傳');
			return false;
		}else{
			if (confirm('確定要上傳這個檔案到序號 ' + id.toString() + ' 嗎?')){
				return true;
			}			
		}
		return false;
	}

	function searchChek()
	{
		if ($('#search_form').context.activeElement.value == 'exportSignatures'){
			$('#search_form').attr('target','_blank');
		}else{
			$('#search_form').attr('target','');
		}
		return true;
	}
</script>
<?php 
	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$page_helper->paginate($dataCount, $page, $url, $page_count);
	echo $OUTPUT->footer(); 
?>
