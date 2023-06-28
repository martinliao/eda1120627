<?php

require_once("../lib/PHPExcel1.8/PHPExcel.php");
require_once("../lib/PHPExcel1.8/PHPExcel/IOFactory.php");
require_once("../lib/PHPExcel1.8/PHPExcel/Cell.php");
require_once("../lib/PHPExcel1.8/PHPExcel/Cell/DataType.php");

require_once('../lib/tcpdf/tcpdf.php');

function exportList($signatures, $queryData){
	$now = new DateTime();
	$objPHPExcel = new PHPExcel();

	$title = '授權報表';

	if (!empty($queryData['speech_sdate']) && !empty($queryData['speech_edate'])){
		$title = $queryData['speech_sdate'].' ~ '.$queryData['speech_edate'].' '.$title;
	}

	$sheet = $objPHPExcel->setActiveSheetIndex(0);
	$sheet->setCellValue('A1', $title);
	$sheet->mergeCells('A1:I1');

	$sheet->setCellValue('A2', '序號');
	$sheet->setCellValue('B2', '演講日期');
	$sheet->setCellValue('C2', '演講題目');
	$sheet->setCellValue('D2', '演講地點');
	$sheet->setCellValue('E2', '授權人');
	$sheet->setCellValue('F2', '有償/無償');
	$sheet->setCellValue('G2', '同不同意分享');
	$sheet->setCellValue('H2', '同不同意開放下載');
	$sheet->setCellValue('I2', '其他授權限制');

	$signatures = array_values($signatures);
	$row = 2;

	$isPaids = [
		 0 =>'無償', 1 => '有償'
	];

	$isShares = [
		 0 =>'不分享', 1 => '分享'
	];
    
    $isDownloads = [
		 0 =>'不開放下載', 1 => '開放下載'
	];

	foreach ($signatures as $i => $signature){
		$row++;
		$speech_date = new DateTime($signature->speech_date);
		$sheet->setCellValue('A'.$row, $signature->id);
		$sheet->setCellValue('B'.$row, $speech_date->format('Y-m-d'));
		$sheet->setCellValue('C'.$row, $signature->title);
		$sheet->setCellValue('D'.$row, $signature->location);
		$sheet->setCellValue('E'.$row, $signature->authorize_user);

		if ($signature->is_paid !== null){
			$sheet->setCellValue('F'.$row, $isPaids[$signature->is_paid]);
		}
		
		if ($signature->is_share !== null){
			$sheet->setCellValue('G'.$row, $isShares[$signature->is_share]);
		}
        
        if ($signature->is_download !== null){
			$sheet->setCellValue('H'.$row, $isDownloads[$signature->is_download]);
		}

		$sheet->setCellValue('I'.$row, $signature->other_limit);		
	}

	$styleArray = array(
	      'borders' => array(
	          'allborders' => array(
	              'style' => PHPExcel_Style_Border::BORDER_THIN,
	              'color' => array('rgb' => '000000')
	          )
	      )
	  );
	$sheet->getStyle("A2:I".$row)->applyFromArray($styleArray);

	$file_name = $now->format('YmdHis').'.xlsx';
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$file_name.'"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');
	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;	
}


function exportSignatures($signatures)
{
	ini_set('max_execution_time', 300);
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	foreach($signatures as $signature){
		if (empty($signature->signature)) {
			continue;
		}

		if ($signature->is_paid == 1){
			if ($signature->language == 'en'){
				showPaidEn($pdf, $signature);
			}else{
				showPaid($pdf, $signature);
			}
			
		}else{

			if ($signature->language == 'en'){
				showFreeEn($pdf, $signature);
			}else{
				showFree($pdf, $signature);
			}
		}
		
	}
	

	// set margins
	// $pdf->SetMargins(10, 10, 10, true);

	// set auto page breaks false
	$pdf->SetAutoPageBreak(true, 0);
	$pdf->Output('proof.pdf', 'I');	
}


function showFree($pdf, $signature){
	// add a page
	if (empty($signature->signature)) return false;

	$speechDate = new DateTime($signature->speech_date);
	$uploadDate = new DateTime($signature->upload_date);

	$pdf->AddPage('P', 'A4');	

	$pdf->Image($signature->signature, 20, 25, 23, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 5, '<h3>著作利用授權同意書<h3>', 0, 'C', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "本人___________同意無償授權臺北市政府及臺北市政府公務人員訓練處（以下稱被授權人），將本", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 15, "人於 ".((int)$speechDate->format('Y')-1911)." 年 ".$speechDate->format('m')." 月 ".$speechDate->format('d')." 日，在{$signature->location}之「{$signature->title}」演講實況，同步進行全程錄音、錄影及課程直播，並提供相關檔案資料等著作，供被授權人改作成為數位學習之衍生著作，授權條款如下：", 0, 'L', 0, 1, '', '', true, 0, true);
	// $pdf->MultiCell(190, 10, "等著作，供被授權人改作成為數位學習之衍生著作，授權條款如下：", 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->MultiCell(190, 22, "<h4>1、姓名表示與原始意思保持</h4>該衍生著作必須表示本人及相關著作人之姓名，並維持演講內容之原始意思，如本人對該衍生著作出具書面修正意見，被授權人應配合辦理。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>2、數位學習推廣用途</h4>本人同意將該衍生著作，於被授權人所屬數位學習管道(包括但不限於數位學習網站、APP)公開傳輸提供學習者使用，及摘錄片段作為學習推廣用途，另基於行動學習需求允許學習者下載使用。", 0, 'L', 0, 1, '', '', true, 0, true);	
	$pdf->MultiCell(190, 22, "<h4>3、限非商業用途</h4>除本人許可外，被授權人、學習者及與被授權人有合作關係之機構，均不得將該衍生著作使用於商業用途。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>4、課程開放分享</h4>本人☑ 同意 / ☐不同意（未勾選視為同意）授權該衍生著作，由被授權人開放分享予其他與被授權人有合作關係之機構，作為教育推廣用途。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 27, "<h4>5、演講教材開放下載</h4>本人☐ 同意 / ☐不同意（未勾選視為同意）授權本場演講教材，由被授權人上傳所屬學習管道，並允許學習者基於學習需求下載參考，且不得使用於商業用途。並於日後必要時，基於公益目的，提供議員問政使用。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>6、其他授權限制之敘明</h4>本人要求該衍生著作，須受以下限制（如使用期限、指定對象、特定授權項目等）：", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 10, "{$signature->other_limit}", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 15, "<h4>本人保證所提供之著作為自行創作，無侵害第三人之智慧財產權或其他權利，對該著作擁有著作權；本人並了解，被授權人擁有該衍生著作之著作財產權。", 0, 'L', 0, 1, '', '', true, 0, true);	

	$checkPath = "template/check.png";
	$checkedPath = "template/checked.png";
	if ($signature->is_share == 1){
		$isShare = $checkedPath;
		$isNotShare = $checkPath;		
	}else{
		$isShare = $checkPath;
		$isNotShare = $checkedPath;
	}

	if ($signature->is_download === NULL) {
		$isDownload = $checkPath;
		$isNotDownload = $checkPath;
	}else if ($signature->is_download == 1){
		$isDownload = $checkedPath;
		$isNotDownload = $checkPath;		
	}else { //is_download = 0
		$isDownload = $checkPath;
		$isNotDownload = $checkedPath;
	}

	$pdf->Image($isShare, 20, 121, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotShare, 37, 121, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isDownload, 20, 143, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotDownload, 37, 143, 5, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 15, "此致　臺北市政府及臺北市政府公務人員訓練處", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 10, "授　權　人：", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->Image($signature->signature, 35, 230, 100, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 10, "身分證字號：{$signature->idno}", 0, 'L', 0, 1, '', '', true, 0, true);	
	//$pdf->MultiCell(190, 10, "中　華　民　國".((int)$uploadDate->format('Y')-1911).'　年　'.$uploadDate->format('m').'　月　'.$uploadDate->format('d').'　日　', 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->Cell(190, 10, '中　華　民　國　'.((int)$uploadDate->format('Y')-1911).'　年　'.$uploadDate->format('m').'　月　'.$uploadDate->format('d').'　日　', 0, 0, 'C', 0, '', 0, false, 'T', 'M'); 
}

function showFreeEn($pdf, $signature){
	// add a page
	if (empty($signature->signature)) return false;

	$speechDate = new DateTime($signature->speech_date);
	$uploadDate = new DateTime($signature->upload_date);

	$pdf->AddPage('P', 'A4');	

	$pdf->Image($signature->signature, 40, 25, 23, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 5, '<h3>COPYRIGHT LICENSE AGREEMENT<h3>', 0, 'C', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 35, "The undersigned,___________, hereby agrees to grant a free license to Taipei City Government and the Department of Civil Servant Development, Taipei City Government (hereinafter referred to collectively as the Licensee) to simultaneously make a sound and video recording of, and live stream, the entirety of the presentation that will be given by the undersigned on ".$speechDate->format('d-m-Y')." at {$signature->location} (location) on the topic of “{$signature->title}” (topic of presentation), and also agrees to provide related file materials and other writings for the Licensee to adapt into derivative works of e-learning. The terms of the license are as follows:", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 27, "<h4>1、Retention of the right of paternity and original meaning</h4>Such derivative works must identify the name of the undersigned and the relevant author(s), and the contents of the original presentation must be maintained. If the undersigned issues a written correction to the derivative works, the Licensee must cooperate with implementing such correction.", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 32, "<h4>2、E-learning promotion purposes</h4>The undersigned agrees to the public transmission of derivative works on the Licensee's e-learning channels (including but not limited to e-learning websites and apps) for the use of learners, and to the use of excerpts for purposes of promoting learning, and to allow learners to download and use the derivative works based on mobile learning needs.", 0, 'L', 0, 1, '', '', true, 0, true);	
	$pdf->MultiCell(190, 22, "<h4>3、Limited to non-commercial use</h4>Derivative works shall not be used for commercial purposes by the Licensee, learners, or organizations with which the Licensee has a cooperative relationship, except with the undersigned’s permission.", 0, 'L', 0, 1, '', '', true, 0, true);					
	$pdf->MultiCell(190, 27, "<h4>4、Open course sharing</h4>The undersigned ☑agrees / ☐disagrees (agreement is deemed if neither option is checked) to authorize derivative works to be made available, by the Licensee, for sharing with other organizations with which the licensee has a cooperative relationship for purposes of promoting education.", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 37, "<h4>5、Presentation materials open for downloading</h4>The undersigned ☐agrees / ☐disagrees (agreement is deemed if neither option is checked) to authorize the Licensee to upload teaching materials of this presentation to the Licensee’s learning channels, and to allow learners to download them for reference based on learning needs, provided that they shall not be used for commercial purposes, and to allow the Licensee to share with Taipei City Council for purposes of promoting the public interest when needed..", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>6、Description of other restrictions on authorization</h4>The undersigned requests that derivative works be subject to the following restrictions (e.g., duration of use, intended recipients, specific licensed items, and so on)：", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 10, "{$signature->other_limit}(Please specify)", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 20, "Sincerely,Taipei City Government and the Department of Civil Servant Development, Taipei City Government", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "Licensor:", 0, 'L', 0, 1, '', '', true, 0, true);

	$checkPath = "template/check.png";
	$checkedPath = "template/checked.png";

	if ($signature->is_share == 1){
		$isShare = $checkedPath;
		$isNotShare = $checkPath;		
	}else{
		$isShare = $checkPath;
		$isNotShare = $checkedPath;
	}

	if ($signature->is_download === NULL) {
		$isDownload = $checkPath;
		$isNotDownload = $checkPath;
	}else if ($signature->is_download == 1){
		$isDownload = $checkedPath;
		$isNotDownload = $checkPath;		
	}else { //is_download = 0
		$isDownload = $checkPath;
		$isNotDownload = $checkedPath;
	}

	$pdf->Image($isShare, 40, 151, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotShare, 59, 151, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isDownload, 40, 178, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotDownload, 59, 178, 5, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->Image($signature->signature, 35, 245, 100, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->MultiCell(190, 5, "Identity card (or ARC/passport) number:{$signature->idno}", 0, 'L', 0, 1, '', '', true, 0, true);	
	$englishMnoth = getEnglishMonth($uploadDate->format('m'));
	$pdf->MultiCell(190, 5, "This ".$uploadDate->format('d')."th day of {$englishMnoth}, ".$uploadDate->format('Y'), 0, 'L', 0, 1, '', '', true, 0, true);	

}

function showPaid($pdf, $signature){
	// add a page
	if (empty($signature->signature)) return false;

	$speechDate = new DateTime($signature->speech_date);
	$uploadDate = new DateTime($signature->upload_date);

	$pdf->AddPage('P', 'A4');	

	$pdf->Image($signature->signature, 20, 25, 23, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 5, '<h3>著作利用授權同意書<h3>', 0, 'C', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "本人___________同意授權臺北市政府及臺北市政府公務人員訓練處（以下稱被授權人），將本人於", 0, 'L', 0, 1, '', '', true, 0, true);
	// $pdf->SetFont('times'，'BI'，20，''，'false';
	$pdf->MultiCell(190, 15, " ".((int)$speechDate->format('Y')-1911)." 年 ".$speechDate->format('m')." 月 ".$speechDate->format('d')." 日，在{$signature->location}之「{$signature->title}」演講實況，同步進行全程錄音、錄影及課程直播，並提供相關檔案資料等著作，供被授權人改作成為數位學習之衍生著作，授權條款如下：", 0, 'L', 0, 1, '', '', true, 0, true);


	// $pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	// $pdf->MultiCell(190, 10, "等著作，供被授權人改作成為數位學習之衍生著作，授權條款如下：", 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->MultiCell(190, 22, "<h4>1、姓名表示與原始意思保持</h4>該衍生著作必須表示本人及相關著作人之姓名，並維持演講內容之原始意思，如本人對該衍生著作出具書面修正意見，被授權人應配合辦理。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>2、數位學習推廣用途</h4>本人同意將該衍生著作，於被授權人所屬數位學習管道(包括但不限於數位學習網站、APP)公開傳輸提供學習者使用，及摘錄片段作為學習推廣用途，另基於行動學習需求允許學習者下載使用。", 0, 'L', 0, 1, '', '', true, 0, true);	
	$pdf->MultiCell(190, 22, "<h4>3、限非商業用途</h4>除本人許可外，被授權人、學習者及與被授權人有合作關係之機構，均不得將該衍生著作使用於商業用途。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>4、課程開放分享</h4>本人☑ 同意 / ☐不同意（未勾選視為同意）授權該衍生著作，由被授權人開放分享予其他與被授權人有合作關係之機構，作為教育推廣用途。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 27, "<h4>5、演講教材開放下載</h4>本人☐ 同意 / ☐不同意（未勾選視為同意）授權本場演講教材，由被授權人上傳所屬學習管道，並允許學習者基於學習需求下載參考，且不得使用於商業用途。並於日後必要時，基於公益目的，提供議員問政使用。", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>6、其他授權限制之敘明</h4>本人要求該衍生著作，須受以下限制（如使用期限、指定對象、特定授權項目等）：", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 10, "{$signature->other_limit}", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 22, "<h4>7、課程授權費</h4>本同意書之授權費用為新台幣 {$signature->paid_fee} 元整(含稅)。本人同意於簽署收據後收取費用。	本人保證所提供之著作為自行創作，無侵害第三人之智慧財產權或其他權利，對該著作擁有著作權；本人並了解，被授權人擁有該衍生著作之著作財產權。", 0, 'L', 0, 1, '', '', true, 0, true);

	$checkPath = "template/check.png";
	$checkedPath = "template/checked.png";
	if ($signature->is_share == 1){
		$isShare = $checkedPath;
		$isNotShare = $checkPath;		
	}else{
		$isShare = $checkPath;
		$isNotShare = $checkedPath;
	}

	if ($signature->is_download === NULL) {
		$isDownload = $checkPath;
		$isNotDownload = $checkPath;
	}else if ($signature->is_download == 1){
		$isDownload = $checkedPath;
		$isNotDownload = $checkPath;		
	}else { //is_download = 0
		$isDownload = $checkPath;
		$isNotDownload = $checkedPath;
	}

	$pdf->Image($isShare, 20, 121, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotShare, 37, 121, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isDownload, 20, 143, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotDownload, 37, 143, 5, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 20, "此致　臺北市政府及臺北市政府公務人員訓練處", 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->MultiCell(190, 10, "授　權　人：", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->Image($signature->signature, 35, 235, 100, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 10, "身分證字號：{$signature->idno}", 0, 'L', 0, 1, '', '', true, 0, true);
	//$pdf->MultiCell(190, 10, "中　華　民　國".((int)$uploadDate->format('Y')-1911).'　年　'.$uploadDate->format('m').'　月　'.$uploadDate->format('d').'　日　', 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->Cell(190, 10, '中　華　民　國　'.((int)$uploadDate->format('Y')-1911).'　年　'.$uploadDate->format('m').'　月　'.$uploadDate->format('d').'　日　', 0, 0, 'C', 0, '', 0, false, 'T', 'M'); 
}

function showPaidEn($pdf, $signature){
	// add a page
	if (empty($signature->signature)) return false;

	$speechDate = new DateTime($signature->speech_date);
	$uploadDate = new DateTime($signature->upload_date);

	$pdf->AddPage('P', 'A4');	

	$pdf->Image($signature->signature, 40, 25, 23, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->MultiCell(190, 5, '<h3>COPYRIGHT LICENSE AGREEMENT<h3>', 0, 'C', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 35, "The undersigned,___________, hereby agrees to grant a paid license to Taipei City Government and the Department of Civil Servant Development, Taipei City Government (hereinafter referred to collectively as the Licensee) to simultaneously make a sound and video recording of, and live stream, the entirety of the presentation that will be given by the undersigned on ".$speechDate->format('d-m-Y')." at {$signature->location} (location) on the topic of  “{$signature->title}” (topic of presentation), and also agrees to provide related file materials and other writings for the Licensee to adapt into derivative works of e-learning. The terms of the license are as follows:", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 27, "<h4>1、Retention of the right of paternity and original meaning</h4>Such derivative works must identify the name of the undersigned and the relevant author(s), and the contents of the original presentation must be maintained. If the undersigned issues a written correction to the derivative works, the Licensee must cooperate with implementing such correction.", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 32, "<h4>2、E-learning promotion purposes</h4>The undersigned agrees to the public transmission of derivative works on the Licensee's e-learning channels (including but not limited to e-learning websites and apps) for the use of learners, and to the use of excerpts for purposes of promoting learning, and to allow learners to download and use the derivative works based on mobile learning needs.", 0, 'L', 0, 1, '', '', true, 0, true);	
	$pdf->MultiCell(190, 22, "<h4>3、Limited to non-commercial use</h4>Derivative works shall not be used for commercial purposes by the Licensee, learners, or organizations with which the Licensee has a cooperative relationship, except with the undersigned’s permission.", 0, 'L', 0, 1, '', '', true, 0, true);					
	$pdf->MultiCell(190, 27, "<h4>4、Open course sharing</h4>The undersigned ☑agrees / ☐disagrees (agreement is deemed if neither option is checked) to authorize derivative works to be made available, by the Licensee, for sharing with other organizations with which the licensee has a cooperative relationship for purposes of promoting education.", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 37, "<h4>5、Presentation materials open for downloading</h4>The undersigned ☐agrees / ☐disagrees (agreement is deemed if neither option is checked) to authorize the Licensee to upload teaching materials of this presentation to the Licensee’s learning channels, and to allow learners to download them for reference based on learning needs, provided that they shall not be used for commercial purposes, and to allow the Licensee to share with Taipei City Council for purposes of promoting the public interest when needed.", 0, 'L', 0, 1, '', '', true, 0, true);

	$checkPath = "template/check.png";
	$checkedPath = "template/checked.png";
	if ($signature->is_share == 1){
		$isShare = $checkedPath;
		$isNotShare = $checkPath;		
	}else{
		$isShare = $checkPath;
		$isNotShare = $checkedPath;
	}

	if ($signature->is_download === NULL) {
		$isDownload = $checkPath;
		$isNotDownload = $checkPath;
	}else if ($signature->is_download == 1){
		$isDownload = $checkedPath;
		$isNotDownload = $checkPath;		
	}else { //is_download = 0
		$isDownload = $checkPath;
		$isNotDownload = $checkedPath;
	}

	$pdf->Image($isShare, 40, 151, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotShare, 59, 151, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isDownload, 40, 178, 5, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($isNotDownload, 59, 178, 5, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->MultiCell(190, 22, "<h4>6、Description of other restrictions on authorization</h4>The undersigned requests that derivative works be subject to the following restrictions (e.g., duration of use, intended recipients, specific licensed items, and so on):", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 10, "{$signature->other_limit}(Please specify)", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 32, "<h4>7、Licensing fee<h4>The licensing fee for this consent is NT {$signature->paid_fee} (tax included). The undersigned agrees to collect the fee after signing the receipt. The undersigned certifies that the work provided is his/her creation and does not infringe on the intellectual property or other rights of third parties and that he/she owns the copyright to the work. The undersigned also understands that the Licensee owns the copyright to the derivative works.", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 5, "", 0, 'L', 0, 1, '', '', true, 0, true);
	$pdf->MultiCell(190, 20, "Sincerely,Taipei City Government and the Department of Civil Servant Development, Taipei City Government", 0, 'L', 0, 1, '', '', true, 0, true);

	$pdf->MultiCell(190, 10, "Licensor:", 0, 'L', 0, 1, '', '', true, 0, true);


	// $pdf->Image($signature->signature, 40, 25, 23, 0, '', '', '', false, 300, '', false, false, 0);
	$pdf->Image($signature->signature, 35, 20, 100, 0, '', '', '', false, 300, '', false, false, 0);

	$pdf->MultiCell(190, 5, "Identity card (or ARC/passport) number:{$signature->idno}", 0, 'L', 0, 1, '', '', true, 0, true);	
	$englishMnoth = getEnglishMonth($uploadDate->format('m'));
	
	$pdf->MultiCell(190, 5, "This ".$uploadDate->format('d')."th day of {$englishMnoth}, ".$uploadDate->format('Y'), 0, 'L', 0, 1, '', '', true, 0, true);	
}

function getEnglishMonth($month)
{
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

	if (isset($englishMonth[$month])){
		return $englishMonth[$month];
	}else{
		return null;
	}
}
