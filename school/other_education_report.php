<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
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

	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER IN ($stud_id) ";
	
	if(empty($_POST['PK_EDUCATION_TYPE'])){
		$EDUCATION_TYPE = "All";
	} else {
		$PK_EDUCATION_TYPE = implode(",",$_POST['PK_EDUCATION_TYPE']);

		$res_edu = $db->Execute("select EDUCATION_TYPE from M_EDUCATION_TYPE WHERE PK_EDUCATION_TYPE IN ($PK_EDUCATION_TYPE) order by EDUCATION_TYPE ASC");
		while (!$res_edu->EOF) {
			if($EDUCATION_TYPE != '')
				$EDUCATION_TYPE .= ', ';
			$EDUCATION_TYPE .= $res_edu->fields['EDUCATION_TYPE'];
			
			$res_edu->MoveNext();
		}
		
		$cond .= " AND S_STUDENT_OTHER_EDU.PK_EDUCATION_TYPE IN ($PK_EDUCATION_TYPE) ";
	}
	
	$stud_query = "select PK_STUDENT_OTHER_EDU, S_STUDENT_MASTER.PK_STUDENT_MASTER, SSN, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ', SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, EDUCATION_TYPE, SCHOOL_NAME, S_STUDENT_OTHER_EDU.ADDRESS, S_STUDENT_OTHER_EDU.ADDRESS_1, S_STUDENT_OTHER_EDU.CITY, STATE_CODE, S_STUDENT_OTHER_EDU.ZIP, OTHER_SCHOOL_PHONE, OTHER_SCHOOL_FAX, IF(GRADUATED = 1, 'Yes', 'No') as GRADUATED, IF(GRADUATED_DATE != '0000-00-00', DATE_FORMAT(GRADUATED_DATE, '%Y-%m-%d') , '' ) as GRADUATED_DATE, IF(TRANSCRIPT_REQUESTED = 1, 'Yes', 'No') as TRANSCRIPT_REQUESTED, IF(TRANSCRIPT_REQUESTED_DATE != '0000-00-00', DATE_FORMAT(TRANSCRIPT_REQUESTED_DATE, '%Y-%m-%d') , '' ) as TRANSCRIPT_REQUESTED_DATE, IF(TRANSCRIPT_RECEIVED = 1, 'Yes', 'No') as TRANSCRIPT_RECEIVED, IF(TRANSCRIPT_RECEIVED_DATE != '0000-00-00', DATE_FORMAT(TRANSCRIPT_RECEIVED_DATE, '%Y-%m-%d') , '' ) as TRANSCRIPT_RECEIVED_DATE, OTHER_SCHOOL_COMMENTS 
	FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_OTHER_EDU ON S_STUDENT_OTHER_EDU.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN M_EDUCATION_TYPE ON M_EDUCATION_TYPE.PK_EDUCATION_TYPE = S_STUDENT_OTHER_EDU.PK_EDUCATION_TYPE 
	LEFT JOIN Z_STATES ON S_STUDENT_OTHER_EDU.PK_STATE = Z_STATES.PK_STATES 
	, S_STUDENT_ACADEMICS, S_STUDENT_ENROLLMENT 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
	WHERE 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND IS_ACTIVE_ENROLLMENT = 1 AND 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
	S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $cond $campus_cond1 
	GROUP BY PK_STUDENT_OTHER_EDU 
	ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC, EDUCATION_TYPE ASC, SCHOOL_NAME ASC  ";

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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Other Education</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Education Types: '.$EDUCATION_TYPE.'</td>
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
							<td width="20%" style="border-bottom:1px solid #000;">
								<b><i>Student</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Education Types</i></b>
							</td>
							<td width="22%" style="border-bottom:1px solid #000;">
								<b><i>School Name</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Passed / Graduated</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Passed / Graduated Date</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Transcript Requested</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Transcript Requested Date</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Transcript Received</i></b>
							</td>
							<td width="8%" style="border-bottom:1px solid #000;">
								<b><i>Transcript Received Date</i></b>
							</td>
						</tr>
					</thead>';
					
					$res_stud = $db->Execute($stud_query);
					while (!$res_stud->EOF) { 
						$txt .= '<tr>
									<td >'.$res_stud->fields['STU_NAME'].'</td>
									<td >'.$res_stud->fields['EDUCATION_TYPE'].'</td>
									<td >'.$res_stud->fields['SCHOOL_NAME'].'</td>
									<td >'.$res_stud->fields['GRADUATED'].'</td>
									<td >'.$res_stud->fields['GRADUATED_DATE'].'</td>
									<td >'.$res_stud->fields['TRANSCRIPT_REQUESTED'].'</td>
									<td >'.$res_stud->fields['TRANSCRIPT_REQUESTED_DATE'].'</td>
									<td >'.$res_stud->fields['TRANSCRIPT_RECEIVED'].'</td>
									<td >'.$res_stud->fields['TRANSCRIPT_RECEIVED_DATE'].'</td>
								</tr>';
						$res_stud->MoveNext();
					}
				
				$txt .= '</table>';
				
			//echo $txt;exit;
		$file_name = 'Other Education.pdf';
		$mpdf->WriteHTML($txt);
		$mpdf->Output($file_name, 'D');
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
		$file_name 		= 'Other Education.xlsx';
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
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'Education Type';
		$width[]   = 20;
		$heading[] = 'School Name';
		$width[]   = 20;
		$heading[] = 'Address';
		$width[]   = 20;
		$heading[] = 'Address 2nd Line';
		$width[]   = 20;
		$heading[] = 'City';
		$width[]   = 20;
		$heading[] = 'State';
		$width[]   = 20;
		$heading[] = 'Zip';
		$width[]   = 20;
		$heading[] = 'School Phone';
		$width[]   = 20;
		$heading[] = 'School Fax';
		$width[]   = 20;
		$heading[] = 'Passed/Graduated';
		$width[]   = 20;
		$heading[] = 'Passed/Graduated Date';
		$width[]   = 20;
		$heading[] = 'Transcript Requested';
		$width[]   = 20;
		$heading[] = 'Transcript Requested Date';
		$heading[] = 'Transcript Received';
		$width[]   = 20;
		$heading[] = 'Transcript Received Date';
		$width[]   = 20;
		$heading[] = 'Comments';
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
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EDUCATION_TYPE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SCHOOL_NAME']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ADDRESS']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ADDRESS_1']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CITY']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STATE_CODE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ZIP']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OTHER_SCHOOL_PHONE']);
		
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OTHER_SCHOOL_FAX']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADUATED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GRADUATED_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSCRIPT_REQUESTED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSCRIPT_REQUESTED_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSCRIPT_RECEIVED']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TRANSCRIPT_RECEIVED_DATE']);
			
			$index++;
			$cell_no = $cell[$index].$line;
			$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['OTHER_SCHOOL_COMMENTS']);
			
			$res->MoveNext();
		}

		$objWriter->save($outputFileName);
		$objPHPExcel->disconnectWorksheets();
		header("location:".$outputFileName);
	}
} 
$res_camp_count = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");	 ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_OTHER_EDUCATION ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_OTHER_EDUCATION ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row" >
										<div class="col-md-2 align-self-center"  >
											<div class="custom-control custom-radio col-md-6">
												<input type="radio" id="LEAD" name="STUDENT_TYPE" value="1" class="custom-control-input" onclick="clear_search()" >
												<label class="custom-control-label" for="LEAD"><?=LEAD?></label>
											</div>
											<div class="custom-control custom-radio col-md-6 ">
												<input type="radio" id="STUDENT" name="STUDENT_TYPE" value="2" checked class="custom-control-input" onclick="clear_search()" >
												<label class="custom-control-label" for="STUDENT"><?=STUDENT?></label>
											</div>
										</div>
										
										<div class="col-md-2 " id="AS_OF_DATE_DIV" >	
											<select id="PK_EDUCATION_TYPE" name="PK_EDUCATION_TYPE[]" multiple class="form-control" >
												<? $res_type = $db->Execute("select EDUCATION_TYPE,PK_EDUCATION_TYPE from M_EDUCATION_TYPE WHERE 1=1 order by EDUCATION_TYPE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_EDUCATION_TYPE']?>" ><?=$res_type->fields['EDUCATION_TYPE']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-1 "></div>
										<div class="col-md-2 ">
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
											<input type="hidden" name="SELECTED_PK_STUDENT_MASTER" id="SELECTED_PK_STUDENT_MASTER" value="" >
										</div>
									</div>
									<hr style="border-top: 1px solid #ccc;" />
									
									<div class="row" style="padding-bottom:20px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="get_term_from_campus();clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
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
		
		<? if($res_camp_count->RecordCount() == 1){ ?>
			get_term_from_campus();
		<? } ?>
		
		clear_search()
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
					
					var LEAD = 1
					if(document.getElementById('LEAD').checked == true)
						LEAD = 1
					else
						LEAD = 0
					
					var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&group_by=1&ENROLLMENT=1&show_check=1&show_count=1&LEAD='+LEAD;
					var value = $.ajax({
						url: "ajax_search_student_for_reports",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							document.getElementById('student_div').innerHTML = data
							document.getElementById('SELECTED_PK_STUDENT_MASTER').value = '';
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
						document.getElementById('PK_TERM_MASTER').name 				= "'PK_TERM_MASTER'[]"
						document.getElementById('PK_TERM_MASTER').setAttribute('multiple', true);
						document.getElementById('PK_TERM_MASTER').setAttribute("onchange", "get_course_offering()");
						
						$("#PK_TERM_MASTER option[value='']").remove();
						
						$('#PK_TERM_MASTER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=FIRST_TERM_DATE_1?>',
							nonSelectedText: '<?=FIRST_TERM_DATE_1?>',
							numberDisplayed: 2,
							nSelectedText: '<?=FIRST_TERM_DATE_1?> selected'
						});
						
					}		
				}).responseText;
			});
		}
		
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_EDUCATION_TYPE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EDUCATION_TYPE?>',
			nonSelectedText: '<?=EDUCATION_TYPE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=EDUCATION_TYPE?> selected'
		});
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_GROUP?>',
			nonSelectedText: '<?=STUDENT_GROUP?>',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_GROUP?> selected'
		});
		$('#PK_TERM_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=FIRST_TERM_DATE_1?>',
			nonSelectedText: '<?=FIRST_TERM_DATE_1?>',
			numberDisplayed: 2,
			nSelectedText: '<?=FIRST_TERM_DATE_1?> selected'
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
			allSelectedText: 'All <?=CAMPUS_CODE?>',
			nonSelectedText: '<?=CAMPUS_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS_CODE?> selected'
		});
	});
	</script>
</body>

</html>