<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_balance.php");
require_once("check_access.php");

if(check_access('REPORT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//header("location:projected_funds_pdf?st=".$_POST['START_DATE'].'&et='.$_POST['END_DATE'].'&dt='.$_POST['DATE_TYPE'].'&e='.$_POST['PK_EMPLOYEE_MASTER'].'&tc='.$_POST['TASK_COMPLETED']);
	
	$cond = "";
	if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '') {
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE BETWEEN '$ST' AND '$ET' ";
	} else if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
		$cond .= " AND TRANSACTION_DATE >= '$ST' ";
	} else if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
		$cond .= " AND TRANSACTION_DATE <= '$ET' ";
	}
	
	//DIAM-2145
	 $sts = "";
	 $wh_con = "";

	 if(!empty($_POST['PK_STUDENT_STATUS'])){
	 $cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
	 }

/*	if(!empty($_POST['PK_STUDENT_STATUS']) && !isset($_POST['INCLUDE_ALL_LEADS'])) {
		$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") ";
	}else{

		// if($_POST['INCLUDE_ALL_LEADS'] == 1){
		// 	$ADMISSION = "";
		// }else{
		// 	//$ADMISSION = " AND ADMISSIONS = 0 ";
		// }

		if(!empty($_POST['PK_STUDENT_STATUS'])  && $_POST['INCLUDE_ALL_LEADS'] == 1){
			$wh_con = "AND ADMISSIONS = 1  OR PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).")";			
		}

		$res_type = $db->Execute("select PK_STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 $wh_con order by STUDENT_STATUS ASC");
		while (!$res_type->EOF) {
			if($sts != '')
				$sts .= ',';
			$sts .= $res_type->fields['PK_STUDENT_STATUS'];
			$res_type->MoveNext();
		}

			if($sts != '')
				$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$sts.") ";
		
		
	}	*/
	//	DIAM-2145
	
	$campus_name  = "";
	$campus_cond  = "";
	$campus_cond1 = "";
	$campus_id	  = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	  = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond  = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
		$campus_cond1 = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}
	
	/* Ticket # 1275 */
	$group_by = "";
	$ledger_cond = "";
	if($_POST['REPORT_TYPE'] == 1){
		$group_by 		= " S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT ";
		$ledger_cond 	= " AND S_STUDENT_LEDGER.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	} else if($_POST['REPORT_TYPE'] == 2){
		$ledger_cond = " AND S_STUDENT_LEDGER.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER ";
		$cond 		.= " AND IS_ACTIVE_ENROLLMENT = 1 ";
		$group_by = " S_STUDENT_LEDGER.PK_STUDENT_MASTER ";
	} 
	
	//echo $cond;exit;
	$query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, SSN, PK_STUDENT_LEDGER, IF(EXPECTED_GRAD_DATE = '0000-00-00','', DATE_FORMAT(EXPECTED_GRAD_DATE, '%m/%d/%Y' )) AS EXPECTED_GRAD_DATE ,IF(DETERMINATION_DATE = '0000-00-00','', DATE_FORMAT(DETERMINATION_DATE, '%Y-%m-%d' )) AS DETERMINATION_DATE, SUM(CREDIT) AS CREDIT, SUM(DEBIT) AS DEBIT, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, FUNDING, STUDENT_STATUS,  IF(LDA = '0000-00-00','', DATE_FORMAT(LDA, '%Y-%m-%d' )) AS LDA, STUDENT_ID     
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	, S_STUDENT_ENROLLMENT
	LEFT JOIN M_STUDENT_STATUS On M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	, S_STUDENT_LEDGER 
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN (SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond1 ) AND
	(S_STUDENT_LEDGER.PK_PAYMENT_BATCH_DETAIL > 0 OR PK_MISC_BATCH_DETAIL > 0 OR PK_TUITION_BATCH_DETAIL > 0 ) $cond $ledger_cond 
	GROUP BY $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ";
	/* Ticket # 1275 */
	//echo $query;exit;
	if($_POST['FORMAT'] == 1){
		/////////////////////////////////////////////////////////////////
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		require_once('../global/tcpdf/config/lang/eng.php');
		require_once('../global/tcpdf/tcpdf.php');

			
		class MYPDF extends TCPDF {
			public function Header() {
				global $db, $campus_name;
				
				$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				
				if($res->fields['PDF_LOGO'] != '') {
					$ext = explode(".",$res->fields['PDF_LOGO']);
					$this->Image($res->fields['PDF_LOGO'], 8, 3, 0, 18, $ext[(count($ext) - 1)], '', 'T', false, 300, '', false, false, 0, false, false, false);
				}
				
				$this->SetFont('helvetica', '', 15);
				$this->SetY(6);
				$this->SetX(85);
				$this->SetTextColor(000, 000, 000);
				$this->MultiCell(100, 5, $res->fields['SCHOOL_NAME'], 0, 'L', 0, 0, '', '', true);
				
				$this->SetFont('helvetica', 'I', 20);
				$this->SetY(8);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 8, "Negative Balances", 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				
				$this->SetFillColor(0, 0, 0);
				$style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
				$this->Line(210, 13, 295, 13, $style);
				
				$this->SetFont('helvetica', 'I', 10);
				$this->SetY(14);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(102, 5, "Campus(es): ".$campus_name, 0, 'R', 0, 0, '', '', true);
				
				$str = "";
				if($_POST['START_DATE'] != '' && $_POST['END_DATE'] != '')
					$str = " ".$_POST['START_DATE'].' - '.$_POST['END_DATE'];
				else if($_POST['START_DATE'] != '')
					$str = " From ".$_POST['START_DATE'];
				else if($_POST['END_DATE'] != '')
					$str = " As of Date: ".$_POST['END_DATE'];

				$this->SetY(21);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
				$str = "";
				if(empty($_POST['PK_STUDENT_STATUS'])) {
					$str = "All Student Status";
				} else {
					$str = "";
					$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") order by STUDENT_STATUS ASC");
					while (!$res_type->EOF) {
						if($str != '')
							$str .= ', ';
						$str .= $res_type->fields['STUDENT_STATUS'];
						$res_type->MoveNext();
					}
					
					if($str != '')
						$str = "Student Status(es): ".$str;
				}
				
				$this->SetY(23);
				$this->SetX(52);
				$this->SetTextColor(000, 000, 000);
				//$this->Cell(102, 5, $str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				$this->MultiCell(240, 5, $str, 0, 'R', 0, 0, '', '', true);
				
				$str = "";
				if($_POST['REPORT_TYPE'] == 1)
					$str = "By Enrollment";
				else if($_POST['REPORT_TYPE'] == 2)
					$str = "By Student";

				$this->SetY(34);
				$this->SetX(190);
				$this->SetTextColor(000, 000, 000);
				$this->Cell(102, 5, "Report Type: ".$str, 0, false, 'R', 0, '', 0, false, 'M', 'L');
				
			}
			public function Footer() {
				global $db;
				$this->SetY(-15);
				$this->SetX(270);
				$this->SetFont('helvetica', 'I', 7);
				$this->Cell(30, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
				
				$this->SetY(-15);
				$this->SetX(10);
				$this->SetFont('helvetica', 'I', 7);
				
				$timezone = $_SESSION['PK_TIMEZONE'];
				if($timezone == '' || $timezone == 0) {
					$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					$timezone = $res->fields['PK_TIMEZONE'];
					if($timezone == '' || $timezone == 0)
						$timezone = 4;
				}
				
				$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
				$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
				
				$this->Cell(30, 10, $date, 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(7, 37, 7);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, 30);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->AddPage();

		$total 	= 0;
		$txt 	= '';
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<td width="17%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Student</td>
							<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >ID</td>
							<td width="14%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Program</td>
							<td width="13%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Status</td>
							<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Start Date</td>
							<td width="6%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >LDA</td>
							<td width="7%" style="border-top:1px solid #000;border-bottom:1px solid #000;" >Determination</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Debit</td>
							<td width="8%" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Credit</td>
							<td width="8%" align="right" style="border-top:1px solid #000;border-bottom:1px solid #000;" align="right" >Balance</td>
						</tr>
					</thead>';

		$TOT_BALANCE = 0;
		$res_ledger = $db->Execute($query);
		
		$sno = 0;
		while (!$res_ledger->EOF) {
			
			$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];

			if($BALANCE < 0){
				$sno++;
				$TOT_BALANCE += $BALANCE;
				
				/* Ticket # 1275  */
				if($BALANCE < 0) {
					$BALANCE = $BALANCE * -1;
					$BALANCE = '($ '.number_format_value_checker($BALANCE,2).')';
				} else
					$BALANCE = '$ '.number_format_value_checker($BALANCE,2);
				/* Ticket # 1275  */
				
				$txt 	.= '<tr>
							<td width="17%" >'.$sno.'. '.$res_ledger->fields['NAME'].'</td>
							<td width="14%" >'.$res_ledger->fields['STUDENT_ID'].'</td>
							<td width="14%" >'.$res_ledger->fields['PROGRAM_CODE'].'</td>
							<td width="13%" >'.$res_ledger->fields['STUDENT_STATUS'].'</td>
							<td width="6%" >'.$res_ledger->fields['BEGIN_DATE_1'].'</td>
							<td width="6%" >'.$res_ledger->fields['LDA'].'</td>
							<td width="7%" >'.$res_ledger->fields['DETERMINATION_DATE'].'</td>
							<td width="8%" align="right" >$ '.number_format_value_checker($res_ledger->fields['DEBIT'],2).'</td>
							<td width="8%" align="right" >$ '.number_format_value_checker($res_ledger->fields['CREDIT'],2).'</td>
							<td width="8%" align="right" >'.$BALANCE.'</td>
						</tr>';
			}
			
			$res_ledger->MoveNext();
		}
		
		$txt 	.= '</table>';
		
		if($TOT_BALANCE < 0) {
			$TOT_BALANCE = $TOT_BALANCE * -1;
			$TOT_BALANCE = '($ '.number_format_value_checker($TOT_BALANCE,2).')';
		} else
			$TOT_BALANCE = '$ '.number_format_value_checker($TOT_BALANCE,2);
		
		$txt 	.= '<br /><br />
					<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<tr>
							<td width="102%" align="right" style="font-size:45px;" align="right" ><i>Grand Total '.$TOT_BALANCE.'</i></td>
						</tr>
					</table>';
		
		
			//echo $txt;exit;
		$pdf->writeHTML($txt, $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

		$file_name = 'Negative Balance.pdf';
		/*if($browser == 'Safari')
			$pdf->Output('temp/'.$file_name, 'FD');
		else	
			$pdf->Output($file_name, 'I');*/
			
		$pdf->Output('temp/'.$file_name, 'FD');
		return $file_name;	
		/////////////////////////////////////////////////////////////////
	} else if($_POST['FORMAT'] == 2){

		include '../global/excel/Classes/PHPExcel/IOFactory.php';
		$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

		$total_fields = 120;
		for($i = 0 ; $i <= $total_fields ; $i++){
			if($i <= 25)
				$cell[] = $cell1[$i];
			else {
				$j = floor($i / 26) - 1;
				$k = ($i % 26);
				//echo $j."--".$k."<br />";
				$cell[] = $cell1[$j].$cell1[$k];
			}	
		}

		$dir 			= 'temp/';
		$inputFileType  = 'Excel2007';
		$file_name 		= 'Negative Balance.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Start Date';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'Determination';
		$width[]   = 20;
		$heading[] = 'Debit';
		$width[]   = 20;
		$heading[] = 'Credit';
		$width[]   = 20;
		$heading[] = 'Balance';
		$width[]   = 20;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
		
		$TOT_BALANCE = 0;
		$res_ledger = $db->Execute($query);
		
		$sno = 0;
		while (!$res_ledger->EOF) {

			$BALANCE 	= $res_ledger->fields['DEBIT'] - $res_ledger->fields['CREDIT'];
			if($BALANCE < 0){
				$sno++;
				$TOT_BALANCE += $BALANCE;
				
				$PK_STUDENT_ENROLLMENT = $res_ledger->fields['PK_STUDENT_ENROLLMENT'];
				$res_campus = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS  $campus_cond1 ");
	
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_campus->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['PROGRAM_CODE']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['BEGIN_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['LDA']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DETERMINATION_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['DEBIT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_ledger->fields['CREDIT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($BALANCE);

			}
			
			$res_ledger->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
}


	
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_NEGATIVE_BALANCE?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS{position: absolute;top: 57px;width: 140px}
		.option_red > a > label{color:red !important}	/* DIAM-2145 */
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_NEGATIVE_BALANCE?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-2">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<?//DIAM-2145
												 $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC,CAMPUS_CODE ASC");
												while (!$res_type->EOF) {
													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
													 ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($ACTIVE == '0') { ?>class="option_red"<? } ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<!--<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" >
										</div>-->
										<div class="col-md-2">
											<?=AS_OF_DATE ?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-3">
											<?=STUDENT_STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control " ><!-- DIAM-2145 -->
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION,ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by  ACTIVE DESC,STUDENT_STATUS ASC"); //<!-- DIAM-2145 -->
												while (!$res_type->EOF) { 
													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													}
													?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" <?php if ($ACTIVE == '0') { ?>class="option_red"<? } ?>><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION'] . ' ' . $Status?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<!-- Ticket # 1275 -->
										<div class="col-md-2">
											<?=REPORT_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE"  class="form-control" >
												<option value="1">By Enrollment</option>
												<option value="2" selected >By Student</option>
											</select>
										</div>
										<!-- Ticket # 1275 -->
										
										<!-- <div class="col-md-1" style="padding: 0;max-width:12.333%;flex: 0 0 12.333%;" >
											<br /><br />
											<input type="checkbox" id="INCLUDE_ALL_LEADS" value="1" name="INCLUDE_ALL_LEADS"> 
											<? //=INCLUDE_ALL_LEADS?>
										</div> --><!--DIAM-2145 -->
										
										<div class="col-md-2" style="padding: 0;max-width:10.667%;flex: 0 0 10.667%;" >
											<br />
											<button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
</body>

</html>
