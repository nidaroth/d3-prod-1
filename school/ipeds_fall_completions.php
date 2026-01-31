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
	
	$REPORT_TYPE = "";
	$REPORT_NAME = "";
	if($_POST['REPORT_TYPE'] == 1) {
		$REPORT_TYPE = "Completions CIP Data - Report";
		$REPORT_NAME = "Completions - CIP Data";
	} else if($_POST['REPORT_TYPE'] == 2) {
		$REPORT_TYPE = "Completions All Completers - Report";
		$REPORT_NAME = "Completions - All Completers";
	} else if($_POST['REPORT_TYPE'] == 3) {
		$REPORT_TYPE = "Completions Completers by Level - Report";
		$REPORT_NAME = "Completions - Completers by Level";
	} else if($_POST['REPORT_TYPE'] == 4) {
		$REPORT_TYPE = "Gender Other/Unknown";
		$REPORT_NAME = "Gender Other/Unknown";
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i>COMP20002</i></td>
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
		
		$res = $db->Execute("CALL COMP20002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','".$REPORT_TYPE."')");
		$BATCH_ID = $res->fields['vThisBatchID'];
	
		$db->close();
		$db->connect($db_host,'root',$db_pass,$db_name);
		
		if($_POST['REPORT_TYPE'] == 1) {
			//Completions CIP Data
			
			$PROGRAM_ARR = array();
			$DESC_ARR 	 = array();
			$CIP_ARR 	 = array();
			$AWARD_ARR   = array();
			$res = $db->Execute("SELECT PROGRAM, PROGRAMDESCRIPTION, CIP, IPEDSAWARDLEVEL FROM S_TEMP_COMP20002A WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY PROGRAM ORDER BY PROGRAM ASC");
			while (!$res->EOF) {
				$PROGRAM_ARR[] 	= $res->fields['PROGRAM'];
				$DESC_ARR[] 	= $res->fields['PROGRAMDESCRIPTION'];
				$CIP_ARR[] 		= $res->fields['CIP'];
				$AWARD_ARR[] 	= $res->fields['IPEDSAWARDLEVEL'];
				
				$res->MoveNext();
			}
			
			$i = 0;
			foreach($PROGRAM_ARR as $PROGRAM) {
				$mpdf->AddPage();
				$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td width="15%">
									<b><i>Program: </i></b>
								</td>
								<td width="85%">'.$PROGRAM.' - '.$DESC_ARR[$i].'</td>
							</tr>
							<tr>
								<td >
									<b><i>CIP: </i></b>
								</td>
								<td >'.$CIP_ARR[$i].'</td>
							</tr>
							<tr>
								<td >
									<b><i>Award Level: </i></b>
								</td>
								<td >'.$AWARD_ARR[$i].'</td>
							</tr>
						</table>
						<br /><br />
						<table border="0" cellspacing="0" cellpadding="4" width="60%">
							<tr>
								<td width="50%" style="border-bottom:1px solid #000;">
									<b><i>Ethnicity</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;" align="right" >
									<b><i>Men</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;" align="right" >
									<b><i>Women</i></b>
								</td>
							</tr>';
							
				$TOT_MEN 	= 0;
				$TOT_WOMEN 	= 0;
				$res = $db->Execute("SELECT * FROM S_TEMP_COMP20002A WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND PROGRAM = '$PROGRAM' ORDER BY IPEDS_SORT_ORDER ");
				while (!$res->EOF) { 
					$txt .= '<tr>
								<td >'.$res->fields['RACE'].'</td>
								<td align="right" >'.$res->fields['MEN'].'</td>
								<td align="right" >'.$res->fields['WOMEN'].'</td>
							</tr>';
							
					$TOT_MEN 	+= $res->fields['MEN'];
					$TOT_WOMEN 	+= $res->fields['WOMEN'];
					$res->MoveNext();
				}
				
				$txt .= '<tr>
							<td ><b>Total</b></td>
							<td align="right" ><b>'.$TOT_MEN.'</b></td>
							<td  align="right" ><b>'.$TOT_WOMEN.'</b></td>
						</tr>
					</table>';
					
				$mpdf->WriteHTML($txt);
				
				$i++;
			}
			
			$db->Execute("DELETE FROM S_TEMP_COMP20002A WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 2) {
			//Completions All Completers
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="60%">
						<tr>
							<td width="50%" style="border-bottom:1px solid #000;">
								<b><i>Ethnicity</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Men</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;" align="right" >
								<b><i>Women</i></b>
							</td>
						</tr>';
			
			$TOT_MEN 	= 0;
			$TOT_WOMEN 	= 0;
			$res = $db->Execute("SELECT * FROM S_TEMP_COMP20002B WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ORDER BY IPEDS_SORT_ORDER ");
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['RACE'].'</td>
							<td align="right" >'.$res->fields['MEN'].'</td>
							<td align="right" >'.$res->fields['WOMEN'].'</td>
						</tr>';
						
				$TOT_MEN 	+= $res->fields['MEN'];
				$TOT_WOMEN 	+= $res->fields['WOMEN'];
				$res->MoveNext();
			}
			
			$txt .= '<tr>
						<td ><b>Total</b></td>
						<td align="right" ><b>'.$TOT_MEN.'</b></td>
						<td  align="right" ><b>'.$TOT_WOMEN.'</b></td>
					</tr>
				</table>';
				
			$mpdf->WriteHTML($txt);
			$db->Execute("DELETE FROM S_TEMP_COMP20002B WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
			
		} else if($_POST['REPORT_TYPE'] == 3) {
			//Completions Completers by Level			
			$AWARD_ARR   = array();
			$res = $db->Execute("SELECT IPEDSAWARDLEVEL FROM S_TEMP_COMP20002C WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' GROUP BY IPEDSAWARDLEVEL ORDER BY IPEDSAWARDLEVEL ASC");
			while (!$res->EOF) {
				$AWARD_ARR[] 	= $res->fields['IPEDSAWARDLEVEL'];
				
				$res->MoveNext();
			}
			
			$i = 0;
			foreach($AWARD_ARR as $AWARD) {
				$mpdf->AddPage();
				$txt = '<table border="0" cellspacing="0" cellpadding="4" width="100%">
							<tr>
								<td >
									<b><i>Award Level: </i></b>
								</td>
								<td >'.$AWARD.'</td>
							</tr>
						</table>
						<br /><br />';
				
				$REPORTGROUP[] = 'By Gender';
				$REPORTGROUP[] = 'Race/Ethnicity';
				$REPORTGROUP[] = 'By Age';
				
				foreach($REPORTGROUP as $REPORTGROUP1) {
					$txt .= '<table border="0" cellspacing="0" cellpadding="4" width="50%">
								<tr>
									<td width="50%" style="border-bottom:1px solid #000;">
										<b><i>'.$REPORTGROUP1.'</i></b>
									</td>
									<td width="50%" style="border-bottom:1px solid #000;" align="right" >
										<b><i>Number of Students</i></b>
									</td>
								</tr>';
							
					$TOT = 0;
					$cond3 = "";
					if(trim($AWARD) == "")
						$cond3 = " AND (IPEDSAWARDLEVEL = '$AWARD' OR ISNULL(IPEDSAWARDLEVEL)) ";
					else
						$cond3 = " AND IPEDSAWARDLEVEL = '$AWARD' ";
						
					$res = $db->Execute("SELECT * FROM S_TEMP_COMP20002C WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' AND REPORTGROUP = '$REPORTGROUP1' $cond3 ");
					while (!$res->EOF) { 
						$txt .= '<tr>
									<td >'.$res->fields['REPORTDETAIL'].'</td>
									<td align="right" >'.$res->fields['STUDENTCOUNT'].'</td>
								</tr>';
								
						$TOT += $res->fields['STUDENTCOUNT'];
						$res->MoveNext();
					}
					
					$txt .= '<tr>
								<td ><b>Total</b></td>
								<td align="right" ><b>'.$TOT.'</b></td>
							</tr>
						</table><br /><br />';
				}
				
				$mpdf->WriteHTML($txt);
				
				$i++;
			}
			
			$db->Execute("DELETE FROM S_TEMP_COMP20002C WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BATCH_ID = '$BATCH_ID' ");
		} else if($_POST['REPORT_TYPE'] == 4) {
			$txt = '<table border="0" cellspacing="0" cellpadding="4" width="50%">';
			$txt .= '<tr>
						<td width="20%" style="border-bottom:1px solid #000;">
							<br /><b><i>Sex</i></b>
						</td>
						<td width="40%" style="border-bottom:1px solid #000;" align="right" >
							<br /><b><i>Number Of Students</i></b>
						</td>
					</tr>';
					
			while (!$res->EOF) { 
				$txt .= '<tr>
							<td >'.$res->fields['Gender'].'</td>
							<td align="right" >'.$res->fields['Unduplicated_Count'].'</td>
						</tr>';
				$res->MoveNext();
			}
			
			$txt .= '</table>';
			$mpdf->WriteHTML($txt);
		}

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
			"CIP_Data",
			"All_Completers",
			"Completers_by_Level",
			"Gender_Other_Unknown"
		];
		$file_name 		= 'IPEDS Fall Completions_'.$REPORT_NAMER[$_POST['REPORT_TYPE']].'.xlsx';
		$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

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
		$heading[] = 'Start Date';
		$width[]   = 20;
		$heading[] = 'End Date';
		$width[]   = 20;
		$heading[] = 'End Type';
		$width[]   = 20;
		$heading[] = 'Sex';
		$width[]   = 20;
		$heading[] = 'IPEDS Sex';
		$width[]   = 20;
		$heading[] = 'Race';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Program Description';
		$width[]   = 20;
		$heading[] = 'CIP';
		$width[]   = 20;
		$heading[] = 'Date Of Birth';
		$width[]   = 20;
		$heading[] = 'Age At End Date';
		$width[]   = 20;
		$heading[] = 'IPEDS Award Level';
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
		// echo "CALL COMP20002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','EXCEL')";exit;
		$res = $db->Execute("CALL COMP20002(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', '".$ST."','".$ET."','EXCEL')");
		while (!$res->EOF) {
			$line++;
			$index = -1;
			
			$StudentStartDate = '';
			if($res->fields['StudentStartDate'] != '' && $res->fields['StudentStartDate'] != '0000-00-00')
				$StudentStartDate = date("Y-m-d",strtotime($res->fields['StudentStartDate']));
				
			$StudentEndDate = '';
			if($res->fields['StudentEndDate'] != '' && $res->fields['StudentEndDate'] != '0000-00-00')
				$StudentEndDate = date("Y-m-d",strtotime($res->fields['StudentEndDate']));
				
			$DateOfBirth = '';
			if($res->fields['DateOfBirth'] != '' && $res->fields['DateOfBirth'] != '0000-00-00')
				$DateOfBirth = date("Y-m-d",strtotime($res->fields['DateOfBirth']));
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Student']);
				
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentStartDate);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($StudentEndDate);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EndDateType']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Gender']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IPEDSGender']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Race']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['Program']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ProgramDescription']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DateOfBirth);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['AgeAtEndDate']);
			

			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IPEDSAwardLevel']);
			
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
	<title><?=MNU_IPEDS_FALL_COMPLETIONS?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
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
							<?=MNU_IPEDS_FALL_COMPLETIONS?>   2024-2025 <!-- DIAM-2397 > DIAM-2401 -->
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
												<option value="1">Completions CIP Data</option>
												<option value="2">Completions All Completers</option>
												<option value="3">Completions Completers by Level</option>
												<option value="4">Gender Other/Unknown</option>
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
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC"); // DIAM-2397 > DIAM-2401
												while (!$res_type->EOF) { 
													 // DIAM-2397 > DIAM-2401
													$ACTIVE 	= $res_type->fields['ACTIVE'];
													if ($ACTIVE == '0') {
														$Status = '(Inactive)';
													} else {
														$Status = '';
													} // DIAM-2397 > DIAM-2401
													?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['CAMPUS_CODE']. ' ' .$Status ?></option> 
													<!-- DIAM-2397 > DIAM-2401 -->
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
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
	});
	</script>

</body>

</html>