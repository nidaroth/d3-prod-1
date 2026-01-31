<?php
require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/time_clock.php");
require_once("../language/attendance_entry.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
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
	$newfile1 = $res->fields['FILE_LOCATION'];

	if ($newfile1 != ""){
		$extn = explode(".",$newfile1);
		$ii = count($extn) - 1;

		if(strtolower($extn[$ii]) == 'xlsx' || strtolower($extn[$ii]) == 'xls' || strtolower($extn[$ii]) == 'csv'){
			$file_name=explode('/',$newfile1);
			// print_r($file_name);
			// exit;
			$file_location="temp/".$file_name[7];
			$inputFileName = $file_location;
			
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
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'STUDENT_ID' ");
		$STUDENT_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'BADGE_ID' ");
		$BADGE_ID_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'DATE' ");
		$DATE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TIME' ");
		$TIME_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'IN_TIME' ");
		$IN_TIME_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'OUT_TIME' ");
		$OUT_TIME_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'BREAK_IN_MIN' ");
		$BREAK_IN_MIN_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ATTENDANCE_HOUR' ");
		$ATTENDANCE_HOUR_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'COURSE_CODE' ");
		$COURSE_CODE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION' ");
		$SESSION_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'SESSION_NO' ");
		$SESSION_NO_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'TERM' ");
		$TERM_COL = $res->fields['EXCEL_COLUMN'];

		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'ATTENDANCE_CODE' ");
		$ATTENDANCE_CODE_COL = $res->fields['EXCEL_COLUMN'];
		
		$res = $db->Execute("SELECT EXCEL_COLUMN from Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' AND TABLE_COLUMN = 'PK_ATTENDANCE_ACTIVITY_TYPE' ");
		$PK_ATTENDANCE_ACTIVITY_TYPE_COL = $res->fields['EXCEL_COLUMN'];
		
		$i = 0;
		$imported_count = 0;
		$total_count	= 0;
		foreach($sheetData as $row )
		{

		if(!empty($row[$STUDENT_ID_COL])){			
			if($_POST['EXCLUDE_FIRST_ROW'] == 1){
				if($i == 0) {
					$i++;
					continue;
				}
			}
			
			$STUDENT_ID 					= trim($row[$STUDENT_ID_COL]);
			$BADGE_ID 						= trim($row[$BADGE_ID_COL]);
			$PK_ATTENDANCE_ACTIVITY_TYPE 	= trim($row[$PK_ATTENDANCE_ACTIVITY_TYPE_COL]);
			
			if($PK_ATTENDANCE_ACTIVITY_TYPE != '') {
				$res_att_act_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE from M_ATTENDANCE_ACTIVITY_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(ATTENDANCE_ACTIVITY_TYPE) = '$PK_ATTENDANCE_ACTIVITY_TYPE' ");
				$PK_ATTENDANCE_ACTIVITY_TYPE = $res_att_act_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'];
			}
			
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL = '';
			
			$TIME_CLOCK_PROCESSOR_DETAIL = array();
			
			$DATE 	= trim($row[$DATE_COL]);
			$DATE 	= str_replace("/","-",$DATE);
			$DATE1 	= explode("-",$DATE);
			if($DATE1[2] < 2000)
				$year = 2000 + $DATE1[2];
			else
				$year = $DATE1[2];
			
			$DATE = date("Y-m-d",strtotime($year.'-'.$DATE1[0].'-'.$DATE1[1]));
			if($_GET['t'] == 1){
				$s_temp  = preg_replace('/\s+/', '', $row[$TIME_COL]);
				$s_temp1 = $s_temp[strlen($s_temp)-1];
				$s_temp2 = $s_temp[strlen($s_temp)-2].$s_temp[strlen($s_temp)-1];
				
				if(strtolower($s_temp1) == 'a')
					$s_temp = str_replace("a"," AM",$s_temp);
				else if(strtolower($s_temp1) == 'p')
					$s_temp = str_replace("p"," PM",$s_temp);
				else if(strtolower($s_temp2) == 'am')
					$s_temp = str_replace("am"," AM",$s_temp);
				else if(strtolower($s_temp2) == 'pm')
					$s_temp = str_replace("pm"," PM",$s_temp);
				
				    $START_TIME = date("H:i:00",strtotime(trim($s_temp)));

				$stud_cond = "";
				if($STUDENT_ID_COL != '')
					$stud_cond = " AND STUDENT_ID = '$STUDENT_ID' AND STUDENT_ID != '' ";
				else
					$stud_cond = " AND BADGE_ID = '$BADGE_ID' AND BADGE_ID != ''  ";
		
				$res = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, CHECK_IN_TIME from S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $stud_cond AND CHECK_IN_DATE = '$DATE' ");
				if($res->RecordCount() == 0) {
					$MESSAGE = '';

					$res_stud = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER from S_STUDENT_MASTER,S_STUDENT_ACADEMICS,S_STUDENT_ENROLLMENT,M_STUDENT_STATUS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $stud_cond AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND ADMISSIONS = 0 AND ARCHIVED = 0");
					if($res_stud->RecordCount() == 0) {
						$MESSAGE 			= 'Student ID/Badge ID not Found';
						$PK_STUDENT_MASTER 	= "";
					} else {
						$PK_STUDENT_MASTER 	= $res_stud->fields['PK_STUDENT_MASTER'];
						$DATE_TIME 		= $DATE.' '.$START_TIME;
						$FROM_DATE_TIME = date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($DATE_TIME)));
						$TO_DATE_TIME 	= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($DATE_TIME)));
						$PK_COURSE_OFFERING="";

						$res_co = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_COURSE.PK_COURSE_OFFERING, PK_STUDENT_ENROLLMENT, PK_ATTENDANCE_CODE FROM S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND CONCAT(SCHEDULE_DATE,' ',START_TIME) BETWEEN '$FROM_DATE_TIME' and '$TO_DATE_TIME' ");
						if($res_co->RecordCount() == 0) {
							// $res_cos = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_COURSE.PK_COURSE_OFFERING, PK_STUDENT_ENROLLMENT, PK_ATTENDANCE_CODE FROM S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND SCHEDULE_DATE='".date('Y-m-d',strtotime($DATE))."'");
							// if($res_cos->RecordCount()>0){
				
							// 	$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_cos->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
							// 	$PK_COURSE_OFFERING=$res_cos->fields['PK_COURSE_OFFERING'];
							// }
							$MESSAGE = 'Non-Scheduled';
						} else {
							while (!$res_co->EOF) {
								$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_co->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
								
								$res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
					
								if($res_sch->RecordCount() > 0) {
									$PK_STUDENT_SCHEDULE = $res_sch->fields['PK_STUDENT_SCHEDULE'];
									
									$res_att = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' ");
									if($res_att->RecordCount() > 0) {
										$MESSAGE = 'Item may already exist in the student record';
										break;
									}
								}
								
								$res_co->MoveNext();
							}
						}
					}
					
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPE']  		= $PK_ATTENDANCE_ACTIVITY_TYPE;
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL']  = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT']   			= $res_co->fields['PK_STUDENT_ENROLLMENT'];
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING']   				= $res_co->fields['PK_COURSE_OFFERING']?$res_co->fields['PK_COURSE_OFFERING']:$PK_COURSE_OFFERING;
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $res_co->fields['PK_ATTENDANCE_CODE'];
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
					$TIME_CLOCK_PROCESSOR_DETAIL['STUDENT_ID'] 	  						= $STUDENT_ID;
					$TIME_CLOCK_PROCESSOR_DETAIL['BADGE_ID'] 	  						= $BADGE_ID;
					$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] 	  						= $MESSAGE;
					$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_DATE'] 						= $DATE;
					$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_TIME'] 						= $START_TIME;
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_TIME_CLOCK_PROCESSOR'] 			= $_GET['c_id'];
					$TIME_CLOCK_PROCESSOR_DETAIL['PK_ACCOUNT'] 							= $_SESSION['PK_ACCOUNT'];

					db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'insert');
					$imported_count++;
				} else {
					$PK_TIME_CLOCK_PROCESSOR_DETAIL = $res->fields['PK_TIME_CLOCK_PROCESSOR_DETAIL'];
					
					$time1  = $res->fields['CHECK_IN_TIME'];
					$time2  = $START_TIME;
					$array1 = explode(':', $time1);
					$array2 = explode(':', $time2);

					$minutes1 = ($array1[0] * 60.0 + $array1[1]);
					$minutes2 = ($array2[0] * 60.0 + $array2[1]);

					$ATTENDANCE_HOUR = ($minutes2 - $minutes1) / 60;
					
					if($MESSAGE == '') {
						$res_11 = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, CHECK_IN_TIME from S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $stud_cond AND  CHECK_IN_DATE = '$DATE' AND CHECK_IN_TIME = '$time1' AND CHECK_OUT_TIME = '$START_TIME' ");
						if($res_11->RecordCount() > 0) {
							$MESSAGE = 'Potential duplicate exists in imported data';
						}
					}
					
					$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_DATE'] 	= $DATE;
					$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_TIME'] 	= $START_TIME;
					$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] = $ATTENDANCE_HOUR;
					db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'update'," PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' ");
				}
			} else if($_GET['t'] == 2){
				$s_temp  = preg_replace('/\s+/', '', $row[$IN_TIME_COL]);
				$s_temp1 = $s_temp[strlen($s_temp)-1];
				$s_temp2 = $s_temp[strlen($s_temp)-2].$s_temp[strlen($s_temp)-1];
				
				if(strtolower($s_temp1) == 'a')
					$s_temp = str_replace("a"," AM",$s_temp);
				else if(strtolower($s_temp1) == 'p')
					$s_temp = str_replace("p"," PM",$s_temp);
				else if(strtolower($s_temp2) == 'am')
					$s_temp = str_replace("am"," AM",$s_temp);
				else if(strtolower($s_temp2) == 'pm')
					$s_temp = str_replace("pm"," PM",$s_temp);
					
				$IN_TIME 		= date("H:i:0",strtotime(trim($s_temp)));
				
				$s_temp  = preg_replace('/\s+/', '', $row[$OUT_TIME_COL]);
				$s_temp1 = $s_temp[strlen($s_temp)-1];
				$s_temp2 = $s_temp[strlen($s_temp)-2].$s_temp[strlen($s_temp)-1];
				
				if(strtolower($s_temp1) == 'a')
					$s_temp = str_replace("a"," AM",$s_temp);
				else if(strtolower($s_temp1) == 'p')
					$s_temp = str_replace("p"," PM",$s_temp);
				else if(strtolower($s_temp2) == 'am')
					$s_temp = str_replace("am"," AM",$s_temp);
				else if(strtolower($s_temp2) == 'pm')
					$s_temp = str_replace("pm"," PM",$s_temp);
					
				$OUT_TIME 		= date("H:i:0",strtotime(trim($s_temp)));
				$BREAK_IN_MIN 	= $row[$BREAK_IN_MIN_COL];
				
				$stud_cond = "";
				if($STUDENT_ID_COL != '')
					$stud_cond = " AND STUDENT_ID = '$STUDENT_ID' AND STUDENT_ID != '' ";
				else
					$stud_cond = " AND BADGE_ID = '$BADGE_ID' AND BADGE_ID != ''  ";
		
				$MESSAGE = '';
				$res_stud = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER from S_STUDENT_MASTER,S_STUDENT_ACADEMICS,S_STUDENT_ENROLLMENT,M_STUDENT_STATUS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $stud_cond AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND ADMISSIONS = 0 AND ARCHIVED = 0");
				if($res_stud->RecordCount() == 0) {
					$MESSAGE 			= 'Student ID/Badge ID not Found';
					$PK_STUDENT_MASTER 	= "";
				} else {
					$PK_STUDENT_MASTER 	= $res_stud->fields['PK_STUDENT_MASTER'];
					$DATE_TIME 			= $DATE.' '.$IN_TIME;
					$FROM_DATE_TIME 	= date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($DATE_TIME)));
					$TO_DATE_TIME 		= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($DATE_TIME)));
					
					$res_co = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_COURSE.PK_COURSE_OFFERING, PK_STUDENT_ENROLLMENT, PK_ATTENDANCE_CODE FROM S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND CONCAT(SCHEDULE_DATE,' ',START_TIME) BETWEEN '$FROM_DATE_TIME' and '$TO_DATE_TIME' ");
					if($res_co->RecordCount() == 0) {
						$MESSAGE = 'Non-Scheduled';
					} else {
						while (!$res_co->EOF) {
							$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_co->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
							
							$res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
				
							if($res_sch->RecordCount() > 0) {
								$PK_STUDENT_SCHEDULE = $res_sch->fields['PK_STUDENT_SCHEDULE'];
								
								$res_att = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' ");
								if($res_att->RecordCount() > 0) {
									$MESSAGE = 'Item may already exist in the student record';
									break;
								}
							}
							
							$res_co->MoveNext();
						}
					}
				}
				
				$time1  = $IN_TIME;
				$time2  = $OUT_TIME;
				$array1 = explode(':', $time1);
				$array2 = explode(':', $time2);

				$minutes1 = ($array1[0] * 60.0 + $array1[1]);
				$minutes2 = ($array2[0] * 60.0 + $array2[1]);

				$ATTENDANCE_HOUR = ($minutes2 - $minutes1) / 60;
				
				if($MESSAGE == '') {
					$res_11 = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, CHECK_IN_TIME from S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond AND  CHECK_IN_DATE = '$DATE' AND CHECK_IN_TIME = '$START_TIME' ");
					if($res_11->RecordCount() > 0) {
						$MESSAGE = 'Potential duplicate exists in imported data';
					}
				}
				
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPE']  		= $PK_ATTENDANCE_ACTIVITY_TYPE;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL']  = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT']   			= $res_co->fields['PK_STUDENT_ENROLLMENT'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING']   				= $res_co->fields['PK_COURSE_OFFERING'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $res_co->fields['PK_ATTENDANCE_CODE'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
				$TIME_CLOCK_PROCESSOR_DETAIL['STUDENT_ID'] 	  						= $STUDENT_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['BADGE_ID'] 	  						= $BADGE_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] 	  						= $MESSAGE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_DATE'] 						= $DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_TIME'] 						= $IN_TIME;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_DATE'] 						= $DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_TIME'] 						= $OUT_TIME;
				$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] 					= $ATTENDANCE_HOUR;
				$TIME_CLOCK_PROCESSOR_DETAIL['BREAK_IN_MIN'] 						= $BREAK_IN_MIN;
				
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_TIME_CLOCK_PROCESSOR'] = $_GET['c_id'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
				db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'insert');
				$imported_count++;
				
			} else if($_GET['t'] == 3){
				$ATTENDANCE_HOUR 	= $row[$ATTENDANCE_HOUR_COL];
				
				$stud_cond = "";
				if($STUDENT_ID_COL != '')
					$stud_cond = " AND STUDENT_ID = '$STUDENT_ID' AND STUDENT_ID != '' ";
				else
					$stud_cond = " AND BADGE_ID = '$BADGE_ID' AND BADGE_ID != ''  ";

				$MESSAGE = '';
				$res_stud = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER from S_STUDENT_MASTER,S_STUDENT_ACADEMICS,S_STUDENT_ENROLLMENT,M_STUDENT_STATUS WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER $stud_cond AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND ADMISSIONS = 0 AND ARCHIVED = 0");
				if($res_stud->RecordCount() == 0) {
					$MESSAGE 			= 'Student ID/Badge ID not Found';
					$PK_STUDENT_MASTER 	= "";
				} else {
					$PK_STUDENT_MASTER 	= $res_stud->fields['PK_STUDENT_MASTER'];

					$res_co = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL,S_STUDENT_COURSE.PK_COURSE_OFFERING, PK_STUDENT_ENROLLMENT, PK_ATTENDANCE_CODE FROM S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND SCHEDULE_DATE = '$DATE' ");
					if($res_co->RecordCount() == 0) {
						$MESSAGE = 'Non-Scheduled';
					} else {
						while (!$res_co->EOF) {
							$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_co->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
							
							$res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
				
							if($res_sch->RecordCount() > 0) {
								$PK_STUDENT_SCHEDULE = $res_sch->fields['PK_STUDENT_SCHEDULE'];
								
								$res_att = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' ");
								if($res_att->RecordCount() > 0) {
									$MESSAGE = 'Item may already exist in the student record';
									break;
								}
							}
							
							$res_co->MoveNext();
						}
					}
				}

				if($MESSAGE == '') {
					$res_11 = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, CHECK_IN_TIME from S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  $stud_cond AND  CHECK_IN_DATE = '$DATE' ");
					if($res_11->RecordCount() > 0) {
						$MESSAGE = 'Potential duplicate exists in imported data';
					}
				}
				
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPE']  		= $PK_ATTENDANCE_ACTIVITY_TYPE;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL']  = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT']   			= $res_co->fields['PK_STUDENT_ENROLLMENT'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING']   				= $res_co->fields['PK_COURSE_OFFERING'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $res_co->fields['PK_ATTENDANCE_CODE'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
				$TIME_CLOCK_PROCESSOR_DETAIL['STUDENT_ID'] 	  						= $STUDENT_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['BADGE_ID'] 	  						= $BADGE_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] 	  						= $MESSAGE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_DATE'] 						= $DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_DATE'] 						= $DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] 					= $ATTENDANCE_HOUR;
				
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_TIME_CLOCK_PROCESSOR'] = $_GET['c_id'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
				db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'insert');
				$imported_count++;
			} else if($_GET['t'] == 4){
				$MESSAGE = "";
			
				$COURSE_CODE 			= trim($row[$COURSE_CODE_COL]);
				$SESSION 				= trim($row[$SESSION_COL]);
				$SESSION_NO 			= trim($row[$SESSION_NO_COL]);
				$SCHEDULED_CLASS_DATE 	= trim($row[$DATE_COL]);
				$CLASS_START_TIME 		= trim($row[$IN_TIME_COL]);
				$CLASS_END_TIME 		= trim($row[$OUT_TIME_COL]);
				$ATTENDANCE_HOUR 		= trim($row[$ATTENDANCE_HOUR_COL]);
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
				
				if($CLASS_START_TIME != '') {
					$s_temp  = preg_replace('/\s+/', '', $CLASS_START_TIME);
					$s_temp1 = $s_temp[strlen($s_temp)-1];
					$s_temp2 = $s_temp[strlen($s_temp)-2].$s_temp[strlen($s_temp)-1];
					
					if(strtolower($s_temp1) == 'a')
						$s_temp = str_replace("a"," AM",$s_temp);
					else if(strtolower($s_temp1) == 'p')
						$s_temp = str_replace("p"," PM",$s_temp);
					else if(strtolower($s_temp2) == 'am')
						$s_temp = str_replace("am"," AM",$s_temp);
					else if(strtolower($s_temp2) == 'pm')
						$s_temp = str_replace("pm"," PM",$s_temp);
						
					$CLASS_START_TIME = date("H:i:00",strtotime(trim($s_temp)));
				}
			
				if($CLASS_END_TIME != '') {
					$s_temp  = preg_replace('/\s+/', '', $CLASS_END_TIME);
					$s_temp1 = $s_temp[strlen($s_temp)-1];
					$s_temp2 = $s_temp[strlen($s_temp)-2].$s_temp[strlen($s_temp)-1];
					
					if(strtolower($s_temp1) == 'a')
						$s_temp = str_replace("a"," AM",$s_temp);
					else if(strtolower($s_temp1) == 'p')
						$s_temp = str_replace("p"," PM",$s_temp);
					else if(strtolower($s_temp2) == 'am')
						$s_temp = str_replace("am"," AM",$s_temp);
					else if(strtolower($s_temp2) == 'pm')
						$s_temp = str_replace("pm"," PM",$s_temp);
						
					$CLASS_END_TIME = date("H:i:00",strtotime(trim($s_temp)));
				}
				
				$stud_cond = "";
				if($STUDENT_ID_COL != '')
					$stud_cond = " AND STUDENT_ID = '$STUDENT_ID' AND STUDENT_ID != '' ";
				else
					$stud_cond = " AND BADGE_ID = '$BADGE_ID' AND BADGE_ID != ''  ";
				
				if($COURSE_CODE != '') {
					$res = $db->Execute("select PK_COURSE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(COURSE_CODE) = '$COURSE_CODE' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= 'Invalid '.COURSE_CODE.' <b>'.$COURSE_CODE.'</b><br />';
					else
						$PK_COURSE = $res->fields['PK_COURSE'];
				} else
					$MESSAGE .= ' <b>'.COURSE_CODE.' Empty</b><br />';
					
				if($TERM != '') {
					$PK_TERM_MASTER = str_replace("/","-",$TERM);
					$PK_TERM_MASTER = explode("-",$PK_TERM_MASTER);
					if($PK_TERM_MASTER[2] < 2000)
						$year = 2000 + $PK_TERM_MASTER[2];
					else
						$year = $PK_TERM_MASTER[2];
					
					$PK_TERM_MASTER = date("Y-m-d",strtotime($year.'-'.$PK_TERM_MASTER[0].'-'.$PK_TERM_MASTER[1]));
					
					$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND BEGIN_DATE = '$PK_TERM_MASTER' ");
					if($res_l->RecordCount() == 0) {
						$MESSAGE .= 'Invalid '.TERM.' <b>'.$TERM.'</b><br />';
					} else {
						while (!$res_l->EOF) { 
							$PK_TERM_MASTER_ARR[] = $res_l->fields['PK_TERM_MASTER'];
							$res_l->MoveNext();
						}
						$PK_TERM_MASTER = implode(",",$PK_TERM_MASTER_ARR);
					}
				}
					
				if($SESSION != '') {
					$res = $db->Execute("select PK_SESSION from M_SESSION WHERE ACTIVE = 1 AND TRIM(SESSION) = '$SESSION' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= 'Invalid '.SESSION.' <b>'.$SESSION.'</b><br />';
					else
						$PK_SESSION = $res->fields['PK_SESSION'];
				} else
					$MESSAGE .= ' <b>'.SESSION.' Empty</b><br />';
					
				if($SESSION_NO == '') {
					$MESSAGE .= ' <b>'.SESSION_NO.' Empty</b><br />';
				}
					
				if($PK_COURSE != '' && $SESSION_NO != '' && $PK_SESSION > 0) {
					$res = $db->Execute("select PK_COURSE_OFFERING from S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$PK_COURSE' AND SESSION_NO = '$SESSION_NO' AND PK_SESSION = '$PK_SESSION' AND PK_TERM_MASTER IN ($PK_TERM_MASTER) ");

					if($res->RecordCount() == 0)
						$MESSAGE .= COURSE_OFFERING.' Not Found<br />';
					else
						$PK_COURSE_OFFERING = $res->fields['PK_COURSE_OFFERING'];
				}
				
				if($PK_COURSE_OFFERING > 0) {
					$flag_1 = 1;
					if($SCHEDULED_CLASS_DATE == ''){
						$MESSAGE .= ' <b>'.SCHEDULED_CLASS_DATE.' Empty</b><br />';
						$flag_1 = 0;
					}  else {
						$SCHEDULED_CLASS_DATE = str_replace("/","-",$SCHEDULED_CLASS_DATE);
						$SCHEDULED_CLASS_DATE1 = explode("-",$SCHEDULED_CLASS_DATE);
						if($SCHEDULED_CLASS_DATE1[2] < 2000)
							$year = 2000 + $SCHEDULED_CLASS_DATE1[2];
						else
							$year = $SCHEDULED_CLASS_DATE1[2];
						
						$SCHEDULED_CLASS_DATE = date("Y/m/d",strtotime($year.'/'.$SCHEDULED_CLASS_DATE1[0].'/'.$SCHEDULED_CLASS_DATE1[1]));
					}
					if($CLASS_START_TIME == ''){
						$MESSAGE .= ' <b>'.CLASS_START_TIME.' Empty</b><br />';
						$flag_1 = 0;
					} 
					if($CLASS_END_TIME == ''){
						$MESSAGE .= ' <b>'.CLASS_END_TIME.' Empty</b><br />';
						$flag_1 = 0;
					} 
					
					if($flag_1 == 1) {
						$CLASS_START_TIME1 	= date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($SCHEDULED_CLASS_DATE.' '.$CLASS_START_TIME)));
						$CLASS_START_TIME2 	= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($SCHEDULED_CLASS_DATE.' '.$CLASS_START_TIME)));
						$CLASS_END_TIME1 	= date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($SCHEDULED_CLASS_DATE.' '.$CLASS_END_TIME)));
						$CLASS_END_TIME2 	= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($SCHEDULED_CLASS_DATE.' '.$CLASS_END_TIME)));
				
						$res = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND (CONCAT(SCHEDULE_DATE,' ',START_TIME) BETWEEN '$CLASS_START_TIME1' and '$CLASS_START_TIME2' OR  CONCAT(SCHEDULE_DATE,' ',END_TIME) BETWEEN '$CLASS_END_TIME1' and '$CLASS_END_TIME2' ) ");
						
						if($res->RecordCount() == 0)
							$MESSAGE .= SCHEDULED_CLASS_MEETING.' Not Found<br />';
						else
							$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
							$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[$PK_COURSE_OFFERING_SCHEDULE_DETAIL] = $PK_COURSE_OFFERING_SCHEDULE_DETAIL; /******* Ticket # 999***********/
					}
				}

				$res = $db->Execute("select PK_STUDENT_SCHEDULE, S_STUDENT_MASTER.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER, S_STUDENT_ACADEMICS WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER $stud_cond ");
				if($res->RecordCount() == 0)
					$MESSAGE .= STUDENTS.' Not Found<br />';
				else {
					$PK_STUDENT_SCHEDULE 	= $res->fields['PK_STUDENT_SCHEDULE'];
					$PK_STUDENT_MASTER 	 	= $res->fields['PK_STUDENT_MASTER'];
					$PK_STUDENT_ENROLLMENT 	= $res->fields['PK_STUDENT_ENROLLMENT'];
				}
				
				if($ATTENDANCE_HOUR == '') {
					$MESSAGE .= ' <b>'.ATTENDANCE_HOURS.' Empty</b><br />';
				}
				
				if($ATTENDANCE_CODE == '') {
					$MESSAGE .= ' <b>'.ATTENDANCE_CODE.' Empty</b><br />';
				} else {
					$res = $db->Execute("select PK_ATTENDANCE_CODE from M_ATTENDANCE_CODE WHERE ACTIVE = 1 AND CODE = '$ATTENDANCE_CODE' ");
					if($res->RecordCount() == 0)
						$MESSAGE .= 'Invalid '.ATTENDANCE_CODE.' <b>'.$ATTENDANCE_CODE.'</b><br />';
					else {
						$PK_ATTENDANCE_CODE = $res->fields['PK_ATTENDANCE_CODE'];
					}
				}
				
				if($CLASS_START_TIME != '')
					$CLASS_START_TIME = date("H:i:00",strtotime($CLASS_START_TIME));
					
				if($CLASS_END_TIME != '')
					$CLASS_END_TIME = date("H:i:00",strtotime($CLASS_END_TIME));
					
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPE']  		= $PK_ATTENDANCE_ACTIVITY_TYPE;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL']  = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT']   			= $PK_STUDENT_ENROLLMENT;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING']   				= $PK_COURSE_OFFERING;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $PK_ATTENDANCE_CODE;
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_MASTER']   				= $PK_STUDENT_MASTER;
				$TIME_CLOCK_PROCESSOR_DETAIL['STUDENT_ID'] 	  						= $STUDENT_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['BADGE_ID'] 	  						= $BADGE_ID;
				$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] 	  						= $MESSAGE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_DATE'] 						= $SCHEDULED_CLASS_DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_TIME'] 						= $CLASS_START_TIME;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_DATE'] 						= $SCHEDULED_CLASS_DATE;
				$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_TIME'] 						= $CLASS_END_TIME;
				$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] 					= $ATTENDANCE_HOUR;
				$TIME_CLOCK_PROCESSOR_DETAIL['BREAK_IN_MIN'] 						= $BREAK_IN_MIN;
				
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_TIME_CLOCK_PROCESSOR'] = $_GET['c_id'];
				$TIME_CLOCK_PROCESSOR_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
				db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'insert');
				$imported_count++;
			}
		} 
	}
		$total_count = $imported_count;

		if($_SESSION['PK_ACCOUNT']==505)
		{
				$create_temp_table= "CREATE TEMPORARY TABLE temp_data
				AS
				SELECT
					S_STUDENT_CAMPUS.PK_CAMPUS,
					S_TIME_CLOCK_PROCESSOR_DETAIL.CHECK_IN_DATE
				FROM
					S_TIME_CLOCK_PROCESSOR_DETAIL
				INNER JOIN
					S_STUDENT_SCHEDULE ON S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_TIME_CLOCK_PROCESSOR_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL
				INNER JOIN
					S_STUDENT_ENROLLMENT ON S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
				INNER JOIN
					S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
				WHERE
					PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]'
					AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
					AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL != 0
				LIMIT 1;";
				$db->Execute($create_temp_table);

				$select="select PK_CAMPUS,CHECK_IN_DATE from temp_data";
				$res=$db->Execute($select);
				$pk_campus=$res->fields['PK_CAMPUS'];
				$CHECK_IN_DATE=$res->fields['CHECK_IN_DATE'];


				$sqlForOtherStudnets=" SELECT
				DISTINCT S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL
			FROM
				S_COURSE_OFFERING_SCHEDULE_DETAIL
			LEFT JOIN
				S_STUDENT_SCHEDULE ON S_STUDENT_SCHEDULE.PK_COURSE_OFFERING_SCHEDULE_DETAIL = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL
			LEFT JOIN
				S_STUDENT_ENROLLMENT ON S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
			LEFT JOIN
				S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
			WHERE 
				S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
				AND S_STUDENT_CAMPUS.PK_CAMPUS IS NOT NULL
				AND S_STUDENT_CAMPUS.PK_CAMPUS = '$pk_campus'
				AND S_COURSE_OFFERING_SCHEDULE_DETAIL.SCHEDULE_DATE = '$CHECK_IN_DATE' GROUP BY S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING_SCHEDULE_DETAIL";
		} else {
		   $sqlForOtherStudnets="SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL > 0 GROUP By PK_COURSE_OFFERING_SCHEDULE_DETAIL ";
		}
		$res = $db->Execute($sqlForOtherStudnets);
		while (!$res->EOF) {
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];			

			$res_course_det = $db->Execute("SELECT S_STUDENT_SCHEDULE.PK_STUDENT_ENROLLMENT, S_STUDENT_SCHEDULE.PK_STUDENT_MASTER, S_STUDENT_SCHEDULE.SCHEDULE_DATE, S_STUDENT_SCHEDULE.START_TIME, S_STUDENT_SCHEDULE.END_TIME, STUDENT_ID, PK_COURSE_OFFERING, S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE , BADGE_ID
			FROM 
			S_STUDENT_SCHEDULE 
			LEFT JOIN S_STUDENT_COURSE ON S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE
			, S_STUDENT_MASTER 
			LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
			WHERE 
			S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
			PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND 
			PK_SCHEDULE_TYPE = 1 AND 
			S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND ARCHIVED = 0");
			while (!$res_course_det->EOF) {
				$PK_STUDENT_ENROLLMENT 	= $res_course_det->fields['PK_STUDENT_ENROLLMENT'];
				$PK_STUDENT_SCHEDULE 	= $res_course_det->fields['PK_STUDENT_SCHEDULE'];
				$PK_STUDENT_MASTER 	= $res_course_det->fields['PK_STUDENT_MASTER'];
				$PK_COURSE_OFFERING 	= $res_course_det->fields['PK_COURSE_OFFERING'];

				
				
				$res_temp = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ATTENDANCE_CODE = 7 ");
				if($res_temp->RecordCount() == 0){
					$resutl_2 = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'" );
					if($resutl_2->RecordCount() == 0) {

						$res_attendance= $db->Execute("SELECT PK_ATTENDANCE_CODE FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND $PK_COURSE_OFFERING = '$PK_COURSE_OFFERING'");
						$total_count++;
						$TIME_CLOCK_PROCESSOR_DETAIL = array();
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL']  = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_ENROLLMENT']   			= $PK_STUDENT_ENROLLMENT;
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING']   				= $res_course_det->fields['PK_COURSE_OFFERING'];
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $res_attendance-> fields['PK_ATTENDANCE_CODE']?$res_attendance-> fields['PK_ATTENDANCE_CODE']:'1';
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_STUDENT_MASTER']   				= $res_course_det->fields['PK_STUDENT_MASTER'];
						$TIME_CLOCK_PROCESSOR_DETAIL['STUDENT_ID'] 	  						= $res_course_det->fields['STUDENT_ID'];
						$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] 	  						= 'Student not found in file';
						$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_DATE'] 						= $res_course_det->fields['SCHEDULE_DATE'];
						$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_IN_TIME'] 						= $res_course_det->fields['START_TIME'];
						$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_DATE'] 						= $res_course_det->fields['SCHEDULE_DATE'];
						$TIME_CLOCK_PROCESSOR_DETAIL['CHECK_OUT_TIME'] 						= $res_course_det->fields['END_TIME'];
						$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] 					= 0;
						$TIME_CLOCK_PROCESSOR_DETAIL['BREAK_IN_MIN'] 						= 0;
						$TIME_CLOCK_PROCESSOR_DETAIL['NOT_FOUND_ON_FILE'] 					= 1;
						
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_TIME_CLOCK_PROCESSOR'] = $_GET['c_id'];
						$TIME_CLOCK_PROCESSOR_DETAIL['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
						if($_SESSION['PK_ACCOUNT']==505){
						db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'insert');
						}
					}
				}
				
				$res_course_det->MoveNext();
			}
			
			$res->MoveNext();
		}
		
		$TIME_CLOCK_PROCESSOR_2['IMPORTED_COUNT'] 	= $imported_count;
		$TIME_CLOCK_PROCESSOR_2['TOTAL_COUNT'] 		= $total_count;
		db_perform('S_TIME_CLOCK_PROCESSOR', $TIME_CLOCK_PROCESSOR_2, 'update'," PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		$db->Execute("UPDATE S_TIME_CLOCK_PROCESSOR_DETAIL SET SCHEDULE_FOUND = 1 WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[c_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL > 0 AND PK_STUDENT_ENROLLMENT !=''");
//exit;
		header("location:time_clock_result?id=".$_GET['c_id'].'&exclude='.$_POST['EXCLUDE_FIRST_ROW'].'&t='.$_GET['t']);
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
	<title><?=TIME_CLOCK_IMPORT.' '.MAPPING?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=TIME_CLOCK_IMPORT.' '.MAPPING?> </h4>
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
														<input type="hidden" name="FIELDS[]" value="STUDENT_ID" >
														<select id="STUDENT_ID" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",STUDENT_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
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
														<input type="hidden" name="FIELDS[]" value="BADGE_ID" >
														<select id="BADGE_ID" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",BADGE_ID))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="BADGE_ID"><?=BADGE_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="DATE" >
														<select id="DATE" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",DATE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="class"><?=DATE?></label>
													</div>
												</div>
											</div>
											
											<? if($_GET['t'] == 1){ ?>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group m-b-40">
															<input type="hidden" name="FIELDS[]" value="TIME" >
															<select id="TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
																<option value=""></option>
																<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
																while (!$res->EOF) { ?>
																	<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
																<? $res->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="TIME"><?=TIME?></label>
														</div>
													</div>
												</div>
											<? } else if($_GET['t'] == 2){ ?>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group m-b-40">
															<input type="hidden" name="FIELDS[]" value="IN_TIME" >
															<select id="IN_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
																<option value=""></option>
																<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
																while (!$res->EOF) { ?>
																	<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",IN_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
																<? $res->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="IN_TIME"><?=IN_TIME?></label>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group m-b-40">
															<input type="hidden" name="FIELDS[]" value="OUT_TIME" >
															<select id="OUT_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
																<option value=""></option>
																<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
																while (!$res->EOF) { ?>
																	<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",OUT_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
																<? $res->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="OUT_TIME"><?=OUT_TIME?></label>
														</div>
													</div>
												</div>
												
												<div class="row">
													<div class="col-md-6">
														<div class="form-group m-b-40">
															<input type="hidden" name="FIELDS[]" value="BREAK_IN_MIN" >
															<select id="BREAK_IN_MIN" name="EXCEL_COLUMN[]" class="form-control required-entry">
																<option value=""></option>
																<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
																while (!$res->EOF) { ?>
																	<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",BREAK_IN_MIN))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
																<? $res->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="BREAK_IN_MIN"><?=BREAK_IN_MIN?></label>
														</div>
													</div>
												</div>
											<? } else if($_GET['t'] == 3){ ?>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group m-b-40">
															<input type="hidden" name="FIELDS[]" value="ATTENDANCE_HOUR" >
															<select id="ATTENDANCE_HOUR" name="EXCEL_COLUMN[]" class="form-control required-entry">
																<option value=""></option>
																<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
																while (!$res->EOF) { ?>
																	<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ATTENDANCE_HOUR))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
																<? $res->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															 <label for="ATTENDANCE_HOUR"><?=ATTENDANCE_HOUR?></label>
														</div>
													</div>
												</div>
											<? } else if($_GET['t'] == 4){ ?>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="IN_TIME" >
														<select id="IN_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CLASS_START_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="IN_TIME"><?=CLASS_START_TIME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="OUT_TIME" >
														<select id="OUT_TIME" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",CLASS_END_TIME))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="OUT_TIME"><?=CLASS_END_TIME?></label>
													</div>
												</div>
											</div>
											
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
														<input type="hidden" name="FIELDS[]" value="ATTENDANCE_HOUR" >
														<select id="ATTENDANCE_HOUR" name="EXCEL_COLUMN[]" class="form-control required-entry">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ATTENDANCE_HOURS))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')'?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="ATTENDANCE_HOUR"><?=ATTENDANCE_HOURS?></label>
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
											<? } ?>
											
											<? $res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
											if($res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){ ?>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="hidden" name="FIELDS[]" value="PK_ATTENDANCE_ACTIVITY_TYPE" >
														<select id="PK_ATTENDANCE_ACTIVITY_TYPE" name="EXCEL_COLUMN[]" class="form-control">
															<option value=""></option>
															<? $res = $db->Execute("SELECT * FROM Z_EXCEL_MAP_DETAIL WHERE PK_MAP_MASTER = '$_GET[id]' ");
															while (!$res->EOF) { ?>
																<option value="<?=$res->fields['EXCEL_COLUMN']?>" <? if(strtolower(str_replace(" ","",$res->fields['EXCEL_COLUMN_NAME'])) == strtolower(str_replace(" ","",ACTIVITY_TYPE))) echo "selected='selected'"; ?> ><?=$res->fields['EXCEL_COLUMN_NAME'].'('.$res->fields['EXCEL_COLUMN'].')' ?></option>
															<? $res->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														 <label for="class"><?=ACTIVITY_TYPE?></label>
													</div>
												</div>
											</div>
											<? } ?>
											
											<div class="row">
												<div class="col-md-6">
													<div class="d-flex" >
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="EXCLUDE_FIRST_ROW" name="EXCLUDE_FIRST_ROW" value="1" >
															<label class="custom-control-label" for="EXCLUDE_FIRST_ROW"><?=EXCLUDE_FIRST_ROW ?></label>
														</div>
													</div>
												</div>
											</div>
											
										</div>
									</div>
									
									
									<br />
									<div class="row">
                                        <div class="col-md-4">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="button" onclick="validate_form()" name="btn" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_time_clock_import_review'" ><?=CANCEL?></button>
												
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
		
		function validate_form(){
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true) {
				if(document.getElementById('STUDENT_ID').value == '' && document.getElementById('BADGE_ID').value == '')
					alert('Please Select Student ID or Badge ID')
				else
					document.form1.submit();
			}
		}
	</script>

</body>

</html>
