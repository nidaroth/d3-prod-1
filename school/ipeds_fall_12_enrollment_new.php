<?
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ipeds_fall_collections_setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['IPEDS'] == 0 || check_access('MANAGEMENT_IPEDS') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;

	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}

	// DIAM-1470
	$PK_STUDENT_STATUS 	 = "";
	if(!empty($_POST['PK_STUDENT_STATUS'])){
		$PK_STUDENT_STATUS 	 = implode(",",$_POST['PK_STUDENT_STATUS']);
	}
	// End DIAM-1470
	
	$REPORT_TYPE = "";
	$REPORT_NAME = "";
	if($_POST['REPORT_TYPE'] == 1) {
		$REPORT_TYPE = "Full Time";
		$REPORT_NAME = "Full Time Students";
	} else if($_POST['REPORT_TYPE'] == 2) {
		$REPORT_TYPE = "Part Time";
		$REPORT_NAME = "Part Time Students";
	} else if($_POST['REPORT_TYPE'] == 3) {
		$REPORT_TYPE = "Gender Other/Unknown";
		$REPORT_NAME = "Gender Other/Unknown Students";
	} else if($_POST['REPORT_TYPE'] == 4) {
		$REPORT_TYPE = "Distance Education";
		$REPORT_NAME = "Distance Education Students";
	} else if($_POST['REPORT_TYPE'] == 5) {
		$REPORT_TYPE = "Undergraduate Students";
		$REPORT_NAME = "Undergraduate Students";
	}
	
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

	if($_POST['FORMAT'] == 1) {
		$browser = '';
		if(stripos($_SERVER['HTTP_USER_AGENT'],"chrome") != false)
			$browser =  "chrome";
		else if(stripos($_SERVER['HTTP_USER_AGENT'],"Safari") != false)
			$browser = "Safari";
		else
			$browser = "firefox";
		
		require_once '../global/mpdf/vendor/autoload.php';
		
		$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
		$PDF_LOGO 	 = $res->fields['PDF_LOGO'];

		$logo = "";
		if($PDF_LOGO != '')
			$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
	
		$header = '<table width="100%" >
						<tr>
							<td width="20%" valign="top" >'.$logo.'</td>
							<td width="30%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="50%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>IPEDS - Fall</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >12 Month Enrollment</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$REPORT_NAME.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Between: '.$_POST['START_DATE'].' - '.$_POST['END_DATE'].'</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus: '.$campus_name.'</td>
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP20001a</i></td>
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
			'default_font_size' => 9
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
	
		
		$i = 0;
		if($_POST['REPORT_TYPE'] == 3) {
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="50%">';
			$txt .= '<tr>
						<td width="20%" style="border-bottom:1px solid #000;">
							<br /><b><i>Gender</i></b>
						</td>
						<td width="40%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Number Of Students</i></b>
						</td>
					</tr>';
					
			$res = $db->Execute("CALL COMP20001a(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','".$REPORT_TYPE."')");
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['Gender'].'</td>
							<td align="right" >'.$res->fields['Unduplicated_Count'].'</td>
						</tr>';
				$res->MoveNext();
			}
			
			$txt .= '</table>';
		} else if($_POST['REPORT_TYPE'] == 5) {
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">';

			$gender = "";
			$res = $db->Execute("CALL COMP20001a(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','".$REPORT_TYPE."')");
			while (!$res->EOF) { 
				if($gender != $res->fields['GENDER'] && strtolower(trim($res->fields['GENDER'])) != "zzz") {
					$gender = $res->fields['GENDER'];
			
					$txt .= '<tr>
							<td width="40%" style="border-bottom:1px solid #000;">
								<br /><b><i>'.$gender.'</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<br /><b><i>Full-Time</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<br /><b><i>Part-Time</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<br /><b><i>Total</i></b>
							</td>
						</tr>';
				}	
				
				$bold1 = "";
				$bold2 = "";
				if($res->fields['RECORD_TYPE'] == "Detail")
					$race = $res->fields['RACE'];
				else {
					$race = "Total ".$gender;
					
					$bold1 = "<b>";
					$bold2 = "</b>";
				}
				
				if(strtolower(trim($res->fields['GENDER'])) == "zzz") {
					$txt .= '<tr><td colspan="4" ><br /><br /></td></tr>';
					$race = "Total Men+ Women";
					
					$bold1 = "<b>";
					$bold2 = "</b>";
				}
				
				$txt .= '<tr>
							<td >'.$bold1.$race.$bold2.'</td>
							<td align="right" >'.$bold1.$res->fields['FULL_TIME'].$bold2.'</td>
							<td align="right" >'.$bold1.$res->fields['PART_TIME'].$bold2.'</td>
							<td align="right" >'.$bold1.$res->fields['TOTAL'].$bold2.'</td>
						</tr>';
				$res->MoveNext();
			}
			
			$txt .= '</table>';
		} else if($_POST['REPORT_TYPE'] == 4) {
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">';
			$txt .= '<tr>
						<td width="60%" style="border-bottom:1px solid #000;">
							<br /><b><i></i></b>
						</td>
						<td width="20%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Non-Degree/Certificate</i></b>
						</td>
						<td width="20%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Degree/Certificate</i></b>
						</td>
					</tr>';
					
			$res = $db->Execute("CALL COMP20001a(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','".$REPORT_TYPE."')");
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td ><b>'.$res->fields['DistanceEducation'].'</b></td>
							<td align="right" >'.$res->fields['NonDegreeCertificate'].'</td>
							<td align="right" >'.$res->fields['DegreeCertificate'].'</td>
						</tr>';
				$res->MoveNext();
			}
			
			$txt .= '</table>';
		} else {
			
			$res = $db->Execute("CALL COMP20001a(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','".$REPORT_TYPE."')");
			$BATCH_ID = $res->fields['vThisBatchID'];

			$db->close();
			$db->connect($db_host,'root',$db_pass,$db_name);
		
			/* Ticket # 1769 */
			/*$res_type = $db->Execute("select PK_GENDER, GENDER from Z_GENDER WHERE 1=1");
			while (!$res_type->EOF) {
				$GENDER_ARR[] = $res_type->fields['GENDER'];
				$res_type->MoveNext();
			}*/
			/* Ticket # 1769 */
			
			$GENDER_ARR[] = "Men";
			$GENDER_ARR[] = "Women";
		
			$TOT_FIRSTTIME 				= 0;
			$TOT_TRANSFERIN 			= 0;
			$TOT_CONTINUINGRETURNING 	= 0;
			$TOT_NONDEGREECERTIFICATE 	= 0;
			$TOT_UNDERGRADUATESTUDENT	= 0;
			
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">';
			foreach($GENDER_ARR as $GENDER){
				if($i == 1)
					$txt .= '<tr><td colspan="6" ><br /><br /></td></tr>';
				$txt .= '<tr>
							<td width="50%" style="border-bottom:1px solid #000;">
								<br /><b><i>'.$GENDER.'</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<br /><b><i>First-Time</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000; align="right"">
								<br /><b><i>Transfer-In</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right">
								<b><i>Continuing Returning</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Non-Degree Non-Certificate</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Undergraduate Students</i></b>
							</td>
						</tr>';

				$FIRSTTIME 				= 0;
				$TRANSFERIN 			= 0;
				$CONTINUINGRETURNING 	= 0;
				$NONDEGREECERTIFICATE 	= 0;
				$UNDERGRADUATESTUDENT	= 0;
				
				$res = $db->Execute("SELECT * FROM S_TEMP_COMP20001 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND GENDER = '$GENDER' ORDER BY IPEDS_SORT_ORDER ");
				while (!$res->EOF) { 
					$FIRSTTIME 				+= $res->fields['FIRSTTIME'];
					$TRANSFERIN 			+= $res->fields['TRANSFERIN'];
					$CONTINUINGRETURNING 	+= $res->fields['CONTINUINGRETURNING'];
					$NONDEGREECERTIFICATE 	+= $res->fields['NONDEGREECERTIFICATE'];
					$UNDERGRADUATESTUDENT	+= $res->fields['UNDERGRADUATESTUDENT'];
					
					$txt .= '<tr>
								<td >'.$res->fields['RACE'].'</td>
								<td align="right" >'.$res->fields['FIRSTTIME'].'</td>
								<td align="right" >'.$res->fields['TRANSFERIN'].'</td>
								<td align="right" >'.$res->fields['CONTINUINGRETURNING'].'</td>
								<td align="right" >'.$res->fields['NONDEGREECERTIFICATE'].'</td>
								<td align="right" >'.$res->fields['UNDERGRADUATESTUDENT'].'</td>
							</tr>';
					$res->MoveNext();
				}
				
				$TOT_FIRSTTIME 				+= $FIRSTTIME;
				$TOT_TRANSFERIN 			+= $TRANSFERIN;
				$TOT_CONTINUINGRETURNING 	+= $CONTINUINGRETURNING;
				$TOT_NONDEGREECERTIFICATE 	+= $NONDEGREECERTIFICATE;
				$TOT_UNDERGRADUATESTUDENT 	+= $UNDERGRADUATESTUDENT;
				
				$txt .= '<tr>
							<td ><b>Total '.$GENDER.'</b></td>
							<td align="right" ><b>'.$FIRSTTIME.'</b></td>
							<td align="right" ><b>'.$TRANSFERIN.'</b></td>
							<td align="right" ><b>'.$CONTINUINGRETURNING.'</b></td>
							<td align="right" ><b>'.$NONDEGREECERTIFICATE.'</b></td>
							<td align="right" ><b>'.$UNDERGRADUATESTUDENT.'</b></td>
						</tr>';
				
				$i++;
			}
			
			$txt .= '<tr>
						<td width="50%" ><b>Total Men + Women</b></td>
						<td width="10%" align="right" ><b>'.$TOT_FIRSTTIME.'</b></td>
						<td width="10%" align="right" ><b>'.$TOT_TRANSFERIN.'</b></td>
						<td width="10%" align="right" ><b>'.$TOT_CONTINUINGRETURNING.'</b></td>
						<td width="10%" align="right" ><b>'.$TOT_NONDEGREECERTIFICATE.'</b></td>
						<td width="10%" align="right" ><b>'.$TOT_UNDERGRADUATESTUDENT.'</b></td>
					</tr>
				</table>';
		}
		
		$db->Execute("DELETE FROM S_TEMP_COMP20001 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		
		$mpdf->WriteHTML($txt);
		$mpdf->Output($REPORT_NAME.'.pdf', 'D');
		
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
		$REPORT_NAMER =[
			"",
			"_Full_Time",
			"_Part_Time",
			"_Gender_Other_Unknown",
			"_Distance_Education",
			"_Undergraduate_Students"
		];
		$file_name 		= 'IPEDS Fall 12 Month Enrollment'.$REPORT_NAMER[$_POST['REPORT_TYPE']].'.xlsx';
		$outputFileName = $dir.$file_name; 
		$outputFileName = str_replace(pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );  

		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

		$line 	= 1;	
		$index 	= 0;
		
		$cell_no = $cell[$index].$line;
		$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue("Campus: ".$campus_name);
		$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);

		$line++;	
		$index 	= -1;
		$heading[] = 'Student Name';
		$width[]   = 20;
		$heading[] = 'First Term Date';
		$width[]   = 20;
		$heading[] = 'Gender';
		$width[]   = 20;
		$heading[] = 'IPEDS Gender';
		$width[]   = 20;
		$heading[] = 'Full/Part Time';
		$width[]   = 20;
		$heading[] = 'State'; // DIAM-1470
		$width[]   = 20;
		$heading[] = 'Race';
		$width[]   = 20;
		$heading[] = 'Distance Learning';
		$width[]   = 20;
		$heading[] = 'First Time';
		$width[]   = 20;
		$heading[] = 'Transfer In';
		$width[]   = 20;
		$heading[] = 'Continuing Returning';
		$width[]   = 20;
		$heading[] = 'Non Degree Certificate';
		$width[]   = 20;
		$heading[] = 'Undergraduate Students';
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
		$res = $db->Execute("CALL COMP20001a(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','EXCEL', '".$PK_STUDENT_STATUS."')"); // DIAM-1470, Added new parameter PK_STUDENT_STATUS in SP
		while (!$res->EOF) {
			$line++;
			$index = -1;
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Gender']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IPEDSGender']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FullPartTime']);

			// DIAM-1470
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['StateName']);
			// End DIAM-1470
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Race']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DistanceLearning']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FirstTime']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TransferIn']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ContinuingReturning']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NonDegreeCertificate']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UndergraduateStudent']);
			
			$res->MoveNext();
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
	<title><?=MNU_IPEDS_FALL_ENROLLMENT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
		#advice-required-entry-PK_STUDENT_STATUS {position: absolute;top: 55px;width: 142px}
		.dropdown-menu>li>a { white-space: nowrap; max-width: 90%}
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
							<?=MNU_IPEDS_FALL_ENROLLMENT?>  2023-2024 
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
											<?=REPORT_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" >
												<option value="1">Full Time</option>
												<option value="2">Part Time</option>
												
												<option value="3">Gender Other/Unknown</option>
												<option value="4">Distance Education</option>
												<option value="5">Undergraduate Students</option>
											</select>
										</div>
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2">
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>

										<!-- DIAM-1470 -->
										<div class="col-md-2 ">
											<?=STATUS?>
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control required-entry" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										<!-- End DIAM-1470 -->
										
										<div class="col-md-2" style="padding: 0;" >
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

		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});

		// DIAM-1470
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		// End DIAM-1470

	});
	</script>

</body>

</html>