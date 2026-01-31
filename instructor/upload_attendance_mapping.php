<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance_entry.php");
require_once("../school/function_attendance.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){ 
	header("location:../index");
	exit;
}

include '../global/excel/Classes/PHPExcel/IOFactory.php'; 

$msg 	= '';
$error 	= array();
$flag 	= 0;
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$i 		= 0;
	$flag 	= 1;
	foreach($_POST['FIELDS'] as $FIELDS ){
		$EXCEL_COLUMN = $_POST['EXCEL_COLUMN'][$i];
		if($FIELDS != '') {
			$MAP_DETAIL['TABLE_COLUMN'] = $FIELDS;
			db_perform('Z_EXCEL_MAP_DETAIL', $MAP_DETAIL, 'update'," PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		} else {
			$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND EXCEL_COLUMN = '$EXCEL_COLUMN' ");
		}		
		$i++;
	}
	
	$db->Execute("DELETE FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = '' ");
		
	$res = $db->Execute("SELECT FILE_LOCATION,HEADING_ROW_NO FROM Z_EXCEL_MAP_MASTER WHERE PK_MAP_MASTER = '$_GET[id]' ");
	$newfile1 		= $res->fields['FILE_LOCATION'];
	$HEADING_ROW_NO = $res->fields['HEADING_ROW_NO'];

	if ($newfile1 != ""){
		$extn = explode(".",$newfile1);
		$ii = count($extn) - 1;

		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){
			$inputFileName = $newfile1;
			
			if(strtolower($extn[$ii]) == 'csv'){
				$inputFileType = 'CSV';
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($inputFileName);
				$objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			}else{
				//echo $inputFileName.'--';exit;	
				$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			}
			$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		}
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'COURSE_CODE' ");
		$COURSE_CODE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION' ");
		$SESSION_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION_NO' ");
		$SESSION_NO_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SCHEDULED_CLASS_DATE' ");
		$SCHEDULED_CLASS_DATE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CLASS_START_TIME' ");
		$CLASS_START_TIME_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'CLASS_END_TIME' ");
		$CLASS_END_TIME_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STUDENT_ID' ");
		$STUDENT_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STUDENTS' ");
		$STUDENTS_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ATTENDANCE_HOURS' ");
		$ATTENDANCE_HOURS_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ATTENDANCE_CODE' ");
		$ATTENDANCE_CODE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TERM' ");
		$TERM_COL = $res->fields['EXCEL_COLUMN'];
		$i = 0;
		foreach($sheetData as $row ){
			$i++;
			if($i <= $HEADING_ROW_NO){
				continue;
			}
			
			$error_str = "";
			
			$COURSE_CODE 			= trim($row[$COURSE_CODE_COL]);
			$SESSION 				= trim($row[$SESSION_COL]);
			$SESSION_NO 			= trim($row[$SESSION_NO_COL]);
			$SCHEDULED_CLASS_DATE 	= trim($row[$SCHEDULED_CLASS_DATE_COL]);
			$CLASS_START_TIME 		= trim($row[$CLASS_START_TIME_COL]);
			$CLASS_END_TIME 		= trim($row[$CLASS_END_TIME_COL]);
			$STUDENT_ID 			= trim($row[$STUDENT_ID_COL]);
			$STUDENTS 				= trim($row[$STUDENTS_COL]);
			$ATTENDANCE_HOURS 		= trim($row[$ATTENDANCE_HOURS_COL]);
			$ATTENDANCE_CODE 		= trim($row[$ATTENDANCE_CODE_COL]);
			$TERM 					= trim($row[$TERM_COL]);
			
			$PK_COURSE				= '';
			$PK_SESSION				= '';
			$PK_COURSE_OFFERING 	= '';
			$PK_STUDENT_SCHEDULE	= '';
			$PK_STUDENT_MASTER		= '';
			$PK_ATTENDANCE_CODE		= '';
			$PK_STUDENT_ENROLLMENT	= '';
			$PK_TERM_MASTER			= '';
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL = '';
			
			if($COURSE_CODE != '') {
				$res = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(COURSE_CODE) = '$COURSE_CODE' ");
				if($res->RecordCount() == 0)
					$error_str .= 'Invalid '.COURSE_CODE.' <b>'.$COURSE_CODE.'</b>';
				else
					$PK_COURSE = $res->fields['PK_COURSE'];
			} else
				$error_str .= ' <b>'.COURSE_CODE.' Empty</b>';
				
			if($TERM != '') {
				$PK_TERM_MASTER = str_replace("/","-",$TERM);
				$PK_TERM_MASTER = explode("-",$PK_TERM_MASTER);
				if($PK_TERM_MASTER[2] < 2000)
					$year = 2000 + $PK_TERM_MASTER[2];
				else
					$year = $PK_TERM_MASTER[2];
				
				$PK_TERM_MASTER = $year.'/'.$PK_TERM_MASTER[0].'/'.$PK_TERM_MASTER[1];
				
				$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BEGIN_DATE = '$PK_TERM_MASTER' ");
					
				if($res_l->RecordCount() == 0) {
					$error_str .= 'Invalid '.TERM.' <b>'.$TERM.'</b>';
				} else {
					$PK_TERM_MASTER = $res_l->fields['PK_TERM_MASTER'];
				}
			}
			if($SESSION != '') {
				$res = $db->Execute("select PK_SESSION from M_SESSION WHERE ACTIVE = 1 AND TRIM(SESSION) = '$SESSION' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				if($res->RecordCount() == 0)
					$error_str .= 'Invalid '.SESSION.' <b>'.$SESSION.'</b>';
				else
					$PK_SESSION = $res->fields['PK_SESSION'];
			} else
				$error_str .= ' <b>'.SESSION.' Empty</b>';
				
			if($SESSION_NO == '') {
				$error_str .= ' <b>'.SESSION_NO.' Empty</b>';
			}
				
			if($PK_COURSE != '' && $SESSION_NO != '' && $PK_SESSION > 0) {
				$res = $db->Execute("select S_COURSE_OFFERING.PK_COURSE_OFFERING from S_COURSE_OFFERING LEFT JOIN S_COURSE_OFFERING_ASSISTANT ON S_COURSE_OFFERING_ASSISTANT.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' AND SESSION_NO = '$SESSION_NO' AND PK_SESSION = '$PK_SESSION' AND PK_TERM_MASTER = '$PK_TERM_MASTER' AND (INSTRUCTOR = '$_SESSION[PK_EMPLOYEE_MASTER]' OR S_COURSE_OFFERING_ASSISTANT.ASSISTANT = '$_SESSION[PK_EMPLOYEE_MASTER]')");
				if($res->RecordCount() == 0)
					$error_str .= COURSE_OFFERING.' Not Found ';
				else
					$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
			}
			
			if($PK_COURSE_OFFERING > 0) {
				$flag_1 = 1;
				if($SCHEDULED_CLASS_DATE == ''){
					$error_str .= ' <b>'.SCHEDULED_CLASS_DATE.' Empty</b>';
					$flag_1 = 0;
				} 
				if($CLASS_START_TIME == ''){
					$error_str .= ' <b>'.CLASS_START_TIME.' Empty</b>';
					$flag_1 = 0;
				} 
				if($CLASS_END_TIME == ''){
					$error_str .= ' <b>'.CLASS_END_TIME.' Empty</b>';
					$flag_1 = 0;
				} 
				
				if($flag_1 == 1) {
					$res = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND (DATE_FORMAT(SCHEDULE_DATE,'%m/%d/%Y') = '$SCHEDULED_CLASS_DATE' OR DATE_FORMAT(SCHEDULE_DATE,'%c/%e/%Y') = '$SCHEDULED_CLASS_DATE') AND (DATE_FORMAT(START_TIME,'%h:%i %p') = '$CLASS_START_TIME' OR DATE_FORMAT(START_TIME,'%l:%i %p') = '$CLASS_START_TIME') AND (DATE_FORMAT(END_TIME,'%h:%i %p') = '$CLASS_END_TIME' OR DATE_FORMAT(END_TIME,'%l:%i %p') = '$CLASS_END_TIME')");
					if($res->RecordCount() == 0)
						$error_str .= SCHEDULED_CLASS_MEETING.' Not Found ';
					else
						$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
				}
			}
			
			if($STUDENT_ID == '') {
				$error_str .= ' <b>'.STUDENT_ID.' Empty</b>';
			} else {
				//AND CONCAT(LAST_NAME,', ',FIRST_NAME) = '$STUDENTS'
				$res = $db->Execute("select PK_STUDENT_SCHEDULE, S_STUDENT_MASTER.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND TRIM(STUDENT_ID) = '$STUDENT_ID' ");
				if($res->RecordCount() == 0)
					$error_str .= STUDENTS.' Not Found ';
				else {
					$PK_STUDENT_SCHEDULE 	= $res->fields['PK_STUDENT_SCHEDULE'];
					$PK_STUDENT_MASTER 	 	= $res->fields['PK_STUDENT_MASTER'];
					$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
				}
			}
			
			if($ATTENDANCE_HOURS == '') {
				$error_str .= ' <b>'.ATTENDANCE_HOURS.' Empty</b>';
			}
			
			if($ATTENDANCE_CODE == '') {
				$error_str .= ' <b>'.ATTENDANCE_CODE.' Empty</b>';
			} else {
				$res = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE ACTIVE = 1 AND CODE = '$ATTENDANCE_CODE' ");
				if($res->RecordCount() == 0)
					$error_str .= 'Invalid '.ATTENDANCE_CODE.' <b>'.$ATTENDANCE_CODE.'</b>';
				else {
					$PK_ATTENDANCE_CODE = $res->fields['PK_ATTENDANCE_CODE'];
				}
			}
				
			if($error_str != '')
				$error[] = 'Row #'.$i.' - '.$error_str;
			else {
				$COMPLETE = 0;
				$res = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' ");
				if($res->RecordCount() == 0) {
					$PK_STUDENT_ATTENDANCE = '';
				} else {
					$PK_STUDENT_ATTENDANCE = $res->fields['PK_STUDENT_ATTENDANCE'];
				}
				
				attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,$COMPLETE,$PK_STUDENT_ATTENDANCE,$PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT,$PK_STUDENT_SCHEDULE,$ATTENDANCE_HOURS,$PK_ATTENDANCE_CODE,$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);
			}
		}
		
		/*if(empty($error)){
			header("location:manage_ar_leder_code.php");
			exit;
		}*/
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
	<title><?=ATTENDANCE_ENTRY_PAGE_TITLE.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=ATTENDANCE_ENTRY_PAGE_TITLE.' '.MAPPING?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="COURSE_CODE" >
														<select id="COURSE_CODE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",COURSE_CODE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="COURSE_CODE"><?=COURSE_CODE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="TERM" >
														<select id="SESSION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TERM))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="TERM"><?=TERM?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="SESSION" >
														<select id="SESSION" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SESSION))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SESSION"><?=SESSION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="SESSION_NO" >
														<select id="SESSION_NO" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SESSION_NO))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SESSION_NO"><?=SESSION_NO?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="SCHEDULED_CLASS_DATE" >
														<select id="SCHEDULED_CLASS_DATE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",SCHEDULED_CLASS_DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="SCHEDULED_CLASS_DATE"><?=SCHEDULED_CLASS_DATE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="CLASS_START_TIME" >
														<select id="CLASS_START_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CLASS_START_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CLASS_START_TIME"><?=CLASS_START_TIME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="CLASS_END_TIME" >
														<select id="CLASS_END_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CLASS_END_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="CLASS_END_TIME"><?=CLASS_END_TIME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="STUDENT_ID" >
														<select id="STUDENT_ID" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENT_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="STUDENT_ID"><?=STUDENT_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="STUDENTS" >
														<select id="STUDENTS" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENTS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="STUDENTS"><?=STUDENTS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="ATTENDANCE_HOURS" >
														<select id="ATTENDANCE_HOURS" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ATTENDANCE_HOURS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="ATTENDANCE_HOURS"><?=ATTENDANCE_HOURS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="ATTENDANCE_CODE" >
														<select id="ATTENDANCE_CODE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ATTENDANCE_CODE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="ATTENDANCE_CODE"><?=ATTENDANCE_CODE?></label>
													</div>
												</div>
											</div>
											
										</div>
										 <div class="col-md-6">
											<div class="col-lg-12" style="color:red" >
											<? if(!empty($error)){
												echo "<u>Below Data Not Imported due to below Reason</u><br />";
												foreach($error as $error1)
													echo $error1."<br />";
											} else 
												if($flag == 1)
													echo "Uploaded Successfully"; ?>
											</div>
										</div>
									</div>
									
									
									<br />
									<div class="row">
                                        <div class="col-md-4">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=UPLOAD?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='attendance_entry'" ><?=CANCEL?></button>
												
											</div>
										</div>
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
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

</body>

</html>