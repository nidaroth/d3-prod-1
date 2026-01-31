<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/placement_rate_report.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
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
		$campus_cond = " AND PK_CAMPUS IN($campus_id)";
	}

	if($_POST['GROUPING_TYPE'] == 1)
		$GROUPING_TYPE = "Program";
	else
		$GROUPING_TYPE = "Program Group";
		
	$DATE_TYPE = "";
	if($_POST['DATE_TYPE'] == 1)
		$DATE_TYPE = "Determination Date";
	else if($_POST['DATE_TYPE'] == 2)
		$DATE_TYPE = "Drop Date";
	else if($_POST['DATE_TYPE'] == 3)
		$DATE_TYPE = "First Term Date";
	else if($_POST['DATE_TYPE'] == 4)
		$DATE_TYPE = "Grad Date";
	else if($_POST['DATE_TYPE'] == 5)
		$DATE_TYPE = "Job Start Date";
	else if($_POST['DATE_TYPE'] == 6)
		$DATE_TYPE = "LDA";
		
	if($_POST['START_DATE'] != ''){
		$ST = date("Y-m-d",strtotime($_POST['START_DATE']));
	}
	if($_POST['END_DATE'] != ''){
		$ET = date("Y-m-d",strtotime($_POST['END_DATE']));
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
	
	$status = "";
	$res_type = $db->Execute("select STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_STUDENT_STATUS IN (".implode(",",$_POST['PK_STUDENT_STATUS']).") order by STUDENT_STATUS ASC");
	while (!$res_type->EOF) {
		if($status != '')
			$status .= ', ';
		$status .= $res_type->fields['STUDENT_STATUS'];
		$res_type->MoveNext();
	}
	
	if($_POST['FORMAT'] == 1) {
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
		require_once '../global/mpdf/vendor/autoload.php';
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Placement Rate Report</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$DATE_TYPE.' Between: '.$_POST['START_DATE'].' - '.$_POST['END_DATE'].'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Status: '.$status.'</td>
						</tr>
					</table>';
					
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>PLAC10001</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
						</tr>
					</table>';
		
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 7,
			'margin_right' => 5,
			'margin_top' => 35,
			'margin_bottom' => 15,
			'margin_header' => 3,
			'margin_footer' => 10,
			'default_font_size' => 8,
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
					<tr>
						<td width="10%" style="border-bottom:1px solid #000;">
							<br /><b><i>Program</i></b>
						</td>
						<td width="10%" style="border-bottom:1px solid #000;" >
							<br /><b><i>Program Group</i></b>
						</td>
						<td width="5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Student Count</i></b>
						</td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Available For Employment</i></b>
						</td>
						<td width="7%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Continuing Education</i></b>
						</td>
						<td width="6%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Employed In Field</i></b>
						</td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Employed In Related Field</i></b>
						</td>
						<td width="8%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Employed Out Of Field</i></b>
						</td>
						<td width="7.5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Refused Employment</i></b>
						</td>
						<td width="9.5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Unavailable For Employment</i></b>
						</td>
						<td width="5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Uknown</i></b>
						</td>
						<td width="5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Waiver</i></b>
						</td>
						<td width="5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Not Set</i></b>
						</td>
						<td width="6.5%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Placement Rate</i></b>
						</td>
					</tr>';
					
		$res = $db->Execute("CALL PLAC10001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."', '".$ET."', '".implode(",",$_POST['PK_STUDENT_STATUS'])."', '".$DATE_TYPE."', 'Summary', '".$GROUPING_TYPE."' )");
		while (!$res->EOF) {
			$b1 = "";
			$b2 = "";
			
			if(strtolower($res->fields['PROGRAM']) == "report totals:" || strtolower($res->fields['PROGRAM']) == "group totals:"){
				$b1 = "<b>";
				$b2 = "</b>";
				
				//$txt .= '<tr><td colspan="14" style="border-top:1px solid #000;" ></td></tr>';
			}
			
			$txt .= '<tr>
						<td>'.$b1.$res->fields['PROGRAM'].$b2.'</td>
						<td>'.$b1.$res->fields['PROGRAM_GROUP'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['STUDENT_COUNT'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['AVAILABLE_FOR_EMPLOYMENT'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['CONTINUING_EDUCATION'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['EMPLOYED_IN_FIELD'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['EMPLOYED_IN_RELATED_FIELD'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['EMPLOYED_OUT_OF_FIELD'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['REFUSED_EMPLOYMENT'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['UNAVAILABLE_FOR_EMPLOYMENT'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['UKNOWN'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['WAIVER'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['NOT_SET'].$b2.'</td>
						<td align="right" >'.$b1.$res->fields['PLACEMENT_RATE'].' %'.$b2.'</td>
					</tr>';
		
			$res->MoveNext();
		}
		
		$txt .= '</table>';
	
		$mpdf->WriteHTML($txt);
		
		$mpdf->Output('Placement Rate.pdf', 'D');
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
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
		$file_name 		= 'Placement Rate.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= -1;

		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Program Group';
		$width[]   = 20;
		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student Id';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Student Status';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Date Type';
		$width[]   = 20;
		$heading[] = 'Date';
		$width[]   = 20;
		$heading[] = 'Placement Status';
		$width[]   = 20;
		$heading[] = 'Home Phone';
		$width[]   = 20;
		$heading[] = 'Mobile Phone';
		$width[]   = 20;
		$heading[] = 'Email';
		$width[]   = 20;
		$heading[] = 'Available For Employment';
		$width[]   = 20;
		$heading[] = 'Continuing Education';
		$width[]   = 20;
		$heading[] = 'Employed In Field';
		$width[]   = 20;
		$heading[] = 'Employed In Related Field';
		$width[]   = 20;
		$heading[] = 'Employed Out Of Field';
		$width[]   = 20;
		$heading[] = 'Refused Employment';
		$width[]   = 20;
		$heading[] = 'Unavailable For Employment';
		$width[]   = 20;
		$heading[] = 'Uknown';
		$width[]   = 20;
		$heading[] = 'Waiver';
		$width[]   = 20;
		$heading[] = 'Not Set';
		$width[]   = 20;
		$heading[] = 'Placed';
		$width[]   = 20;
		$heading[] = 'Not Placed';
		$width[]   = 20;
		
		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
			$i++;
		}	

		$objPHPExcel->getActiveSheet()->freezePane('A1');
	
		$res_fa = $db->Execute("CALL PLAC10001(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."', '".$ET."', '".implode(",",$_POST['PK_STUDENT_STATUS'])."', '".$DATE_TYPE."', 'Detail', '".$GROUPING_TYPE."' )");
		while (!$res_fa->EOF) { 
			
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PROGRAM_GROUP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_ID']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CAMPUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['STUDENT_STATUS']);
				
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['FIRST_TERM'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['FIRST_TERM'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['DATE_TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			if($res_fa->fields['DATE'] != '') {
				$dateValue = floor( PHPExcel_Shared_Date::PHPToExcel(DateTime::createFromFormat('Y-m-d', $res_fa->fields['DATE'])));
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($dateValue);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no) ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
			}
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PLACEMENT_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['HOME_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['MOBILE_PHONE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['EMAIL']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['AVAILABLE_FOR_EMPLOYMENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['CONTINUING_EDUCATION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['EMPLOYED_IN_FIELD']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['EMPLOYED_IN_RELATED_FIELD']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['EMPLOYED_OUT_OF_FIELD']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['REFUSED_EMPLOYMENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['UNAVAILABLE_FOR_EMPLOYMENT']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['UKNOWN']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['WAIVER']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NOT_SET']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['PLACED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_fa->fields['NOT_PLACED']);
			
			$res_fa->MoveNext();
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
	<title><?=MNU_PLACEMENT_RATE_REPORT ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS, #advice-required-entry-PK_STUDENT_STATUS{position: absolute;top: 57px; width:150px}
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
							<?=MNU_PLACEMENT_RATE_REPORT ?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row form-group">
										<div class="col-md-2 ">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<div class="col-md-2 ">
											<?=STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-8 text-right">
											<button type="button" onclick="window.location.href='placement_rate_report_setup'" class="btn waves-effect waves-light btn-info"><?=REPORT_SETUP?></button>
											<br />
											<? $res = $db->Execute("select * from S_PLACEMENT_RATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res->fields['EDITED_ON'] != '' && $res->fields['EDITED_ON'] != '0000-00-00 00:00:00'){
												$EDITED_BY	= $res->fields['EDITED_BY'];
												$EDITED_ON	= $res->fields['EDITED_ON'];
												$res_user = $db->Execute("SELECT CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME FROM S_EMPLOYEE_MASTER, Z_USER WHERE PK_USER = '$EDITED_BY' AND Z_USER.ID =  S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE IN (1,2) "); 

												$EDITED_BY	= $res_user->fields['NAME']; 
												echo "Edited: ".$EDITED_BY." ".date("m/d/Y",strtotime($EDITED_ON));
											} ?>
										</div>
									</div>
									
									<div class="row form-group">
										<div class="col-md-2">
											<?=DATE_TYPE ?>
											<select id="DATE_TYPE" name="DATE_TYPE" class="form-control" >
												<option value="1" >Determination Date</option>
												<option value="2" >Drop Date</option>
												<option value="3" >First Term Date</option>
												<option value="4" >Grad Date</option>
												<option value="5" >Job Start Date</option>
												<option value="6" >LDA</option>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center" >
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" placeholder="" >
										</div>
										
										<div class="col-md-2 align-self-center" >
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" placeholder="" >
										</div>
										
										<!--<div class="col-md-2">
											<?=REPORT_TYPE ?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="1" >Detail</option>
												<option value="2" >Summary</option>
											</select>
										</div>-->
										
										<div class="col-md-2">
											<?=GROUP_BY ?>
											<select id="GROUPING_TYPE" name="GROUPING_TYPE" class="form-control" >
												<option value="1" >By Program</option>
												<option value="2" >By Program Group</option>
											</select>
										</div>
										
										<div class="col-md-2" style="padding: 0;" >
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
		
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 1,
			nSelectedText: '<?=STATUS?> selected'
		});
	});
	</script>
</body>

</html>
