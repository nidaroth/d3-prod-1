<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/accsc.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}

$res = $db->Execute("SELECT ACCSC FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ACCSC'] == 0 || $res->fields['ACCSC'] == '') {
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_id 	 = implode(",",$_POST['PK_CAMPUS']);
	}

	if($_POST['REPORT_OPTION'] == 1)
		$REPORT_OPTION = "Program";
	else
		$REPORT_OPTION = "Program Group";
	
	if($_POST['FORMAT'] == 1) {
		require_once '../global/mpdf/vendor/autoload.php';
	
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE, EMAIL FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME 	= $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 	= $res->fields['PDF_LOGO'];
		$EMAIL			= $res->fields['EMAIL'];
		$PHONE			= $res->fields['PHONE'];
		$SCHOOL_ADDRESS = trim($res->fields['ADDRESS']." ".$res->fields['ADDRESS_1'])."<br />".$res->fields['CITY']." ".$res->fields['STATE_CODE']." ".$res->fields['ZIP'];
		
		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
			
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}
		
		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP40002</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';

		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 50,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 9,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		// $mpdf->autoPageBreak = true;
		$mpdf->SetHTMLFooter($footer);
		
		$k 		= 1;
		$txt 	= "";
		// echo "CALL COMP40002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".date("Y-m-d", strtotime($_POST['START_DATE']))."','".date("Y-m-d", strtotime($_POST['END_DATE']))."', '".$REPORT_OPTION."')";exit;
		$res = $db->Execute("CALL COMP40002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".date("Y-m-d", strtotime($_POST['START_DATE']))."','".date("Y-m-d", strtotime($_POST['END_DATE']))."', '".$REPORT_OPTION."')");
		while (!$res->EOF) {

			if($k == 1 || $PROGRAM != $res->fields['PROGRAM']) {
				
				if($k == 1) {
					$PROGRAM 				= $res->fields['PROGRAM_DESCRIPTION'];
					$PROGRAM_LENGTH 		= $res->fields['PROGRAM_LENGTH_MONTHS'];
					$START_DATE_MONTH_YEAR	= $res->fields['START_DATE_MONTH_YEAR'];
				}
				
				if($txt != '') {
					$txt .= "</table>";
					
					$header = '<table width="100%" style="page-break-inside: avoid" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="30%" valign="top" ><span style="font-size:20px">'.$SCHOOL_NAME.'</span></td>
								<td width="50%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ACCSC Verification Source</b></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<table width="100%" >
							<tr>
								<td width="60%" valign="top" >
									<table width="100%" >
										<tr>
											<td colspan="2" ><b style="font-size:15px" >'.$PROGRAM.'</b></td>
										</tr>
										<tr>
											<td width="40%" >Report Date:</td>
											<td width="60%" >'.date("m/d/Y").'</td>
										</tr>
										<tr>
											<td >Program Length in Months:</td>
											<td >'.$PROGRAM_LENGTH.'</td>
										</tr>
										<tr>
											<td >Reporting Period Begin Date:</td>
											<td >'.$_POST['START_DATE'].'</td>
										</tr>
										<tr>
											<td >Reporting Period End Date:</td>
											<td >'.$_POST['END_DATE'].'</td>
										</tr>
										<tr>
											<td >Class Start Year Month:</td>
											<td >'.$START_DATE_MONTH_YEAR.'</td>
										</tr>
									</table>
								</td>
								<td width="40%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="40%" valign="top" >Institution Name:</td>
											<td width="60%" >'.$SCHOOL_NAME.'</td>
										</tr>
										<tr>
											<td valign="top" >Business Address:</td>
											<td >'.$SCHOOL_ADDRESS.'</td>
										</tr>
										<tr>
											<td valign="top" >Phone:</td>
											<td >'.$PHONE.'</td>
										</tr>
										<tr>
											<td valign="top" >Email:</td>
											<td >'.$EMAIL.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
					$mpdf->SetHTMLHeader($header);
					// $mpdf->AddPage();
					$mpdf->WriteHTML($txt);
					$tmptxt = $tmptxt.$txt;
					$txt = '';
				}
				
				$k 	= 1;
				$PROGRAM 				= $res->fields['PROGRAM_DESCRIPTION'];
				$PROGRAM_LENGTH 		= $res->fields['PROGRAM_LENGTH_MONTHS'];
				$START_DATE_MONTH_YEAR	= $res->fields['START_DATE_MONTH_YEAR'];
			}

			if($k == 1){
				$txt = '<table cellspacing="0" cellpadding="4" width="100%" style="page-break-inside: avoid">
							<tr>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="16%" ><b>Student<br />Student Status</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="16%" ><b>Program<br />Start Date</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="16%" ><b>Job Status<br />Employer</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="16%" ><b>Employer Point<br />Of Contact</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="10%" ><b>Date of Employement</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="16%" ><b>Descriptive Job Title<br />and Responsibilities</b></td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;" width="10%" ><b>Source Of Verification</b></td>
							</tr>';
			}
			$START_DATE = '';
			if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
				$START_DATE = date("m/d/Y", strtotime($res->fields['START_DATE']));
				
			$EMPLOYMENT_START_DATE = '';
			if($res->fields['EMPLOYMENT_START_DATE'] != '' && $res->fields['EMPLOYMENT_START_DATE'] != '0000-00-00')
				$EMPLOYMENT_START_DATE = date("m/d/Y", strtotime($res->fields['EMPLOYMENT_START_DATE']));
				
			$txt .= '<tr>
						<td >'.$res->fields['STUDENT'].'<br />'.$res->fields['STUDENT_STATUS'].'</td>
						<td >'.$res->fields['PROGRAM_CODE'].'<br />'.$START_DATE.'</td>
						<td >'.$res->fields['JOB_PLACEMENT_STATUS'].'<br />'.$res->fields['COMPANY_NAME'].'<br />'.$res->fields['COMPANY_ADDRESS'].'<br />'.$res->fields['COMPANY_CITY_STATE_ZIP'].'<br />'.$res->fields['COMPANY_PHONE'].'</td>
						<td >'.$res->fields['CONTACT_NAME'].'<br />'.$res->fields['CONTACT_TITLE'].'<br />'.$res->fields['CONTACT_EMAIL'].'<br />'.$res->fields['CONTACT_PHONE'].'</td>
						<td >'.$EMPLOYMENT_START_DATE.'</td>
						<td >'.$res->fields['JOB_TITLE'].'</td>
						<td >'.$res->fields['PLACEMENT_VERIFICATION_SOURCE'].'</td>
					</tr>';
			
			$k++;
			$res->MoveNext();
		}
		
		if($txt != '') {
			$txt .= "</table>";
			
			$header = '<table width="100%" style="page-break-inside: avoid" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="30%" valign="top" ><span style="font-size:20px">'.$SCHOOL_NAME.'</span></td>
								<td width="50%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>ACCSC Verification Source</b></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<table width="100%" >
							<tr>
								<td width="60%" valign="top" >
									<table width="100%" >
										<tr>
											<td colspan="2" ><b style="font-size:15px" >'.$PROGRAM.'</b></td>
										</tr>
										<tr>
											<td width="40%" >Report Date:</td>
											<td width="60%" >'.date("m/d/Y").'</td>
										</tr>
										<tr>
											<td >Program Length in Months:</td>
											<td >'.$PROGRAM_LENGTH.'</td>
										</tr>
										<tr>
											<td >Reporting Period Begin Date:</td>
											<td >'.$_POST['START_DATE'].'</td>
										</tr>
										<tr>
											<td >Reporting Period End Date:</td>
											<td >'.$_POST['END_DATE'].'</td>
										</tr>
										<tr>
											<td >Class Start Year Month:</td>
											<td >'.$START_DATE_MONTH_YEAR.'</td>
										</tr>
									</table>
								</td>
								<td width="40%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="40%" valign="top" >Institution Name:</td>
											<td width="60%" >'.$SCHOOL_NAME.'</td>
										</tr>
										<tr>
											<td valign="top" >Business Address:</td>
											<td >'.$SCHOOL_ADDRESS.'</td>
										</tr>
										<tr>
											<td valign="top" >Phone:</td>
											<td >'.$PHONE.'</td>
										</tr>
										<tr>
											<td valign="top" >Email:</td>
											<td >'.$EMAIL.'</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			$mpdf->SetHTMLHeader($header);
			//$mpdf->AddPage();
			$mpdf->WriteHTML($txt);
			$tmptxt = $tmptxt.$txt;
			$txt = '';
		}
	// echo $tmptxt;
	// exit;
		$mpdf->Output("ACCSC Employment Verification Source Report.pdf", 'D');
		exit;
		
	} else if($_POST['FORMAT'] == 2) {
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
		$file_name 		= 'ACCSC Employment Verification Source Report.xlsx';
		$outputFileName = $dir.$file_name ;
		
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName ); 

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/Licensure_Certification_Exam_Pass_Rates.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line 	= 1;	
		$index 	= 0;

		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Reporting Period Begin Date';
		$width[]   = 20;
		$heading[] = 'Reporting Period End Date';
		$width[]   = 20;
		
		$heading[] = 'Student Phone';
		$width[]   = 20;
		$heading[] = 'Student Address';
		$width[]   = 20;
		$heading[] = 'Student City/Stae/Zip';
		$width[]   = 20;
		$heading[] = 'Email';
		$width[]   = 20;
		$heading[] = 'Student Status';
		$width[]   = 20;
		$heading[] = 'Program Code';
		$width[]   = 20;
		$heading[] = 'Program Description';
		$width[]   = 20;
		$heading[] = 'Program Group';
		$width[]   = 20;
		$heading[] = 'Program Group Description';
		$width[]   = 20;
		$heading[] = 'Job Placement Status';
		$width[]   = 20;
		$heading[] = 'Program Length Months';
		$width[]   = 20;
		$heading[] = 'Start Date';
		$width[]   = 20;
		$heading[] = 'Start Date Month-Year';
		$width[]   = 20;
		$heading[] = 'Job Description';
		$width[]   = 20;
		$heading[] = 'Employment Start Date';
		$width[]   = 20;
		$heading[] = 'Job Title';
		$width[]   = 20;
		$heading[] = 'Placement Verification Source';
		$width[]   = 20;
		$heading[] = 'Placement Verification Source Description';
		$width[]   = 20;
		$heading[] = 'Verification Date';
		$width[]   = 20;
		$heading[] = 'Company Name';
		$width[]   = 20;
		$heading[] = 'Company Email';
		$width[]   = 20;
		$heading[] = 'Company Phone';
		$width[]   = 20;
		$heading[] = 'Company Address';
		$width[]   = 20;
		$heading[] = 'Company City/State/Zip';
		$width[]   = 20;
		$heading[] = 'Contact Name';
		$width[]   = 20;
		$heading[] = 'Contact Title';
		$width[]   = 20;
		$heading[] = 'Contact Email';
		$width[]   = 20;
		$heading[] = 'Contact Phone';
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
		// echo "CALL COMP40002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".date("Y-m-d", strtotime($_POST['START_DATE']))."','".date("Y-m-d", strtotime($_POST['END_DATE']))."', '".$REPORT_OPTION."')";exit;
		$res = $db->Execute("CALL COMP40002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".date("Y-m-d", strtotime($_POST['START_DATE']))."','".date("Y-m-d", strtotime($_POST['END_DATE']))."', '".$REPORT_OPTION."')");
		while (!$res->EOF) {
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT']);
			
			$REPORTING_PERIOD_BEGIN_DATE = '';
			if($res->fields['REPORTING_PERIOD_BEGIN_DATE'] != '' && $res->fields['REPORTING_PERIOD_BEGIN_DATE'] != '0000-00-00')
				$REPORTING_PERIOD_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_BEGIN_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($REPORTING_PERIOD_BEGIN_DATE);
			
			$REPORTING_PERIOD_END_DATE = '';
			if($res->fields['REPORTING_PERIOD_END_DATE'] != '' && $res->fields['REPORTING_PERIOD_END_DATE'] != '0000-00-00')
				$REPORTING_PERIOD_END_DATE = date("m/d/Y", strtotime($res->fields['REPORTING_PERIOD_END_DATE']));
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($REPORTING_PERIOD_END_DATE);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ADDRESS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_CITY_STATE_ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_CODE']);

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_GROUP_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JOB_PLACEMENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PROGRAM_LENGTH_MONTHS']);
						
			$START_DATE = '';
			if($res->fields['START_DATE'] != '' && $res->fields['START_DATE'] != '0000-00-00')
				$START_DATE = date("m/d/Y", strtotime($res->fields['START_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($START_DATE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['START_DATE_MONTH_YEAR']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JOB_DESCRIPTION']);
			
			$EMPLOYMENT_START_DATE = '';
			if($res->fields['EMPLOYMENT_START_DATE'] != '' && $res->fields['EMPLOYMENT_START_DATE'] != '0000-00-00')
				$EMPLOYMENT_START_DATE = date("m/d/Y", strtotime($res->fields['EMPLOYMENT_START_DATE']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($EMPLOYMENT_START_DATE);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['JOB_TITLE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PLACEMENT_VERIFICATION_SOURCE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PLACEMENT_VERIFICATION_SOURCE_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['VERIFICATION_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_ADDRESS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_CITY_STATE_ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CONTACT_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CONTACT_TITLE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CONTACT_EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CONTACT_PHONE']);
			
			$res->MoveNext();
		}
	}
	
	$objWriter->save($outputFileName);
	$objPHPExcel->disconnectWorksheets();
	header("location:".$outputFileName);

	exit;
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
	<title><?=MNU_ACCSC_EMP_VER_SOURCE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 57px; width:150px}
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
							<?=MNU_ACCSC_EMP_VER_SOURCE ?>
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
											<?=REPORT_TYPE ?>
											<select id="REPORT_TYPE_NEW" name="REPORT_TYPE_NEW" class="form-control"> 
												<option value="DETAIL" >Detail</option>
											</select>
										</div>
										<div class="col-md-3 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select OFFICIAL_CAMPUS_NAME,PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>"
													<?php if($res_type->RecordCount() === 1){
														echo " selected ";
													}
													?>><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<?=ENROLLMENT_START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=ENROLLMENT_END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=REPORT_OPTIONS ?>
											<select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" >
												<option value="1" >Program</option>
												<option value="2" >Program Group</option>
											</select>
										</div>
										<div class="col-md-1" style="padding: 0;" >
											<br />
											<!-- <button type="button" onclick="submit_form(1)" class="btn waves-effect waves-light btn-info"><?=PDF?></button> -->
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
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
	});
	
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
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
</body>

</html>