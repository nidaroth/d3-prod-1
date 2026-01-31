<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/transfer_credit.php");
require_once("../language/course_offering.php");
require_once("check_access.php");

if(check_access('REPORT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$temp = explode(",",$_POST['SELECTED_PK_STUDENT_MASTER']);
	$temp = array_unique($temp, SORT_NUMERIC);
	$stud_id = implode(",",$temp);

	$cond = '';
	$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	$cond .= " AND S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];
	
	$campus_name = "";
	$campus_cond = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];

		$res_campus->MoveNext();
	}
	
	$transfer_status = "";
	if(!empty($_POST['PK_CREDIT_TRANSFER_STATUS'])) {
		$PK_CREDIT_TRANSFER_STATUS 	= implode(",",$_POST['PK_CREDIT_TRANSFER_STATUS']);
		$res_sts = $db->Execute("select CREDIT_TRANSFER_STATUS from M_CREDIT_TRANSFER_STATUS  WHERE PK_CREDIT_TRANSFER_STATUS IN ($PK_CREDIT_TRANSFER_STATUS) order by CREDIT_TRANSFER_STATUS ASC");
		while (!$res_sts->EOF) {
			if($transfer_status != '')
				$transfer_status .= ', ';
			$transfer_status .= $res_sts->fields['CREDIT_TRANSFER_STATUS'];

			$res_sts->MoveNext();
		}
		
		$cond .= " AND S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS IN ($PK_CREDIT_TRANSFER_STATUS) ";
	} else 
		$transfer_status = "All Status";

	$query = "select S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) AS STU_NAME, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%Y-%m-%d' )) AS  BEGIN_DATE_1, STUDENT_ID, CAMPUS_CODE, SCHOOL_NAME, S_COURSE.COURSE_CODE, CREDIT_TRANSFER_STATUS, S_STUDENT_CREDIT_TRANSFER.UNITS, S_STUDENT_CREDIT_TRANSFER.FA_UNITS, S_STUDENT_CREDIT_TRANSFER.HOUR, S_STUDENT_CREDIT_TRANSFER.PREP, TC_NUMERIC_GRADE, S_GRADE.GRADE, S_STUDENT_CREDIT_TRANSFER.COURSE_CODE as PRIOR_COURSE_CODE, S_COURSE.COURSE_DESCRIPTION, PRIOR_COURSE_DESCRIPTION, PRIOR_HOUR, PRIOR_PREP, PRIOR_FA_UNITS, PRIOR_UNITS, PRIOR_GRADE, S_STUDENT_CREDIT_TRANSFER.NOTES, YEAR, TERM, TC_PRIOR_NUMERIC_GRADE        
	FROM 
	S_STUDENT_MASTER, S_STUDENT_ACADEMICS, S_STUDENT_CREDIT_TRANSFER 
	LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = S_STUDENT_CREDIT_TRANSFER.PK_EQUIVALENT_COURSE_MASTER 
	LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
	LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS
	, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	, M_STUDENT_STATUS 
	WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
	S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_ENROLLMENT AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.ARCHIVED = 0 AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
	M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	$cond  ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) ASC, CREDIT_TRANSFER_STATUS ASC, S_COURSE.COURSE_CODE ASC ";
	
	if($_POST['FORMAT'] == 1){
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
							<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
							<td width="30%" valign="top" >
								<table width="100%" >
									<tr>
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Transfer Credit</b></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
						</tr>
						<tr>
							<td colspan="3" width="100%" align="right" style="font-size:13px;" >Transfer Status(es): '.$transfer_status.'</td>
						</tr>
					</table>';
					
		$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
					
		$footer = '<table width="100%" >
						<tr>
							<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
							<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
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
			'format' => [210, 296],
			'orientation' => 'L'
		]);
		$mpdf->autoPageBreak = true;
		
		$mpdf->SetHTMLHeader($header);
		$mpdf->SetHTMLFooter($footer);
		
		$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="13%" style="border-bottom:1px solid #000;">
									<b><i>Student</i></b>
								</td>
								<td width="20%" style="border-bottom:1px solid #000;">
									<b><i>Enrollment</i></b>
								</td>
								
								<td width="18%" style="border-bottom:1px solid #000;">
									<b><i>School Name</i></b>
								</td>
								<td width="12%" style="border-bottom:1px solid #000;">
									<b><i>Equivalent Course</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Transfer Status</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Hours</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Prep Hours</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>FA Units</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Units</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Grade</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Numeric Grade</i></b>
								</td>
							</tr>
						</thead>';
						
		$res = $db->Execute($query);			
		while (!$res->EOF) { 
			$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
			
			$txt .= '<tr>
						<td >'.$res->fields['STU_NAME'].'</td>
						<td >'.$res->fields['BEGIN_DATE_1'].' - '.$res->fields['CODE'].' - '.$res->fields['STUDENT_STATUS'].' - '.$res->fields['CAMPUS_CODE'].'</td>
						<td >'.$res->fields['SCHOOL_NAME'].'</td>
						<td >'.$res->fields['COURSE_CODE'].'</td>
						<td >'.$res->fields['CREDIT_TRANSFER_STATUS'].'</td>
						<td >'.$res->fields['HOUR'].'</td>
						<td >'.$res->fields['PREP'].'</td>
						<td >'.$res->fields['FA_UNITS'].'</td>
						<td >'.$res->fields['UNITS'].'</td>
						<td >'.$res->fields['GRADE'].'</td>
						<td >'.$res->fields['TC_NUMERIC_GRADE'].'</td>
					</tr>';
			$res->MoveNext();
		}
		$txt .= '</table>';
	
		$mpdf->WriteHTML($txt);
		$mpdf->Output("Transfer Credit.pdf", 'D');
	} else {
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
		$outputFileName = $dir."Transfer Credit.xlsx";
		$outputFileName = str_replace(
		pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),$outputFileName );
		
		$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setIncludeCharts(TRUE);
		//$objPHPExcel   = $objReader->load('../../global/excel/Template/empty_template.xlsx');
		$objPHPExcel = new PHPExcel();
		$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		
		$line = 1;
		$index 	= -1;
		$heading[] = 'Student';
		$width[]   = 15;
		$heading[] = 'Student ID';
		$width[]   = 15;
		
		$heading[] = 'Campus';
		$width[]   = 15;
		$heading[] = 'First Term';
		$width[]   = 15;
		$heading[] = 'Program';
		$width[]   = 15;
		$heading[] = 'Status';
		$width[]   = 15;
		
		$heading[] = 'Transfer Status';
		$width[]   = 15;
		$heading[] = 'Equivalent Course';
		$width[]   = 15;
		$heading[] = 'Equivalent Course Description';
		$width[]   = 15;
		$heading[] = 'Hours';
		$width[]   = 15;
		$heading[] = 'Prep Hours';
		$width[]   = 15;
		$heading[] = 'FA Units';
		$width[]   = 15;
		$heading[] = 'Units';
		$width[]   = 15;
		$heading[] = 'Grade';
		$width[]   = 15;
		$heading[] = 'Numeric Grade';
		$width[]   = 15;
		
		$heading[] = 'Prior School Name';
		$width[]   = 15;
		$heading[] = 'Prior Course Code';
		$width[]   = 15;
		$heading[] = 'Prior Course Description';
		$width[]   = 15;
		$heading[] = 'Prior Hours';
		$width[]   = 15;
		$heading[] = 'Prior Prep Hours';
		$width[]   = 15;
		$heading[] = 'Prior FA Units';
		$width[]   = 15;
		$heading[] = 'Prior Units';
		$width[]   = 15;
		$heading[] = 'Prior Grade';
		$width[]   = 15;
		$heading[] = 'Prior Numeric Grade';
		$width[]   = 15;
		$heading[] = 'Prior Year';
		$width[]   = 15;
		$heading[] = 'Prior Term';
		$width[]   = 15;
		$heading[] = 'Notes';
		$width[]   = 15;

		$i = 0;
		foreach($heading as $title) {
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
			$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
						
			$i++;
		}

		$res = $db->Execute($query);
		while (!$res->EOF) { 
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STUDENT_STATUS']);
		
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CREDIT_TRANSFER_STATUS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COURSE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COURSE_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOUR']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PREP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FA_UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TC_NUMERIC_GRADE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SCHOOL_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_COURSE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_COURSE_DESCRIPTION']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_HOUR']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_PREP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_FA_UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_UNITS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRIOR_GRADE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TC_PRIOR_NUMERIC_GRADE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['YEAR']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TERM']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NOTES']);
		
			$res->MoveNext();
		}
		
		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
		exit;
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
	<title><?=MNU_TRANSFER_CREDIT ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_STUDENT_STATUS, #advice-required-entry-PK_CAMPUS{position: absolute;top: 57px;width: 140px}
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
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
                        <h4 class="text-themecolor">
							<?=MNU_TRANSFER_CREDIT ?>
						</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="" >
									<div class="row form-group"  >
										
										<div class="col-md-2 align-self-center"  >
											<br />
											<div class="custom-control custom-radio col-md-6">
												<input type="radio" id="LEAD" name="STUDENT_TYPE" value="1" class="custom-control-input" onclick="clear_search()" >
												<label class="custom-control-label" for="LEAD"><?=LEAD?></label>
											</div>
											<div class="custom-control custom-radio col-md-6 ">
												<input type="radio" id="STUDENT" name="STUDENT_TYPE" value="2" checked class="custom-control-input" onclick="clear_search()" >
												<label class="custom-control-label" for="STUDENT"><?=STUDENT?></label>
											</div>
										</div>
										
										<div class="col-md-2">
											<?=TRANSFER_STATUS ?>
											<select id="PK_CREDIT_TRANSFER_STATUS" name="PK_CREDIT_TRANSFER_STATUS[]" multiple class="form-control" onchange="clear_search()" > 
												<? $res_type = $db->Execute("SELECT PK_CREDIT_TRANSFER_STATUS, CONCAT(CREDIT_TRANSFER_STATUS, ' - ', DESCRIPTION) as CREDIT_TRANSFER_STATUS, ACTIVE FROM M_CREDIT_TRANSFER_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY ACTIVE DESC, CREDIT_TRANSFER_STATUS ASC ");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CREDIT_TRANSFER_STATUS'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$res_type->fields['PK_CREDIT_TRANSFER_STATUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												}  ?>
											</select>
										</div>
										
										<div class="col-md-2 align-self-center  ">
											<button type="button" onclick="submit_form(1)" id="btn_1" style="display:none;" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" style="display:none;" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<div class="row form-group"  >
										<div class="col-md-2">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_TERM_MASTER" name="PK_TERM_MASTER[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS_PROGRAM']?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS, DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_STUDENT_STATUS']?>" ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<select id="PK_STUDENT_GROUP" name="PK_STUDENT_GROUP[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by STUDENT_GROUP ASC");
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
								
									<br />
									<div id="student_div" >
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
	<script type="text/javascript">

		function clear_search(){
			document.getElementById('student_div').innerHTML = '';
			show_btn()
		}
		
		function search(){
			jQuery(document).ready(function($) {
				var LEAD = 1
				if(document.getElementById('LEAD').checked == true)
					LEAD = 1
				else
					LEAD = 0
					
				var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_CREDIT_TRANSFER_STATUS='+$('#PK_CREDIT_TRANSFER_STATUS').val()+'&show_check=1&show_count=1&ENROLLMENT=2&LEAD='+LEAD;
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
				document.getElementById('btn_1').style.display = 'none';
			}
		}
		
		function get_count(){
			var PK_STUDENT_MASTER_sel = '';
			var tot = 0
			var PK_STUDENT_ENROLLMENT = document.getElementsByName('PK_STUDENT_ENROLLMENT[]')
			for(var i = 0 ; i < PK_STUDENT_ENROLLMENT.length ; i++){
				if(PK_STUDENT_ENROLLMENT[i].checked == true) {
					if(PK_STUDENT_MASTER_sel != '')
						PK_STUDENT_MASTER_sel += ',';
						
					PK_STUDENT_MASTER_sel += document.getElementById('S_PK_STUDENT_MASTER_'+PK_STUDENT_ENROLLMENT[i].value).value
					tot++;
				}
			}
			document.getElementById('SELECTED_PK_STUDENT_MASTER').value = PK_STUDENT_MASTER_sel
			//alert(PK_STUDENT_MASTER_sel)
			
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
	</script>
	
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

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

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

		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_CREDIT_TRANSFER_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=TRANSFER_STATUS?>',
			nonSelectedText: '<?=TRANSFER_STATUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=TRANSFER_STATUS?> selected'
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
</body>

</html>