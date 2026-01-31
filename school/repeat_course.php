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
	
	$PK_STUDENT_ENROLLMENT 	= implode(",",$_POST['PK_STUDENT_ENROLLMENT']);
	$cond = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT IN ($PK_STUDENT_ENROLLMENT) ";

	$stud_query = "SELECT * FROM (
		select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, S_COURSE_OFFERING.PK_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_MASTER.FIRST_NAME, S_STUDENT_MASTER.LAST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1, S_TERM_MASTER.BEGIN_DATE, M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		, S_STUDENT_COURSE, S_COURSE_OFFERING 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_COURSE.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_COURSE_OFFERING.PK_COURSE_OFFERING = S_STUDENT_COURSE.PK_COURSE_OFFERING $cond 
		UNION ALL 
		select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, PK_EQUIVALENT_COURSE_MASTER as PK_COURSE, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_MASTER.FIRST_NAME, S_STUDENT_MASTER.LAST_NAME, CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) AS STU_NAME, STUDENT_ID, CAMPUS_CODE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE,  M_CAMPUS_PROGRAM.CODE, STUDENT_STATUS 
		FROM 
		S_STUDENT_MASTER, S_STUDENT_ACADEMICS , S_STUDENT_ENROLLMENT 
		LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		, S_STUDENT_CREDIT_TRANSFER 
		WHERE 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER AND 
		S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER $cond 
	) as TEMP 
	
	GROUP BY PK_STUDENT_ENROLLMENT, PK_COURSE HAVING COUNT(PK_COURSE) > 1 
	ORDER BY STU_NAME ASC, STUDENT_ID ASC, BEGIN_DATE ASC ";
		
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
										<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Repeat Courses</b></td>
									</tr>
									<tr>
										<td width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
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
							<td width="7%" style="border-bottom:1px solid #000;">
								<b><i>Term</i></b>
							</td>
							<td width="13%" style="border-bottom:1px solid #000;">
								<b><i>Course Offering</i></b>
							</td>
							<td width="13%" style="border-bottom:1px solid #000;">
								<b><i>Instructor</i></b>
							</td>
							<td width="10%" style="border-bottom:1px solid #000;">
								<b><i>Course Offering<br />Student Status</i></b>
							</td>
							<td width="5%" style="border-bottom:1px solid #000;">
								<b><i>Final<br />Grade</i></b>
							</td>
						</tr>
					</thead>';
					
					$res_stud = $db->Execute($stud_query);
					while (!$res_stud->EOF) { 
						$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
						$PK_STUDENT_MASTER		= $res_stud->fields['PK_STUDENT_MASTER'];
						$PK_COURSE			   	= $res_stud->fields['PK_COURSE'];
						
						$res_co = $db->Execute("SELECT * FROM 
							(select S_COURSE.COURSE_CODE as CO, CREDIT_TRANSFER_STATUS as STUD_STATS, 'Transfer' as BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, '' as INSTRUCTOR, S_GRADE.GRADE 
							FROM 
							S_STUDENT_CREDIT_TRANSFER 
							LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
							LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
							LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = PK_EQUIVALENT_COURSE_MASTER 
							WHERE 
							PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_EQUIVALENT_COURSE_MASTER = '$PK_COURSE' 
							UNION 
							SELECT CONCAT(COURSE_CODE, ' (', SUBSTR(SESSION, 1, 1),'-',SESSION_NO,')') as CO, COURSE_OFFERING_STUDENT_STATUS as STUD_STATS, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1, S_TERM_MASTER.BEGIN_DATE, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR, GRADE 
							FROM 
							S_STUDENT_COURSE 
							LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
							LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS = M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS 
							LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
							, S_COURSE_OFFERING
							LEFT JOIN M_SESSION ON S_COURSE_OFFERING.PK_SESSION = M_SESSION.PK_SESSION 
							LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
							, S_COURSE 
							WHERE 
							S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
							S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
							S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
							S_COURSE.PK_COURSE = '$PK_COURSE' 
						) as TEMP
						ORDER BY CO ASC, BEGIN_DATE ASC");
						
						while (!$res_co->EOF) { 
							$txt .= '<tr>
										<td >'.$res_stud->fields['STU_NAME'].'</td>
										<td >'.$res_stud->fields['STUDENT_ID'].'</td>
										<td >'.$res_stud->fields['BEGIN_DATE_1'].' - '.$res_stud->fields['CODE'].' - '.$res_stud->fields['STUDENT_STATUS'].' - '.$res_stud->fields['CAMPUS_CODE'].'</td>
										<td >'.$res_co->fields['BEGIN_DATE_1'].'</td>
										<td >'.$res_co->fields['CO'].'</td>
										<td >'.$res_co->fields['INSTRUCTOR'].'</td>
										<td >'.$res_co->fields['STUD_STATS'].'</td>
										<td >'.$res_co->fields['GRADE'].'</td>
									</tr>';
							$res_co->MoveNext();
						}

						$res_stud->MoveNext();
					}
				
				$txt .= '</table>';
				
			//echo $txt;exit;
		$file_name = 'Repeat Course.pdf';
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
		$file_name 		= 'Repeat Course.xlsx';
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

		$heading[] = 'Last Name';
		$width[]   = 20;
		$heading[] = 'First Name';
		$width[]   = 20;
		$heading[] = 'Student ID';
		$width[]   = 20;
		$heading[] = 'Campus';
		$width[]   = 20;
		$heading[] = 'First Term';
		$width[]   = 20;
		$heading[] = 'Program';
		$width[]   = 20;
		$heading[] = 'Status';
		$width[]   = 20;
		$heading[] = 'Term';
		$width[]   = 20;
		$heading[] = 'Course Offering';
		$width[]   = 20;
		$heading[] = 'Instructor';
		$width[]   = 20;
		$heading[] = 'Course Offering Student Status';
		$width[]   = 20;
		$heading[] = 'Final Grade';
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
		
		$res_stud = $db->Execute($stud_query);
		while (!$res_stud->EOF) {
			$PK_STUDENT_ENROLLMENT 	= $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$PK_STUDENT_MASTER		= $res_stud->fields['PK_STUDENT_MASTER'];
			$PK_COURSE			   	= $res_stud->fields['PK_COURSE'];
			
			$res_co = $db->Execute("SELECT * FROM 
				(select S_COURSE.COURSE_CODE as CO, CREDIT_TRANSFER_STATUS as STUD_STATS, 'Transfer' as BEGIN_DATE_1, '0000-00-00' as BEGIN_DATE, '' as INSTRUCTOR, S_GRADE.GRADE 
				FROM 
				S_STUDENT_CREDIT_TRANSFER 
				LEFT JOIN M_CREDIT_TRANSFER_STATUS ON M_CREDIT_TRANSFER_STATUS.PK_CREDIT_TRANSFER_STATUS = S_STUDENT_CREDIT_TRANSFER.PK_CREDIT_TRANSFER_STATUS 
				LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_CREDIT_TRANSFER.PK_GRADE 
				LEFT JOIN S_COURSE ON S_COURSE.PK_COURSE = PK_EQUIVALENT_COURSE_MASTER 
				WHERE 
				PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_EQUIVALENT_COURSE_MASTER = '$PK_COURSE' 
				UNION 
				SELECT CONCAT(COURSE_CODE, ' (', SUBSTR(SESSION, 1, 1),'-',SESSION_NO,')') as CO, COURSE_OFFERING_STUDENT_STATUS as STUD_STATS, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','', DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%Y-%m-%d' )) AS BEGIN_DATE_1, S_TERM_MASTER.BEGIN_DATE, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ',S_EMPLOYEE_MASTER.FIRST_NAME) as INSTRUCTOR, GRADE 
				FROM 
				S_STUDENT_COURSE 
				LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_COURSE.PK_TERM_MASTER 
				LEFT JOIN M_COURSE_OFFERING_STUDENT_STATUS ON S_STUDENT_COURSE.PK_COURSE_OFFERING_STUDENT_STATUS = M_COURSE_OFFERING_STUDENT_STATUS.PK_COURSE_OFFERING_STUDENT_STATUS 
				LEFT JOIN S_GRADE ON S_GRADE.PK_GRADE = S_STUDENT_COURSE.FINAL_GRADE 
				, S_COURSE_OFFERING
				LEFT JOIN M_SESSION ON S_COURSE_OFFERING.PK_SESSION = M_SESSION.PK_SESSION 
				LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR
				, S_COURSE 
				WHERE 
				S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND 
				S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND 
				S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND 
				S_COURSE.PK_COURSE = '$PK_COURSE' 
			) as TEMP
			ORDER BY CO ASC, BEGIN_DATE ASC");
			
			while (!$res_co->EOF) { 
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['LAST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['FIRST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['BEGIN_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['CAMPUS_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_stud->fields['STUDENT_STATUS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['BEGIN_DATE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['CO']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['INSTRUCTOR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['STUD_STATS']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_co->fields['GRADE']);

				$res_co->MoveNext();
			}

			$res_stud->MoveNext();
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
	<title><?=MNU_REPEAT_COURSE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_REPEAT_COURSE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels" method="post" name="form1" id="form1" >
									
									<div class="row" style="padding-bottom:20px;" >
										<div class="col-md-2 ">
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="get_term_from_campus();clear_search()" >
												<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
												while (!$res_type->EOF) { ?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->RecordCount() == 1) echo "selected";?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
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
										
										<div class="col-md-2 align-self-center ">
											<button type="button" onclick="search()" class="btn waves-effect waves-light btn-info"><?=SEARCH?></button>
										
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
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
					
					var data  = 'PK_STUDENT_GROUP='+$('#PK_STUDENT_GROUP').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_CAMPUS_PROGRAM='+$('#PK_CAMPUS_PROGRAM').val()+'&PK_STUDENT_STATUS='+$('#PK_STUDENT_STATUS').val()+'&show_check=1&show_count=1&ENROLLMENT='+ENROLLMENT+'&no_admin_check='+no_admin_check+'&PK_CAMPUS='+$('#PK_CAMPUS').val(); 
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
						//document.getElementById('PK_TERM_MASTER').setAttribute("onchange", "get_course_offering()");
						
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

	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS_CODE?>',
			nonSelectedText: '<?=CAMPUS_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS_CODE?> selected'
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
		
		$('#PK_STUDENT_GROUP').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=GROUP_CODE?>',
			nonSelectedText: '<?=GROUP_CODE?>',
			numberDisplayed: 2,
			nSelectedText: '<?=GROUP_CODE?> selected'
		});
		
	});
	</script>
</body>

</html>