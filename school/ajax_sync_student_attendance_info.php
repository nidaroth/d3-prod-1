<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER 	= $_REQUEST['id']; 
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid']; 
$PK_COURSE_OFFERING	= $_REQUEST['coid']; 
$syn_process	= $_REQUEST['syn_process'];

//SELECT * FROM `S_COURSE_OFFERING_SCHEDULE_DETAIL` WHERE `PK_COURSE_OFFERING` = 220587 AND `PK_ACCOUNT` = 15 AND ACTIVE=1
$res_type = $db->Execute("select * from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SCHEDULE_DATE ASC, START_TIME ASC");

// first check student is assigned to co or not
$res_s_course = $db->Execute("SELECT * FROM `S_STUDENT_COURSE` WHERE `PK_ACCOUNT` =  '$_SESSION[PK_ACCOUNT]' AND `PK_STUDENT_MASTER` =  '$PK_STUDENT_MASTER' AND `PK_STUDENT_ENROLLMENT` = '$PK_STUDENT_ENROLLMENT' AND PK_COURSE_OFFERING =$PK_COURSE_OFFERING");
$PK_STUDENT_COURSE = $res_s_course->fields['PK_STUDENT_COURSE'];

//check for scheduled or not in attendace
$res_s_sch = $db->Execute("SELECT * FROM `S_STUDENT_SCHEDULE` WHERE `PK_ACCOUNT` =  '$_SESSION[PK_ACCOUNT]' AND `PK_STUDENT_MASTER` =  '$PK_STUDENT_MASTER' AND `PK_STUDENT_ENROLLMENT` = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE ='$PK_STUDENT_COURSE'");

// echo "SELECT * FROM `S_STUDENT_SCHEDULE` WHERE `PK_ACCOUNT` =  '$_SESSION[PK_ACCOUNT]' AND `PK_STUDENT_MASTER` =  '$PK_STUDENT_MASTER' AND `PK_STUDENT_ENROLLMENT` = '$PK_STUDENT_ENROLLMENT' AND PK_STUDENT_COURSE ='$PK_STUDENT_COURSE'";

// echo $res_s_sch->RecordCount();
$flag = 0;
if($syn_process==1){
	if($res_type->RecordCount() > 0 && $res_s_course->RecordCount() > 0 && $res_s_sch->RecordCount()==0){
		echo "1"; // show sync button
		
	}else{
		echo "0";
	}
}else{
	

	 $res_sch = $db->Execute("SELECT * FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

	 if($res_sch->RecordCount() > 0){

		while (!$res_sch->EOF) {

			$STUDENT_SCHEDULE['SCHEDULE_DATE'] 	 	 	 			= $res_sch->fields['SCHEDULE_DATE'];
			$STUDENT_SCHEDULE['START_TIME'] 	 	 	 			= $res_sch->fields['START_TIME'];
			$STUDENT_SCHEDULE['END_TIME'] 	 	 		 			= $res_sch->fields['END_TIME'];
			$STUDENT_SCHEDULE['HOURS'] 	 	 			 			= $res_sch->fields['HOURS'];
			$STUDENT_SCHEDULE['PK_CAMPUS_ROOM'] 	 	 			= $res_sch->fields['PK_CAMPUS_ROOM'];
			$STUDENT_SCHEDULE['PK_STUDENT_COURSE'] 	 	 			= $PK_STUDENT_COURSE;
			$STUDENT_SCHEDULE['PK_STUDENT_ENROLLMENT'] 	 			= $PK_STUDENT_ENROLLMENT;
			$STUDENT_SCHEDULE['PK_SCHEDULE_TYPE'] 	 	 			= 1;
			$STUDENT_SCHEDULE['PK_COURSE_OFFERING_SCHEDULE_DETAIL']	= $res_sch->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
			$STUDENT_SCHEDULE['PK_STUDENT_MASTER'] 	 	 			= $PK_STUDENT_MASTER;
			$STUDENT_SCHEDULE['PK_ACCOUNT'] 				 		= $_SESSION['PK_ACCOUNT'];
			$STUDENT_SCHEDULE['CREATED_BY']  			 			= $_SESSION['PK_USER'];
			$STUDENT_SCHEDULE['CREATED_ON']  			 			= date("Y-m-d H:i");
			db_perform('S_STUDENT_SCHEDULE', $STUDENT_SCHEDULE, 'insert');
			$PK_STUDENT_SCHEDULE = $db->insert_ID();
	
			
			
			//if($res_sch->fields['COMPLETED'] == 1 || strtotime($STUDENT_SCHEDULE['SCHEDULE_DATE']) < strtotime(date("Y-m-d")) ){
				$COMPLETED = $res_sch->fields['COMPLETED'];
				if($COMPLETED == 1) {
					$PK_ATTENDANCE_CODE = 7;
					$ATTENDANCE_HOURS	= 0;
				} else {
					$PK_ATTENDANCE_CODE = $DEFAULT_ATTENDANCE_CODE;
					$ATTENDANCE_HOURS	= $STUDENT_SCHEDULE['HOURS'];
					
					if($PK_ATTENDANCE_CODE == "" || $PK_ATTENDANCE_CODE == 0)
						$PK_ATTENDANCE_CODE = 14;
				}
				$res_def_att_typ = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPES from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$STUDENT_SCHEDULE[PK_COURSE_OFFERING_SCHEDULE_DETAIL]' ");
				
				//Ticket # 1100
				$res_att_pre = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

				
				if($res_att_pre->RecordCount() == 0) {
					$ATTENDANCE_DETAIL = array();
					$ATTENDANCE_DETAIL['PK_STUDENT_MASTER'] 					= $STUDENT_SCHEDULE['PK_STUDENT_MASTER'];
					$ATTENDANCE_DETAIL['PK_STUDENT_ENROLLMENT'] 				= $STUDENT_SCHEDULE['PK_STUDENT_ENROLLMENT'];
					$ATTENDANCE_DETAIL['PK_STUDENT_SCHEDULE'] 					= $PK_STUDENT_SCHEDULE;
					$ATTENDANCE_DETAIL['ATTENDANCE_HOURS'] 						= $ATTENDANCE_HOURS;
					$ATTENDANCE_DETAIL['PK_ATTENDANCE_CODE'] 					= $PK_ATTENDANCE_CODE;
					$ATTENDANCE_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] 	= $STUDENT_SCHEDULE['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
					$ATTENDANCE_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPESS']			= $res_def_att_typ->fields['PK_ATTENDANCE_ACTIVITY_TYPES'];
					$ATTENDANCE_DETAIL['COMPLETED']   							= $COMPLETED;
					$ATTENDANCE_DETAIL['PK_ACCOUNT']  							= $_SESSION['PK_ACCOUNT'];;
					$ATTENDANCE_DETAIL['CREATED_BY']  							= $_SESSION['PK_USER'];
					$ATTENDANCE_DETAIL['CREATED_ON']  							= date("Y-m-d H:i");
					db_perform('S_STUDENT_ATTENDANCE', $ATTENDANCE_DETAIL, 'insert');
				}
			
			//}

			$res_sch->MoveNext();
		}
	}
		
	echo "0"; //for hide button

}

