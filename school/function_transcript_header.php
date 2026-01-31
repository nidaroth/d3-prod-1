<? function transcript_header($field, $cond){
	global $db;
	/* DIAM-1556 /DIAM-2180*/
	$add_field = ",IF(DETERMINATION_DATE = '0000-00-00','',DATE_FORMAT(DETERMINATION_DATE,'%m/%d/%Y' )) AS DETERMINATION_DATE,IF(DROP_DATE = '0000-00-00','',DATE_FORMAT(DROP_DATE,'%m/%d/%Y' )) AS DROP_DATE,M_CAMPUS_PROGRAM.DIPLOMA AS DIPLOMA_CERTIFICATE";
	/* DIAM-1556 /DIAM-2180*/
	$res = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, OFFICIAL_CAMPUS_NAME, CAMPUS_CODE, IF(DATE_OF_BIRTH = '0000-00-00','',DATE_FORMAT(DATE_OF_BIRTH,'%m/%d/%Y' )) AS DATE_OF_BIRTH, S_STUDENT_CONTACT.EMAIL, CONCAT(DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' ),' - ',DATE_FORMAT(S_TERM_MASTER.END_DATE,'%m/%d/%Y' )) AS TERM_RANGE, TERM_DESCRIPTION, IF(S_TERM_MASTER.END_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS END_DATE, IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y' )) AS BEGIN_DATE, M_ENROLLMENT_STATUS.DESCRIPTION as FULL_PART_TIME, M_FUNDING.FUNDING, IF(EXPECTED_GRAD_DATE = '0000-00-00','',DATE_FORMAT(EXPECTED_GRAD_DATE,'%m/%d/%Y' )) AS EXPECTED_GRAD_DATE, IF(GRADE_DATE = '0000-00-00','',DATE_FORMAT(GRADE_DATE,'%m/%d/%Y' )) AS GRADE_DATE, S_STUDENT_CONTACT.HOME_PHONE, IF(LDA = '0000-00-00','',DATE_FORMAT(LDA,'%m/%d/%Y' )) AS LDA, IF(MIDPOINT_DATE = '0000-00-00','',DATE_FORMAT(MIDPOINT_DATE,'%m/%d/%Y' )) AS MIDPOINT_DATE, M_CAMPUS_PROGRAM.HOURS, M_CAMPUS_PROGRAM.MONTHS, M_CAMPUS_PROGRAM.UNITS, M_CAMPUS_PROGRAM.WEEKS, SESSION, SESSION_ABBREVIATION, SSN as SSN_1, SSN as SSN_2, STUDENT_STATUS, STUDENT_ID, STUDENT_GROUP, M_CAMPUS_PROGRAM.CODE as PROGRAM_CODE, M_CAMPUS_PROGRAM.PROGRAM_TRANSCRIPT_CODE , M_CAMPUS_PROGRAM.DESCRIPTION as PROGRAM_DESCRIPTION, BADGE_ID, S_STUDENT_CONTACT.ADDRESS as STUD_ADDRESS_1, S_STUDENT_CONTACT.ADDRESS_1 as STUD_ADDRESS_2, CONCAT(S_STUDENT_CONTACT.CITY, ', ', STATE_CODE, ' - ', S_STUDENT_CONTACT.ZIP) as STUD_CITY_STATE_ZIP $add_field  
	FROM 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' 
	LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES 
	LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER 
	LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
	LEFT JOIN M_ENROLLMENT_STATUS ON M_ENROLLMENT_STATUS.PK_ENROLLMENT_STATUS = S_STUDENT_ENROLLMENT.PK_ENROLLMENT_STATUS 
	LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
	LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
	LEFT JOIN S_CAMPUS ON S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS 
	LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
	LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP  
	LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_STUDENT_ENROLLMENT.PK_SESSION 
	LEFT JOIN M_FUNDING ON M_FUNDING.PK_FUNDING = S_STUDENT_ENROLLMENT.PK_FUNDING 
	WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond $order_by");
	
	$str = "";
	if($field == "OFFICIAL_CAMPUS_NAME")
		$str = "Campus: ".$res->fields[$field];
	else if($field == "BADGE_ID")
		$str = "Badge ID:  ".$res->fields[$field];
	else if($field == "STUD_ADDRESS_1")
		$str = $res->fields[$field];
	else if($field == "STUD_ADDRESS_2")
		$str = $res->fields[$field];
	else if($field == "STUD_CITY_STATE_ZIP")
		$str = $res->fields[$field];
	else if($field == "CAMPUS_CODE")
		$str = "Campus:  ".$res->fields[$field];
	else if($field == "DATE_OF_BIRTH")
		$str = "DOB: ".$res->fields[$field];
	else if($field == "EMAIL")
		$str = "Email: ".$res->fields[$field];
	else if($field == "TERM_RANGE")
		$str = "Start: ".$res->fields[$field];
	else if($field == "TERM_DESCRIPTION")
		$str = "First Term: ".$res->fields[$field];
	else if($field == "END_DATE")
		$str = "End: ".$res->fields[$field];
	else if($field == "BEGIN_DATE")
		$str = "First Term: ".$res->fields[$field];
	else if($field == "FULL_PART_TIME")
		$str = "FT/PT: ".$res->fields[$field];
	else if($field == "FUNDING")
		$str = "Funding: ".$res->fields[$field];
	else if($field == "EXPECTED_GRAD_DATE")
		$str = "Exp Grad Date: ".$res->fields[$field];
	else if($field == "GRADE_DATE")
		$str = "Grad Date: ".$res->fields[$field];
	else if($field == "STUDENT_GROUP")
		$str = "Group: ".$res->fields[$field];
	else if($field == "HOME_PHONE")
		$str = "Phone: ".$res->fields[$field];
	else if($field == "LDA")
		$str = "LDA: ".$res->fields[$field];
	else if($field == "MIDPOINT_DATE")
		$str = "Mid-Point: ".$res->fields[$field];
	else if($field == "PROGRAM_CODE")
		$str = "Program: ".$res->fields[$field];
	else if($field == "PROGRAM_CODE_DESCRIPTION")
		$str = "Program: ".$res->fields['PROGRAM_CODE']." - ".$res->fields['PROGRAM_DESCRIPTION'];
	else if($field == "PROGRAM_TRANSCRIPT_CODE")
		$str = "Program Transcript Code: ".$res->fields[$field];
	else if($field == "PROGRAM_DESCRIPTION")
		$str = "Program Description: ".$res->fields[$field];
	else if($field == "HOURS")
		$str = "Program Hours: ".$res->fields[$field];
	else if($field == "MONTHS")
		$str = "Program Months: ".$res->fields[$field];
	else if($field == "UNITS")
		$str = "Program Units: ".$res->fields[$field];
	else if($field == "WEEKS")
		$str = "Program Weeks: ".$res->fields[$field];
	else if($field == "SESSION_ABBREVIATION")
		$str = "Session: ".$res->fields['SESSION'];
	else if($field == "DROP_DATE") //DIAM-1556
		$str = "Drop Date: ".$res->fields[$field];
	else if($field == "DETERMINATION_DATE") //DIAM-1556
		$str = "Determination Date: ".$res->fields[$field];
	else if($field == "DIPLOMA_CERTIFICATE"){ //DIAM-2180
		$certificates = array("0"=>"","1" =>"Diploma","2" =>"Certificate","3" =>"N/A","4" =>"Degree");
		$str = "Diploma/Certificate: ".$certificates[$res->fields[$field]];	
	}else if($field == "SSN_1") {
		$value = "";
		if($res->fields[$field] != ''){
			$value 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$res->fields[$field]);
		}
		
		$str = "SSN: ".$value;
	} else if($field == "SSN_2") {
		$value = "";
		if($res->fields[$field] != ''){
			$SSN_1 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$res->fields[$field]);
			$SSN_ARR = explode("-",$SSN_1);
			$value 	 = 'xxx-xx-'.$SSN_ARR[2];
		}
		$str = "SSN: ".$value;
	} else if($field == "STUDENT_STATUS")
		$str = "Status: ".$res->fields[$field];
	else if($field == "STUDENT_ID")
		$str = "Student ID: ".$res->fields[$field];
	else {
		$PK_STUDENT_MASTER 	= $res->fields['PK_STUDENT_MASTER'];
		$value				= '';
		$res_fields = $db->Execute("SELECT FIELD_NAME, PK_DATA_TYPES from S_CUSTOM_FIELDS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_FIELDS.ACTIVE = 1 AND PK_CUSTOM_FIELDS = '$field' ");
		
		if($res_fields->fields['PK_DATA_TYPES'] == 1 || $res_fields->fields['PK_DATA_TYPES'] == 4) { 
			//Text, Date
			$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$field' ");
			$value = $res_stu_cus->fields['FIELD_VALUE'];
			
			if($res_fields->fields['PK_DATA_TYPES'] == 4 && $value != ''){
				$value = date("m/d/Y",strtotime($value));
			}
		} else if($res_fields->fields['PK_DATA_TYPES'] == 2) { 
			//Drop Down
			$res_stu_cus = $db->Execute("select OPTION_NAME FROM S_STUDENT_CUSTOM_FIELDS, S_USER_DEFINED_FIELDS_DETAIL WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$field' AND PK_USER_DEFINED_FIELDS_DETAIL =  FIELD_VALUE");
			$value = $res_stu_cus->fields['OPTION_NAME'];
			
		} else if($res_fields->fields['PK_DATA_TYPES'] == 3) { 
			//Multiple Choice
			$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_CUSTOM_FIELDS = '$field' ");
			$value = $res_stu_cus->fields['FIELD_VALUE'];
			
			$res_stu_cus = $db->Execute("select GROUP_CONCAT(OPTION_NAME ORDER BY OPTION_NAME ASC SEPARATOR ', ') as OPTION_NAME FROM S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($value)  ");
			$value = $res_stu_cus->fields['OPTION_NAME'];
		}
		
		if($value != '')
			$str = $res_fields->fields['FIELD_NAME'].": ".$value;
	}
		
		//DIAM-2159
		if($field =='LAST_CLASS_ATTENDED_DATE'){
			$PK_STUDENT_MASTER 	= $res->fields['PK_STUDENT_MASTER'];
			$LCDA_DATE = get_last_class_attened_date($PK_STUDENT_MASTER, $cond);
			$str = "Last Class Attended Date: ".$LCDA_DATE;		
		}
		//DIAM-2159


	return $str;
}

//DIAM-2159
function get_last_class_attened_date($PK_STUDENT_MASTER, $cond){
	global $db;

	$cond = str_replace("S_STUDENT_ENROLLMENT","S_STUDENT_SCHEDULE",$cond);

	/** DIAM-1303 **/
	$res = $db->Execute("SELECT IF(S_STUDENT_SCHEDULE.SCHEDULE_DATE != '0000-00-00', DATE_FORMAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,'%m/%d/%Y'),'') AS SCHEDULE_DATE, S_STUDENT_SCHEDULE.START_TIME, S_STUDENT_SCHEDULE.END_TIME, S_STUDENT_SCHEDULE.HOURS, S_STUDENT_ATTENDANCE.COMPLETED AS COMPLETED_1, IF(S_STUDENT_ATTENDANCE.COMPLETED = 1,'Y','') as COMPLETED, S_STUDENT_ATTENDANCE.ATTENDANCE_HOURS, S_STUDENT_SCHEDULE.PK_SCHEDULE_TYPE,S_STUDENT_SCHEDULE.HOURS
	FROM 
	S_STUDENT_SCHEDULE 
	LEFT JOIN S_STUDENT_ATTENDANCE ON S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE
	-- LEFT JOIN M_ATTENDANCE_CODE ON M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE 
	WHERE 
	S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' $cond AND 
	S_STUDENT_ATTENDANCE.COMPLETED = 1 AND (PK_SCHEDULE_TYPE = 1 || PK_SCHEDULE_TYPE=2) AND 
	S_STUDENT_ATTENDANCE.ATTENDANCE_HOURS != '0.00' AND S_STUDENT_ATTENDANCE.PK_ATTENDANCE_CODE='14' AND
	S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY S_STUDENT_SCHEDULE.SCHEDULE_DATE DESC, S_STUDENT_SCHEDULE.START_TIME DESC LIMIT 1 "); // , M_ATTENDANCE_CODE.CODE AS ATTENDANCE_CODE

	$COMPLETED         = $res->fields['COMPLETED'];
	// $ATTENDANCE_CODE   = $res->fields['ATTENDANCE_CODE'];
	$ATTENDANCE_HOURS  = $res->fields['ATTENDANCE_HOURS'];
	$HOURS  = $res->fields['HOURS'];
	// if($res->fields['COMPLETED_1'] == 1 || $res->fields['PK_SCHEDULE_TYPE'] == 2 || $res->fields['ATTENDANCE_CODE'] == 'P')
	// {
	// 	$ATTENDANCE_CODE   = $res->fields['ATTENDANCE_CODE'];
	// 	$ATTENDANCE_HOURS  = $res->fields['ATTENDANCE_HOURS'];
	// }
	if ($COMPLETED == 'Y' && ($ATTENDANCE_HOURS != '' || $HOURS!="")) // && $ATTENDANCE_CODE == 'P'
	{
		$Last_Attend_Class_Date = $res->fields['SCHEDULE_DATE'];
	}
	/** End DIAM-1303 **/

	return $Last_Attend_Class_Date;
}
//DIAM-2159
