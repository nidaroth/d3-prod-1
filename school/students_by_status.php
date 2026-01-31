<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}


if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
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
	
	$date_label1 = "";
	$date_label  = "";
	$date_cond   = "";
	if($_POST['REPORT_OPTION'] == 1) {
		$AS_OF_DATE1 = date("Y-m-d",strtotime($_POST['AS_OF_DATE']));
		$date_cond   = " AND DATE_FORMAT(CHANGED_ON,'%Y-%m-%d') <= '$AS_OF_DATE1' ";
		$date_label  = "As of Date: ".$_POST['AS_OF_DATE'];
		$date_label1 = "Status as of Selected Date";
	} else {
		$DATE1 = date("Y-m-d",strtotime($_POST['START_DATE']));
		$DATE2 = date("Y-m-d",strtotime($_POST['END_DATE']));
		$date_cond 		= " AND DATE_FORMAT(CHANGED_ON,'%Y-%m-%d') BETWEEN '$DATE1' AND '$DATE2' ";
		$date_label 	= "Between: ".$_POST['START_DATE']." - ".$_POST['END_DATE'];
		$date_label1 	= "Status Between Selected Dates";
	}
	
	$all_enrollments = "No";
	if($_POST['INCLUDE_ALL_ENROLLMENTS'] == 1)
		$all_enrollments = "Yes";
	
	$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	$cond = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";

	$prog_query = "select M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, M_CAMPUS_PROGRAM.CODE, M_CAMPUS_PROGRAM.DESCRIPTION 
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0 AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $cond $campus_cond1 GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ORDER BY M_CAMPUS_PROGRAM.CODE ";

	$stud_query = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,SSN,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, STUDENT_GROUP, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1, IF(MIDPOINT_DATE = '0000-00-00', '',DATE_FORMAT(MIDPOINT_DATE,'%Y-%m-%d' )) AS MIDPOINT_DATE, IF(EXPECTED_GRAD_DATE = '0000-00-00', '',DATE_FORMAT(EXPECTED_GRAD_DATE,'%Y-%m-%d' )) AS EXPECTED_GRAD_DATE , M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS,IF(STATUS_DATE = '0000-00-00','',DATE_FORMAT(STATUS_DATE,'%Y-%m-%d' )) AS STATUS_DATE, IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE,'%Y-%m-%d' )) AS GRADE_DATE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA,'%Y-%m-%d' )) AS LDA, IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE,'%m/%d/%Y' )) AS DETERMINATION_DATE, IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE,'%Y-%m-%d' )) AS DROP_DATE, HOME_PHONE, CELL_PHONE, EMAIL, CAMPUS_CODE, M_CAMPUS_PROGRAM.CODE   
	FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1'
	, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
	 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.ARCHIVED = 0  AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $cond $campus_cond1 ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, S_TERM_MASTER.BEGIN_DATE ASC, M_CAMPUS_PROGRAM.CODE ASC ";
		
	if($_POST['FORMAT'] == 1) {
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Students by Status</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >'.$date_label.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Include All Enrollments: '.$all_enrollments.'</td>
									</tr>
								</table>
							</td>
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
							<td width="33%" valign="top" style="font-size:10px;" align="center" ><i></i></td>
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
			'default_font_size' => 9,
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);

		$txt  = '<table border="0" cellspacing="0" cellpadding="2" width="100%">
					<thead>
						<tr>
							<td width="17%" style="border-bottom:1px solid #000;">
								<b><i>Student</i></b>
							</td>
							<td width="11%" style="border-bottom:1px solid #000;">
								<b><i>Student ID</i></b>
							</td>
							<td width="24%" style="border-bottom:1px solid #000;">
								<b><i>Enrollment</i></b>
							</td>
							
							<td width="20%" style="border-bottom:1px solid #000;">
								<b><i>'.$date_label1.'</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>Grad Date</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>LDA</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Determination Date</i></b>
							</td>
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>Drop Date</i></b>
							</td>
						</tr>
					</thead>';
					
					$res_stud = $db->Execute($stud_query);
					while (!$res_stud->EOF) { 
						$PK_STUDENT_ENROLLMENT = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
						$res_camp = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS  WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS");
						
						$res_log = $db->Execute("SELECT NEW_VALUE FROM S_STUDENT_TRACK_CHANGES  WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND FIELD_NAME = 'STATUS' $date_cond ORDER BY CHANGED_ON DESC");
						
						if($res_log->RecordCount() > 0)
							$sts = $res_log->fields['NEW_VALUE'];
						else
							$sts = "No Status History as of Selected Date";
							$txt .= '<tr>
										<td >'.$res_stud->fields['STU_NAME'].'</td>
										<td >'.$res_stud->fields['STUDENT_ID'].'</td>
										<td >'.$res_stud->fields['BEGIN_DATE_1'].' - '.$res_stud->fields['CODE'].' - '.$res_stud->fields['STUDENT_STATUS'].' - '.$res_camp->fields['CAMPUS_CODE'].'</td>
										<td >'.$sts.'</td>
										<td >'.$res_stud->fields['GRADE_DATE'].'</td>
										<td >'.$res_stud->fields['LDA'].'</td>
										<td >'.$res_stud->fields['DETERMINATION_DATE'].'</td>
										<td >'.$res_stud->fields['DROP_DATE'].'</td>
									</tr>';
						//}
						$res_stud->MoveNext();
					}
				
				$txt .= '</table>';
				
			//echo $txt;exit;
		$file_name = 'Students by Status.pdf';
		$mpdf->WriteHTML($txt);
		$mpdf->Output($file_name, 'D');

		return $file_name;
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
		$file_name 		= 'Students by Status.xlsx';
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
		$index 	= -1;

		$heading[] = 'Student';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus Code';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Grad Date';
		$width[]   = 20;
		$heading[] = 'LDA';
		$width[]   = 20;
		$heading[] = 'Determination Date';
		$width[]   = 20;
		$heading[] = 'Drop Date';
		$width[]   = 20;
		$heading[] = 'Home Phone';
		$width[]   = 20;
		$heading[] = 'Mobile Phone';
		$width[]   = 20;
		$heading[] = 'Email';
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
		
		$res = $db->Execute($stud_query);
		while (!$res->EOF) {
			$PK_STUDENT_ENROLLMENT = $res->fields['PK_STUDENT_ENROLLMENT'];
			$res_log = $db->Execute("SELECT NEW_VALUE FROM S_STUDENT_TRACK_CHANGES  WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND FIELD_NAME = 'STATUS' $date_cond ORDER BY CHANGED_ON DESC");
			
			if($res_log->RecordCount() > 0)
			$stsexp = $res_log->fields['NEW_VALUE'];
			else
			$stsexp = "No Status History as of Selected Date";
			//if($res_log->RecordCount() > 0) {
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STU_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['BEGIN_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($stsexp);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADE_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LDA']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DETERMINATION_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DROP_DATE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL']);
			//}
			
			$res->MoveNext();
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_STUDENTS_BY_STATUS?> | <?=$title?></title>
	<style>
		#advice-required-entry-PK_CAMPUS{position: absolute;top: 29px;}
		
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; }
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=MNU_STUDENTS_BY_STATUS?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" >
										<div class="col-md-2 ">
											<?=REPORT_OPTION ?>
											<select id="REPORT_OPTION" name="REPORT_OPTION" class="form-control" onchange="show_fields(this.value)" >
												<option value="1">As of Date</option>
												<option value="2">Date Range</option>
											</select>
										</div>
										
										<div class="col-md-2 " id="AS_OF_DATE_DIV" >	
											<br />
											<input type="text" class="form-control date required-entry" id="AS_OF_DATE" name="AS_OF_DATE" value="" placeholder="<?=AS_OF_DATE?>" >
										</div>
										
										<div class="col-md-2 " id="START_DATE_DIV" style="display:none" >	
											<br />
											<input type="text" class="form-control date" id="START_DATE" name="START_DATE" value="" placeholder="<?=START_DATE?>" >
										</div>
										
										<div class="col-md-2 " id="END_DATE_DIV" style="display:none" >	
											<br />
											<input type="text" class="form-control date" id="END_DATE" name="END_DATE" value="" placeholder="<?=END_DATE?>" >
										</div>
										
										
										<div class="col-md-2 align-self-center" >
											<br />
											<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
												<input type="checkbox" class="custom-control-input" id="INCLUDE_ALL_ENROLLMENTS" name="INCLUDE_ALL_ENROLLMENTS" value="1" >
												<label class="custom-control-label" for="INCLUDE_ALL_ENROLLMENTS"><?=INCLUDE_ALL_ENROLLMENTS?></label>
											</div>
										</div>
										<div class="col-md-1 "></div>
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									<hr style="border-top: 1px solid #ccc;" />
									
									<div class="row" style="padding-bottom:20px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="get_term_from_campus();clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												$count_campus = $res_type->RecordCount();
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
											
											<div class="validation-advice" id="Reg_PK_CAMPUS_1" style="display:none">This is a required field.</div>
										</div>
										
										<div class="col-md-2 ">
											<div id="PK_TERM_MASTER_DIV">
												<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
													
												</select>
											</div>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_GROUP']?>" ><?=$res_type->fields['STUDENT_GROUP']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-1 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										</div>
									</div>
									
									<div class="row" style="padding-bottom:20px;" >
										<div class="col-md-2 ">
											<select id="PK_COURSE" name="PK_COURSE[]" multiple class="form-control" onchange="get_course_offering(this.value);clear_search()" >
												<? /* Ticket # 1740  */
												$res_type = $db->Execute("select PK_COURSE, COURSE_CODE, TRANSCRIPT_CODE, COURSE_DESCRIPTION from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_COURSE']?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1740  */ ?>
											</select>
										</div>
										
										<div class="col-md-2 " id="PK_COURSE_OFFERING_DIV" >
											<select id="PK_COURSE_OFFERING" name="PK_COURSE_OFFERING" class="form-control" >
												<option value=""><?=COURSE_OFFERING_PAGE_TITLE?></option>
											</select>
										</div>
									</div>
									
									<br />
									<div id="student_div" >
										<? //$_REQUEST['ENROLLMENT'] = 1;
										/*$_REQUEST['show_check'] 	= 1;
										$_REQUEST['show_count'] 	= 1;*/
										//require_once('ajax_search_student_for_reports.php'); ?>
									</div>
									
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		clear_search()
		//search(); Ticket # 1216
	});
	</script>
	
	<script type="text/javascript">
		var form1 = new Validation('form1');
		function submit_form(val){
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		}
		function get_course_offering(val){
			jQuery(document).ready(function($) { 
				var data  = 'val='+$('#PK_COURSE').val()+'&multiple=0';
				var value = $.ajax({
					url: "ajax_get_course_offering",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_COURSE_OFFERING_DIV').innerHTML = data;
						document.getElementById('PK_COURSE_OFFERING').setAttribute('multiple', true);
						document.getElementById('PK_COURSE_OFFERING').name = "PK_COURSE_OFFERING[]"
						$("#PK_COURSE_OFFERING option[value='']").remove();
						
						document.getElementById('PK_COURSE_OFFERING').setAttribute("onchange", "clear_search()");
						
						$('#PK_COURSE_OFFERING').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
							nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
							numberDisplayed: 2,
							nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
						});
					}		
				}).responseText;
			});
		}
		function get_course_offering_session(){
		}
		
		function clear_search(){
			document.getElementById('student_div').innerHTML = ''
			show_btn()
		}
		
		function search(){
			jQuery(document).ready(function($) {
				if($('#PK_CAMPUS').val() == ''){
					document.getElementById('Reg_PK_CAMPUS_1').style.display = 'block';
				} else {
					document.getElementById('Reg_PK_CAMPUS_1').style.display = 'none';
					
					//var no_admin_check 	= 0;
					var no_admin_check 	= 0;
					var ENROLLMENT 		= 2;
					if(document.getElementById('INCLUDE_ALL_ENROLLMENTS').checked == true){
						no_admin_check 	= 0;
						ENROLLMENT		= 1;
					}
					
					var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&show_check=1&show_count=1&ENROLLMENT='+ENROLLMENT+'&no_admin_check='+no_admin_check+'&PK_CAMPUS='+$('#PK_CAMPUS').val();
					try {
						if($('#REPORT_OPTION').val() == 2){
							data = data+'&START_DATE='+$('#START_DATE').val()+'&END_DATE='+$('#END_DATE').val(); // DIAM-1017
						}
					} catch (error) {
						
					}
					
					 //Ticket # 1215
					//var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_COURSE='+$('#PK_COURSE').val()+'&PK_COURSE_OFFERING='+$('#PK_COURSE_OFFERING').val()+'&ENROLLMENT='+$('#ENROLLMENT').val()+'&show_check=1&show_count=1'; //Ticket # 1215
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							
							show_btn()
						}		
					}).responseText;
				}
			});
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				PK_STUDENT_ENROLLMENT[i].checked = str
			}
			get_count()
		}
		
		function show_btn(){
			
			var flag = 0;
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('btn_1').style.display = 'inline';
				document.getElementById('btn_2').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
			}
		}
		
		function get_count(){
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		function get_term_from_campus(){
			jQuery(document).ready(function($) {
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val();
				var value = $.ajax({
					url: "ajax_get_term_from_campus",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	

						document.getElementById('PK_TERM_MASTER_DIV').innerHTML 	= data;
						//document.getElementById('PK_TERM_MASTER').className 		= 'required-entry';
						document.getElementById('PK_TERM_MASTER').name 				= "'PK_TERM_MASTER'[]"
						document.getElementById('PK_TERM_MASTER').setAttribute('multiple', true);
						document.getElementById('PK_TERM_MASTER').setAttribute("onchange", "get_course_offering()");
						
						$("#PK_TERM_MASTER option[value='']").remove();
						
						$('#PK_TERM_MASTER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=TERM?>',
							nonSelectedText: '<?=TERM?>',
							numberDisplayed: 2,
							nSelectedText: '<?=TERM?> selected'
						});
						
					}		
				}).responseText;
			});
		}
		
		function show_fields(val){
			if(val == 1) {
				document.getElementById('AS_OF_DATE_DIV').style.display = 'block';
				document.getElementById('START_DATE_DIV').style.display = 'none';
				document.getElementById('END_DATE_DIV').style.display 	= 'none';
				
				document.getElementById('AS_OF_DATE').className 		= 'form-control date required-entry';
				document.getElementById('START_DATE').className 		= 'form-control date';
				document.getElementById('END_DATE').className 			= 'form-control date';
			} else {
				document.getElementById('AS_OF_DATE_DIV').style.display = 'none';
				document.getElementById('START_DATE_DIV').style.display = 'block';
				document.getElementById('END_DATE_DIV').style.display 	= 'block';
				
				document.getElementById('AS_OF_DATE').className 		= 'form-control date';
				document.getElementById('START_DATE').className 		= 'form-control date required-entry';
				document.getElementById('END_DATE').className 			= 'form-control date required-entry';
			}
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_CODE?>',
			nonSelectedText: '<?=COURSE_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_CODE?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM?>',
			nonSelectedText: '<?=FIRST_TERM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM?> selected'
		});
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '<?=PROGRAM?>',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STATUS?>',
			nonSelectedText: '<?=STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STATUS?> selected'
		});
		$('#PK_COURSE_OFFERING').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COURSE_OFFERING_PAGE_TITLE?>',
			nonSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=COURSE_OFFERING_PAGE_TITLE?> selected'
		});
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS_CODE?>',
			nonSelectedText: '<?=CAMPUS_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS_CODE?> selected'
		});

		<? if($count_campus == 1){ ?>
			get_term_from_campus();clear_search();
		<? } ?>
	});
	</script>
</body>

</html>
