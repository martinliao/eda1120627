<?php 
require("../config.php");

global $CFG;
require($CFG->dirroot."/lib/phpword/bootstrap.php");
require($CFG->dirroot."/helper/file_helper.php");
require('helper/helper.php');


// 權限檢查
require_login();
checkSignatureRole();

$signatureId = isset($_GET['id']) ? $_GET['id'] : null;
$signature = getSignature($signatureId);

if (empty($signature->signature)){
	die();
}

$language = ($signature->language == 'en') ? '_en' : '';
if ($signature->is_paid == 0){
	$file = $CFG->dirroot."/fetSignature/template/free{$language}.docx";
}elseif($signature->is_paid == 1){
	$file = $CFG->dirroot."/fetSignature/template/paid{$language}.docx";
}

$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file); 

$templateProcessor->setImageValue('signature1', ['path' => $signature->signature, 'width' => 200]);
$templateProcessor->setImageValue('signature2', ['path' => $signature->signature, 'width' => 200]);

$speechDate = new DateTime($signature->speech_date);
$uploadDate = new DateTime($signature->upload_date);

if ($signature->language == 'en'){

	$englishMonth = [
		'01' => 'January', 
		'02' => 'February', 
		'03' => 'March', 
		'04' => 'April', 
		'05' => 'May', 
		'06' => 'June',
		'07' => 'July', 
		'08' => 'August', 
		'09' => 'September', 
		'10' => 'October', 
		'11' => 'November', 
		'12' => 'December'
	];

	$templateProcessor->setValue('speechDate', $speechDate->format('d-m-Y'));

	$templateProcessor->setValue('uploadYear', $uploadDate->format('Y'));
	$templateProcessor->setValue('uploadMonth', $englishMonth[$uploadDate->format('m')]);
	$templateProcessor->setValue('uploadDay', $uploadDate->format('d').'th');
}else{
	$templateProcessor->setValue('speechYear', (int)$speechDate->format('Y') - 1911);
	$templateProcessor->setValue('speechMonth', $speechDate->format('m'));
	$templateProcessor->setValue('speechDay', $speechDate->format('d'));	

	$templateProcessor->setValue('uploadYear', (int)$uploadDate->format('Y') - 1911);
	$templateProcessor->setValue('uploadMonth', $uploadDate->format('m'));
	$templateProcessor->setValue('uploadDay', $uploadDate->format('d'));	
}

$templateProcessor->setValue('location', $signature->location);
$templateProcessor->setValue('title', $signature->title);

$isShare = ($signature->is_share == 1) ? '☑' : '☐';
$notShare = ($signature->is_share != 1) ? '☑' : '☐';
if($signature->is_download === null){
	$isDownload = '☐';
	$notDownload = '☐';
}else {
	$isDownload = ($signature->is_download == 1) ? '☑' : '☐';
	$notDownload = ($signature->is_download != 1) ? '☑' : '☐';
}

$templateProcessor->setValue('isShare', $isShare);
$templateProcessor->setValue('notShare', $notShare);
$templateProcessor->setValue('isDownload', $isDownload);
$templateProcessor->setValue('notDownload', $notDownload);

$templateProcessor->setValue('other_limit', $signature->other_limit);
$templateProcessor->setValue('idno', $signature->idno);



if ($signature->is_paid == 1){
	$templateProcessor->setValue('paid_fee', $signature->paid_fee);
}

$save_path = "/www/userupload/elearn/signature/tmp/".$signatureId.'_'.date('YmdHis').".docx";
$templateProcessor->saveAs($save_path);  

$file_helper = new file_helper();
$file_helper->download($save_path);

function getSignature($id)
{
	global $DB;
	$sql = "SELECT * FROM mdl_fet_signature WHERE id = :id";
	$params = ['id' => $id];
	return $DB->get_record_sql($sql, $params);
}
