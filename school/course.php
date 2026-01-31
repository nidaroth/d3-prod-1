<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$COURSE['COURSE_CODE']  			= $_POST['COURSE_CODE'];
	$COURSE['TRANSCRIPT_CODE']  		= $_POST['TRANSCRIPT_CODE'];
	$COURSE['COURSE_DESCRIPTION']  		= $_POST['COURSE_DESCRIPTION'];
	$COURSE['UNITS']  					= $_POST['UNITS'];
	$COURSE['HOURS']  					= $_POST['HOURS'];
	$COURSE['FA_UNITS']  				= $_POST['FA_UNITS'];
	$COURSE['PREP_HOURS']  				= $_POST['PREP_HOURS'];
	$COURSE['MAX_CLASS_SIZE']  			= $_POST['MAX_CLASS_SIZE'];
	$COURSE['PK_ATTENDANCE_TYPE']  		= $_POST['PK_ATTENDANCE_TYPE'];
	$COURSE['PK_ATTENDANCE_CODE']  		= $_POST['PK_ATTENDANCE_CODE'];
	$COURSE['EXTERNAL_ID']  			= $_POST['EXTERNAL_ID'];
	$COURSE['LMS_COURSE_TEMPLATE_ID']	= $_POST['LMS_COURSE_TEMPLATE_ID']; //Ticket # 1372
	$COURSE['ALLOW_ONLINE_ENROLLMENT']  = $_POST['ALLOW_ONLINE_ENROLLMENT'];
	$COURSE['FULL_COURSE_DESCRIPTION']  = $_POST['FULL_COURSE_DESCRIPTION'];
	
	if($_GET['id'] == ''){
		$OLD_PK_CAMPUS_PROGRAM = '';
		$COURSE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$COURSE['CREATED_BY']  = $_SESSION['PK_USER'];
		$COURSE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_COURSE', $COURSE, 'insert');
		$PK_COURSE = $db->insert_ID();
	} else {
		$PK_COURSE 				= $_GET['id'];
		$COURSE['ACTIVE'] 		= $_POST['ACTIVE'];
		$COURSE['EDITED_BY']   	= $_SESSION['PK_USER'];
		$COURSE['EDITED_ON']   	= date("Y-m-d H:i");
		db_perform('S_COURSE', $COURSE, 'update'," PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	foreach($_POST['PK_CAMPUS'] as $PK_CAMPUS) {
		$res = $db->Execute("SELECT PK_COURSE_CAMPUS FROM S_COURSE_CAMPUS WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); //ticket # 1533
		if($res->RecordCount() == 0) {
			$COURSE_CAMPUS['PK_CAMPUS']   	= $PK_CAMPUS;
			$COURSE_CAMPUS['PK_COURSE'] 	= $PK_COURSE;
			$COURSE_CAMPUS['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$COURSE_CAMPUS['CREATED_BY']  	= $_SESSION['PK_USER'];
			$COURSE_CAMPUS['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_CAMPUS', $COURSE_CAMPUS, 'insert');
			$PK_COURSE_CAMPUS_ARR[] = $db->insert_ID();
		} else {
			$PK_COURSE_CAMPUS_ARR[] = $res->fields['PK_COURSE_CAMPUS'];
		}
	}
	
	$cond = "";
	if(!empty($PK_COURSE_CAMPUS_ARR))
		$cond = " AND PK_COURSE_CAMPUS NOT IN (".implode(",",$PK_COURSE_CAMPUS_ARR).") ";
	
	$db->Execute("DELETE FROM S_COURSE_CAMPUS WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	foreach($_POST['PK_PREREQUISITE_COURSE'] as $PK_PREREQUISITE_COURSE) {
		$res = $db->Execute("SELECT PK_COURSE_PREREQUISITE FROM S_COURSE_PREREQUISITE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PREREQUISITE_COURSE = '$PK_PREREQUISITE_COURSE' "); 
		
		if($res->RecordCount() == 0) {
			$COURSE_PREREQUISITE['PK_COURSE']   			= $PK_COURSE;
			$COURSE_PREREQUISITE['PK_PREREQUISITE_COURSE'] 	= $PK_PREREQUISITE_COURSE;
			$COURSE_PREREQUISITE['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			$COURSE_PREREQUISITE['CREATED_BY']  			= $_SESSION['PK_USER'];
			$COURSE_PREREQUISITE['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_COURSE_PREREQUISITE', $COURSE_PREREQUISITE, 'insert');
			$PK_COURSE_PREREQUISITE_ARR[] = $db->insert_ID();
		} else {
			$PK_COURSE_PREREQUISITE_ARR[] = $res->fields['PK_COURSE_PREREQUISITE'];
		}
	}

	$cond = "";
	if(!empty($PK_COURSE_PREREQUISITE_ARR))
		$cond = " AND PK_COURSE_PREREQUISITE NOT IN (".implode(",",$PK_COURSE_PREREQUISITE_ARR).") ";
		
	$db->Execute("DELETE FROM S_COURSE_PREREQUISITE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 	
	
	//////////////////////////////////
	foreach($_POST['PK_COREQUISITES_COURSE'] as $PK_COREQUISITES_COURSE) {
		$res = $db->Execute("SELECT PK_COURSE_COREQUISITES FROM S_COURSE_COREQUISITES WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COREQUISITES_COURSE = '$PK_COREQUISITES_COURSE' "); 
		
		if($res->RecordCount() == 0) {
			$COURSE_COREQUISITES['PK_COURSE']   			= $PK_COURSE;
			$COURSE_COREQUISITES['PK_COREQUISITES_COURSE'] 	= $PK_COREQUISITES_COURSE;
			$COURSE_COREQUISITES['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
			$COURSE_COREQUISITES['CREATED_BY']  			= $_SESSION['PK_USER'];
			$COURSE_COREQUISITES['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_COURSE_COREQUISITES', $COURSE_COREQUISITES, 'insert');
			$PK_COURSE_COREQUISITES_ARR[] = $db->insert_ID();
		} else {
			$PK_COURSE_COREQUISITES_ARR[] = $res->fields['PK_COURSE_COREQUISITES'];
		}
	}

	$cond = "";
	if(!empty($PK_COURSE_COREQUISITES_ARR))
		$cond = " AND PK_COURSE_COREQUISITES NOT IN (".implode(",",$PK_COURSE_COREQUISITES_ARR).") ";
		
	$db->Execute("DELETE FROM S_COURSE_COREQUISITES WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 	
	///////////////////////////////////////////////////
	
	$i = 0;
	$TOTAL_FEE 			= 0;
	$TOTAL_SCHOOL_COST  = 0;
	foreach($_POST['PK_COURSE_FEE'] as $PK_COURSE_FEE) {
		$COURSE_FEE = array();
		$COURSE_FEE['PK_AR_LEDGER_CODE']   	= $_POST['PK_AR_LEDGER_CODE'][$i];
		$COURSE_FEE['DESCRIPTION']   		= $_POST['FEE_DESCRIPTION'][$i];
		$COURSE_FEE['FEE_AMT']   			= $_POST['FEE_AMT'][$i];
		$COURSE_FEE['ISBN_10']   			= $_POST['ISBN_10'][$i];
		$COURSE_FEE['ISBN_13']   			= $_POST['ISBN_13'][$i];
		$COURSE_FEE['SCHOOL_COST']   		= $_POST['SCHOOL_COST'][$i];
			
		if($PK_COURSE_FEE == '') {
			$COURSE_FEE['PK_COURSE'] 	= $PK_COURSE;
			$COURSE_FEE['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$COURSE_FEE['CREATED_BY']  	= $_SESSION['PK_USER'];
			$COURSE_FEE['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_FEE', $COURSE_FEE, 'insert');
			$PK_COURSE_FEE_ARR[] = $db->insert_ID();
		} else {
			$COURSE_FEE['EDITED_BY']  	= $_SESSION['PK_USER'];
			$COURSE_FEE['EDITED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_FEE', $COURSE_FEE, 'update'," PK_COURSE_FEE = '$PK_COURSE_FEE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$PK_COURSE_FEE_ARR[] = $PK_COURSE_FEE;
		}
		
		$TOTAL_FEE 			+= $COURSE_FEE['FEE_AMT'];
		$TOTAL_SCHOOL_COST  += $COURSE_FEE['SCHOOL_COST'];
		
		$i++;
	}
	
	$COURSE['TOTAL_FEE']   			= $TOTAL_FEE;
	$COURSE['TOTAL_SCHOOL_COST']   	= $TOTAL_SCHOOL_COST;
	db_perform('S_COURSE', $COURSE, 'update'," PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$cond = "";
	if(!empty($PK_COURSE_FEE_ARR))
		$cond = " AND PK_COURSE_FEE NOT IN (".implode(",",$PK_COURSE_FEE_ARR).") ";
	
	$db->Execute("DELETE FROM S_COURSE_FEE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	$i = 0;
	foreach($_POST['PK_COURSE_GRADE_BOOK'] as $PK_COURSE_GRADE_BOOK) {
		$GRADE_BOOK = array();
		$GRADE_BOOK['COLUMN_NO']   				= $_POST['COLUMN_NO'][$i];
		$GRADE_BOOK['CODE']   					= $_POST['CODE'][$i];
		$GRADE_BOOK['DESCRIPTION']   			= $_POST['GRADE_BOOK_DESCRIPTION'][$i];
		$GRADE_BOOK['PK_GRADE_BOOK_TYPE']   	= $_POST['PK_GRADE_BOOK_TYPE'][$i];
		$GRADE_BOOK['PERIOD']   				= $_POST['PERIOD'][$i];
		$GRADE_BOOK['POINTS']   				= $_POST['POINTS'][$i];
		$GRADE_BOOK['WEIGHT']   				= $_POST['WEIGHT'][$i];
		$GRADE_BOOK['WEIGHTED_POINTS']   		= $_POST['WEIGHTED_POINTS'][$i];
		
		if($PK_COURSE_GRADE_BOOK == '') {
			$GRADE_BOOK['PK_COURSE'] 	= $PK_COURSE;
			$GRADE_BOOK['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$GRADE_BOOK['CREATED_BY']  	= $_SESSION['PK_USER'];
			$GRADE_BOOK['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_GRADE_BOOK', $GRADE_BOOK, 'insert');
			$PK_COURSE_GRADE_BOOK_ARR[] = $db->insert_ID();
		} else {
			$GRADE_BOOK['EDITED_BY']  	= $_SESSION['PK_USER'];
			$GRADE_BOOK['EDITED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_GRADE_BOOK', $GRADE_BOOK, 'update'," PK_COURSE_GRADE_BOOK = '$PK_COURSE_GRADE_BOOK' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$PK_COURSE_GRADE_BOOK_ARR[] = $PK_COURSE_GRADE_BOOK;
		}
		
		$i++;
	}
	
	$cond = "";
	if(!empty($PK_COURSE_GRADE_BOOK_ARR))
		$cond = " AND PK_COURSE_GRADE_BOOK NOT IN (".implode(",",$PK_COURSE_GRADE_BOOK_ARR).") ";
	
	$db->Execute("DELETE FROM S_COURSE_GRADE_BOOK WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
		
	////////////////////////////////////
	foreach($_POST['schedule_count'] as $schedule_count){
		$OFFERING_SCHEDULE = array();
		$PK_SESSION = $_POST['DEF_SCH_PK_SESSION_'.$schedule_count];
		$OFFERING_SCHEDULE['PK_SESSION']  	= $PK_SESSION;
		$OFFERING_SCHEDULE['SUNDAY']  		= $_POST['SUNDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['MONDAY']  		= $_POST['MONDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['TUESDAY']  		= $_POST['TUESDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['WEDNESDAY'] 	= $_POST['WEDNESDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['THURSDAY']  	= $_POST['THURSDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['FRIDAY']  		= $_POST['FRIDAY_'.$schedule_count];
		$OFFERING_SCHEDULE['SATURDAY']  	= $_POST['SATURDAY_'.$schedule_count];
		
		$OFFERING_SCHEDULE['SUN_ROOM']  = $_POST['SUN_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['MON_ROOM']  = $_POST['MON_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['TUE_ROOM']  = $_POST['TUE_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['WED_ROOM']  = $_POST['WED_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['THU_ROOM']  = $_POST['THU_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['FRI_ROOM']  = $_POST['FRI_'.$schedule_count.'_ROOM'];
		$OFFERING_SCHEDULE['SAT_ROOM']  = $_POST['SAT_'.$schedule_count.'_ROOM'];
		
		if($OFFERING_SCHEDULE['SUNDAY'] == 1) {
			$OFFERING_SCHEDULE['SUN_START_TIME'] 					= $_POST['SUN_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['SUN_END_TIME']  					= $_POST['SUN_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['SUN_HOURS']  						= $_POST['SUN_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['SUN_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['SUN_START_TIME'] != '')
				$OFFERING_SCHEDULE['SUN_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SUN_START_TIME']));
				
			if($OFFERING_SCHEDULE['SUN_END_TIME'] != '')
				$OFFERING_SCHEDULE['SUN_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SUN_END_TIME']));
				
			if($OFFERING_SCHEDULE['SUN_START_TIME'] == '' || $OFFERING_SCHEDULE['SUN_END_TIME'] == '')
				$OFFERING_SCHEDULE['SUN_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['SUN_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['SUN_END_TIME']  					= '';
			$OFFERING_SCHEDULE['SUN_HOURS']  						= '';
			$OFFERING_SCHEDULE['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['MONDAY'] == 1) {
			$OFFERING_SCHEDULE['MON_START_TIME'] 					= $_POST['MON_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['MON_END_TIME']  					= $_POST['MON_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['MON_HOURS']  						= $_POST['MON_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['MON_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['MON_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['MON_START_TIME'] != '')
				$OFFERING_SCHEDULE['MON_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['MON_START_TIME']));
				
			if($OFFERING_SCHEDULE['MON_END_TIME'] != '')
				$OFFERING_SCHEDULE['MON_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['MON_END_TIME']));
				
			if($OFFERING_SCHEDULE['MON_START_TIME'] == '' || $OFFERING_SCHEDULE['MON_END_TIME'] == '')
				$OFFERING_SCHEDULE['MON_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['MON_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['MON_END_TIME']  					= '';
			$OFFERING_SCHEDULE['MON_HOURS']  						= '';
			$OFFERING_SCHEDULE['MON_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['TUESDAY'] == 1) {
			$OFFERING_SCHEDULE['TUE_START_TIME'] 					= $_POST['TUE_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['TUE_END_TIME']  					= $_POST['TUE_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['TUE_HOURS']  						= $_POST['TUE_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['TUE_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['TUE_START_TIME'] != '')
				$OFFERING_SCHEDULE['TUE_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['TUE_START_TIME']));
				
			if($OFFERING_SCHEDULE['TUE_END_TIME'] != '')
				$OFFERING_SCHEDULE['TUE_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['TUE_END_TIME']));
				
			if($OFFERING_SCHEDULE['TUE_START_TIME'] == '' || $OFFERING_SCHEDULE['TUE_END_TIME'] == '')
				$OFFERING_SCHEDULE['TUE_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['TUE_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['TUE_END_TIME']  					= '';
			$OFFERING_SCHEDULE['TUE_HOURS']  						= '';
			$OFFERING_SCHEDULE['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['WEDNESDAY'] == 1) {
			$OFFERING_SCHEDULE['WED_START_TIME'] 					= $_POST['WED_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['WED_END_TIME']  					= $_POST['WED_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['WED_HOURS']  						= $_POST['WED_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['WED_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['WED_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['WED_START_TIME'] != '')
				$OFFERING_SCHEDULE['WED_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['WED_START_TIME']));
				
			if($OFFERING_SCHEDULE['WED_END_TIME'] != '')
				$OFFERING_SCHEDULE['WED_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['WED_END_TIME']));
				
			if($OFFERING_SCHEDULE['WED_START_TIME'] == '' || $OFFERING_SCHEDULE['WED_END_TIME'] == '')
				$OFFERING_SCHEDULE['WED_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['WED_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['WED_END_TIME']  					= '';
			$OFFERING_SCHEDULE['WED_HOURS']  						= '';
			$OFFERING_SCHEDULE['WED_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['THURSDAY'] == 1) {
			$OFFERING_SCHEDULE['THU_START_TIME'] 					= $_POST['THU_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['THU_END_TIME']  					= $_POST['THU_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['THU_HOURS']  						= $_POST['THU_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['THU_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['THU_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['THU_START_TIME'] != '')
				$OFFERING_SCHEDULE['THU_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['THU_START_TIME']));
				
			if($OFFERING_SCHEDULE['THU_END_TIME'] != '')
				$OFFERING_SCHEDULE['THU_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['THU_END_TIME']));
				
			if($OFFERING_SCHEDULE['THU_START_TIME'] == '' || $OFFERING_SCHEDULE['THU_END_TIME'] == '')
				$OFFERING_SCHEDULE['THU_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['THU_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['THU_END_TIME']  					= '';
			$OFFERING_SCHEDULE['THU_HOURS']  						= '';
			$OFFERING_SCHEDULE['THU_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['FRIDAY'] == 1) {
			$OFFERING_SCHEDULE['FRI_START_TIME'] 					= $_POST['FRI_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['FRI_END_TIME']  					= $_POST['FRI_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['FRI_HOURS']  						= $_POST['FRI_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['FRI_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['FRI_START_TIME'] != '')
				$OFFERING_SCHEDULE['FRI_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['FRI_START_TIME']));
				
			if($OFFERING_SCHEDULE['FRI_END_TIME'] != '')
				$OFFERING_SCHEDULE['FRI_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['FRI_END_TIME']));
				
			if($OFFERING_SCHEDULE['FRI_START_TIME'] == '' || $OFFERING_SCHEDULE['FRI_END_TIME'] == '')
				$OFFERING_SCHEDULE['FRI_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['FRI_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['FRI_END_TIME']  					= '';
			$OFFERING_SCHEDULE['FRI_HOURS']  						= '';
			$OFFERING_SCHEDULE['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		if($OFFERING_SCHEDULE['SATURDAY'] == 1) {
			$OFFERING_SCHEDULE['SAT_START_TIME'] 					= $_POST['SAT_'.$schedule_count.'_START_TIME'];
			$OFFERING_SCHEDULE['SAT_END_TIME']  					= $_POST['SAT_'.$schedule_count.'_END_TIME'];
			$OFFERING_SCHEDULE['SAT_HOURS']  						= $_POST['SAT_'.$schedule_count.'_HOURS'];
			$OFFERING_SCHEDULE['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= $_POST['SAT_'.$schedule_count.'_PK_ATTENDANCE_ACTIVITY_TYPE'];
			
			if($OFFERING_SCHEDULE['SAT_START_TIME'] != '')
				$OFFERING_SCHEDULE['SAT_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SAT_START_TIME']));
				
			if($OFFERING_SCHEDULE['SAT_END_TIME'] != '')
				$OFFERING_SCHEDULE['SAT_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SAT_END_TIME']));
				
			if($OFFERING_SCHEDULE['SAT_START_TIME'] == '' || $OFFERING_SCHEDULE['SAT_END_TIME'] == '')
				$OFFERING_SCHEDULE['SAT_HOURS'] = '';
		} else {
			$OFFERING_SCHEDULE['SAT_START_TIME'] 					= '';
			$OFFERING_SCHEDULE['SAT_END_TIME']  					= '';
			$OFFERING_SCHEDULE['SAT_HOURS']  						= '';
			$OFFERING_SCHEDULE['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'] 	= '';
		}
		
		$res = $db->Execute("SELECT PK_COURSE_DEFAULT_SCHEDULE FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION = '$PK_SESSION' "); 
		if($res->RecordCount() == 0) {
			$OFFERING_SCHEDULE['PK_COURSE'] 	= $PK_COURSE;
			$OFFERING_SCHEDULE['PK_ACCOUNT'] 	= $_SESSION['PK_ACCOUNT'];
			$OFFERING_SCHEDULE['CREATED_BY']  	= $_SESSION['PK_USER'];
			$OFFERING_SCHEDULE['CREATED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_DEFAULT_SCHEDULE', $OFFERING_SCHEDULE, 'insert');
			$PK_COURSE_DEFAULT_SCHEDULE_ARR[] = $db->insert_ID();
		} else {
			$PK_COURSE_DEFAULT_SCHEDULE  	  = $res->fields['PK_COURSE_DEFAULT_SCHEDULE'];
			$PK_COURSE_DEFAULT_SCHEDULE_ARR[] = $PK_COURSE_DEFAULT_SCHEDULE;
			
			$OFFERING_SCHEDULE['EDITED_BY']  	= $_SESSION['PK_USER'];
			$OFFERING_SCHEDULE['EDITED_ON']  	= date("Y-m-d H:i");
			db_perform('S_COURSE_DEFAULT_SCHEDULE', $OFFERING_SCHEDULE, 'update'," PK_COURSE_DEFAULT_SCHEDULE = '$PK_COURSE_DEFAULT_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}
	}
	
	$cond = "";
	if(!empty($PK_COURSE_DEFAULT_SCHEDULE_ARR))
		$cond = " AND PK_COURSE_DEFAULT_SCHEDULE NOT IN (".implode(",",$PK_COURSE_DEFAULT_SCHEDULE_ARR).") ";
	
	$db->Execute("DELETE FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	///////////////////////////////////

	if($_GET['id'] == '')
		header("location:course?id=".$PK_COURSE);
	else if($_POST['SAVE_CONTINUE'] == 0)
		header("location:manage_course");
	else
		header("location:course?id=".$PK_COURSE."&tab=".str_replace("#","",$_POST['current_tab']));
	exit;
}
if($_GET['tab'] == 'course' || $_GET['tab'] == '')
	$course = 'active';
else if($_GET['tab'] == 'courseDetails')
	$courseDetails = 'active';
else if($_GET['tab'] == 'courseFeesTab')
	$courseFeesTab = 'active';
else if($_GET['tab'] == 'gradeBookTab')
	$gradeBookTab = 'active';
else if($_GET['tab'] == 'defaultScheduleTab')
	$defaultScheduleTab = 'active';	
else
	$course = 'active';
	
if($_GET['id'] == ''){
	$COURSE_CODE 				= '';
	$TRANSCRIPT_CODE 			= '';
	$COURSE_DESCRIPTION 		= '';
	$UNITS 						= '';
	$FA_UNITS 					= '';
	$HOURS 						= '';
	$PREP_HOURS 				= '';
	$MAX_CLASS_SIZE 			= '';
	$PK_ATTENDANCE_CODE 		= '';
	$PK_ATTENDANCE_TYPE			= '';
	$EXTERNAL_ID 			 	= '';
	$LMS_COURSE_TEMPLATE_ID		= ''; //Ticket # 1372
	$ALLOW_ONLINE_ENROLLMENT 	= '';
	$FULL_COURSE_DESCRIPTION 	= '';
	
	$PK_CAMPUS_mul = '';
} else {
	$res = $db->Execute("SELECT  * FROM S_COURSE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_course");
		exit;
	}
	
	$COURSE_CODE 				= $res->fields['COURSE_CODE'];
	$TRANSCRIPT_CODE 			= $res->fields['TRANSCRIPT_CODE'];
	$COURSE_DESCRIPTION 		= $res->fields['COURSE_DESCRIPTION'];
	$UNITS 						= $res->fields['UNITS'];
	$FA_UNITS 					= $res->fields['FA_UNITS'];
	$HOURS 						= $res->fields['HOURS'];
	$PREP_HOURS 				= $res->fields['PREP_HOURS'];
	$MAX_CLASS_SIZE 			= $res->fields['MAX_CLASS_SIZE'];
	$PK_ATTENDANCE_CODE 		= $res->fields['PK_ATTENDANCE_CODE'];
	$PK_ATTENDANCE_TYPE			= $res->fields['PK_ATTENDANCE_TYPE'];
	$EXTERNAL_ID 				= $res->fields['EXTERNAL_ID'];
	$LMS_COURSE_TEMPLATE_ID 	= $res->fields['LMS_COURSE_TEMPLATE_ID']; //Ticket # 1372
	$ALLOW_ONLINE_ENROLLMENT 	= $res->fields['ALLOW_ONLINE_ENROLLMENT'];
	$FULL_COURSE_DESCRIPTION 	= $res->fields['FULL_COURSE_DESCRIPTION'];
	$ACTIVE 					= $res->fields['ACTIVE'];
	
	$PK_CAMPUS_mul = '';
	$res_camp = $db->Execute("select PK_CAMPUS FROM S_COURSE_CAMPUS WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	while (!$res_camp->EOF) {
		if($PK_CAMPUS_mul != '')
			$PK_CAMPUS_mul .= ',';
			
		$PK_CAMPUS_mul .= $res_camp->fields['PK_CAMPUS'];
		
		$res_camp->MoveNext();
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
	<title><?=COURSE_PAGE_TITLE?> | <?=$title?></title>
	<style>
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
		li > a > label{position: unset !important;}
		
		/* Ticket # 1696 */
		.dropdown-menu>li>a { white-space: nowrap; } 
		.option_red > a > label{color:red !important}
		/* Ticket # 1696 */
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
                        <!-- Ticket # 1449  -->
                        <h4 class="text-themecolor">
							<? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=COURSE_PAGE_TITLE?>
							<? if($_GET['id'] != '') echo " - ".$COURSE_CODE; ?>
						</h4>
						<!-- Ticket # 1449  -->
                    </div>
                </div>
				<form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<ul class="nav nav-tabs customtab" role="tablist">
									<li class="nav-item"> <a class="nav-link <?=$course?>" data-toggle="tab" href="#course" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=TAB_COURSE?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$courseDetails?>" data-toggle="tab" href="#courseDetails" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=TAB_COURSE_DEATIL?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$courseFeesTab?>" data-toggle="tab" href="#courseFeesTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_COURSE_FEE?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$gradeBookTab?>" data-toggle="tab" href="#gradeBookTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_GRADE_BOOK?></span></a> </li>
									
									<li class="nav-item"> <a class="nav-link <?=$defaultScheduleTab?>" data-toggle="tab" href="#defaultScheduleTab" role="tab"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"><?=TAB_DEFAULT_SCHEDULE?></span></a> </li>
								</ul>
								
								<div class="card-body">
									<div class="tab-content">
										
										<div class="tab-pane <?=$course?>" id="course" role="tabpanel">
											<div class="row">
												<div class="col-sm-6">
													<div class="row">
														<div class="col-md-6">
															<!-- Ticket # 1044  -->
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="COURSE_CODE" name="COURSE_CODE" value="<?=$COURSE_CODE?>" onBlur="duplicate_check()" >
																<span class="bar"></span>
																<label for="COURSE_CODE"><?=COURSE_CODE?></label>
																<div id="already_exit" style="display:none;color:#ff0000;" ><?=COURSE_CODE?> already exists. Try with another.</div>
															</div>
															<!-- Ticket # 1044  -->
														</div>
													
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="UNITS" name="UNITS" value="<?=$UNITS?>">
																<span class="bar"></span>
																<label for="UNITS"><?=UNITS?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="TRANSCRIPT_CODE" name="TRANSCRIPT_CODE" value="<?=$TRANSCRIPT_CODE?>" >
																<span class="bar"></span>
																<label for="TRANSCRIPT_CODE"><?=TRANSCRIPT_CODE?></label>
															</div>
														</div>
													
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="FA_UNITS" name="FA_UNITS" value="<?=$FA_UNITS?>">
																<span class="bar"></span>
																<label for="FA_UNITS"><?=FA_UNITS?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="COURSE_DESCRIPTION" name="COURSE_DESCRIPTION" value="<?=$COURSE_DESCRIPTION?>">
																<span class="bar"></span>
																<label for="COURSE_DESCRIPTION"><?=COURSE_DESCRIPTION?></label>
															</div>
														</div>
													
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="HOURS" name="HOURS" value="<?=$HOURS?>">
																<span class="bar"></span>
																<label for="HOURS"><?=HOURS?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-12 col-sm-6 focused">
															<span class="bar"></span> 
															<label for="COREQUISITES"><?=COREQUISITES ?></label>
														</div>
														
														<div class="col-12 col-sm-6 focused">
															<span class="bar"></span> 
															<label for="PREREQUISITE"><?=PREREQUISITE?></label>
														</div>
													</div>
													
													<div class="row">
														<div class="col-12 col-sm-6 " id="PK_COREQUISITES_DIV" >
															<select id="PK_COREQUISITES_COURSE" name="PK_COREQUISITES_COURSE[]" multiple >
																<? /* Ticket #1696  */
																$res_type = $db->Execute("select PK_COURSE, CONCAT(COURSE_CODE , ' - ', TRANSCRIPT_CODE, ' - ', COURSE_DESCRIPTION) as COURSE_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE != '$_GET[id]' order by ACTIVE DESC, COURSE_CODE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['COURSE_CODE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = '';
																	$PK_COURSE = $res_type->fields['PK_COURSE'];
																	$res = $db->Execute("select PK_COURSE_COREQUISITES FROM S_COURSE_COREQUISITES WHERE PK_COREQUISITES_COURSE = '$PK_COURSE' AND PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																	if($res->RecordCount() > 0)
																		$selected = 'selected'; ?>
																	<option value="<?=$res_type->fields['PK_COURSE']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket #1696  */ ?>
															</select>
														</div>
														
														<div class="col-12 col-sm-6 " id="PK_PREREQUISITE_DIV" >
															<select id="PK_PREREQUISITE_COURSE" name="PK_PREREQUISITE_COURSE[]" multiple >
																<? /* Ticket #1696  */
																$res_type = $db->Execute("select PK_COURSE, CONCAT(COURSE_CODE , ' - ', TRANSCRIPT_CODE, ' - ', COURSE_DESCRIPTION) as COURSE_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE != '$_GET[id]' order by ACTIVE DESC, COURSE_CODE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['COURSE_CODE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = '';
																	$PK_COURSE = $res_type->fields['PK_COURSE'];
																	$res = $db->Execute("select PK_COURSE_PREREQUISITE FROM S_COURSE_PREREQUISITE WHERE PK_PREREQUISITE_COURSE = '$PK_COURSE' AND PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																	if($res->RecordCount() > 0)
																		$selected = 'selected'; ?>
																	<option value="<?=$res_type->fields['PK_COURSE']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
																<?	$res_type->MoveNext();
																} /* Ticket #1696  */ ?>
															</select>
														</div>
													</div>
													
													<? if($_GET['id'] == ''){ ?>
													<div class="row">
														<div class="col-md-12 text-right">
															<button type="button" onclick="show_tab('courseDetails')" class="btn waves-effect waves-light btn-info"><?=NEXT?></button>
															
															<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course'" ><?=CANCEL?></button>
														</div>
													</div>
													<? } ?>
												</div>
												
												<div class="col-sm-6">
													<div class="row">
														<div class="col-12 col-sm-6 focused">
															<span class="bar"></span> 
															<label for="CAMPUS"><?=CAMPUS?></label>
														</div>
													</div>
													<div class="row">
														<div class="col-sm-12" ><!-- Ticket # 1567 -->
															<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple >
																<? /* Ticket #1696  */
																$res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
																while (!$res_type->EOF) { 
																	$option_label = $res_type->fields['CAMPUS_CODE'];
																	if($res_type->fields['ACTIVE'] == 0)
																		$option_label .= " (Inactive)";
																		
																	$selected = '';
																	$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																	$res = $db->Execute("select PK_COURSE_CAMPUS FROM S_COURSE_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																	if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) //Ticket #849 
																		$selected = 'selected'; ?>
																	<option value="<?=$res_type->fields['PK_CAMPUS']?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
																<?	$res_type->MoveNext();
																} /* Ticket #1696  */ ?>
															</select>
														</div>
													</div>
													
													<!-- Ticket # 1567 -->
													<br />
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ALLOW_ONLINE_ENROLLMENT?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="ALLOW_ONLINE_ENROLLMENT_1" name="ALLOW_ONLINE_ENROLLMENT"  value="1" <? if($ALLOW_ONLINE_ENROLLMENT == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="ALLOW_ONLINE_ENROLLMENT_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="ALLOW_ONLINE_ENROLLMENT_2" name="ALLOW_ONLINE_ENROLLMENT" value="0"  <? if($ALLOW_ONLINE_ENROLLMENT == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="ALLOW_ONLINE_ENROLLMENT_2" ><?=NO ?></label>
														</div>
													</div>
													<? if($_GET['id'] != ''){ ?>
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ACTIVE?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="ACTIVE_1" name="ACTIVE"  value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="ACTIVE_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="ACTIVE_2" name="ACTIVE" value="0"  <? if($ACTIVE == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="ACTIVE_2" ><?=NO ?></label>
														</div>
													</div>
													<? } ?>
													<!-- Ticket # 1567 -->
													
												</div>
											</div>
										</div>
										
										<div class="tab-pane <?=$courseDetails?>" id="courseDetails" role="tabpanel">
											<div class="row">
												<div class="col-sm-6">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="PREP_HOURS" name="PREP_HOURS" value="<?=$PREP_HOURS?>">
																<span class="bar"></span>
																<label for="PREP_HOURS"><?=PREP_HOURS?></label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="MAX_CLASS_SIZE" name="MAX_CLASS_SIZE" value="<?=$MAX_CLASS_SIZE?>">
																<span class="bar"></span>
																<label for="MAX_CLASS_SIZE"><?=MAX_CLASS_SIZE?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_ATTENDANCE_TYPE" name="PK_ATTENDANCE_TYPE" class="form-control" >
																	<option value="" ></option>
																	<? $res_type = $db->Execute("select PK_ATTENDANCE_TYPE,ATTENDANCE_TYPE,DESCRIPTION from M_ATTENDANCE_TYPE WHERE ACTIVE = 1 order by ATTENDANCE_TYPE ASC");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_ATTENDANCE_TYPE'] ?>" <? if($PK_ATTENDANCE_TYPE == $res_type->fields['PK_ATTENDANCE_TYPE']) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_TYPE'].' '.$res_type->fields['DESCRIPTION']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span> 
																<label for="PK_ATTENDENCE_TYPE"><?=ATTENDENCE_TYPE?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<select id="PK_ATTENDANCE_CODE" name="PK_ATTENDANCE_CODE" class="form-control">
																	<option selected></option>
																	<? /* Ticket # 1696 */
																	$res_type = $db->Execute("select PK_ATTENDANCE_CODE, CONCAT(CODE, ' - ', ATTENDANCE_CODE) as ATTENDANCE_CODE, ACTIVE from M_ATTENDANCE_CODE WHERE 1 = 1 order by ACTIVE DESC, ATTENDANCE_CODE ASC");
																	while (!$res_type->EOF) { 
																		$option_label = $res_type->fields['ATTENDANCE_CODE'];
																		if($res_type->fields['ACTIVE'] == 0)
																			$option_label .= " (Inactive)"; ?>
																		<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> <? if($res_type->fields['PK_ATTENDANCE_CODE'] == $PK_ATTENDANCE_CODE) echo "selected"; ?> ><?=$option_label ?></option>
																	<?	$res_type->MoveNext();
																	} /* Ticket # 1696 */ ?>
																</select>
																<span class="bar"></span>
																<label for="ATTENDANCE_CODE"><?=ATTENDANCE_CODE?></label>
															</div>
														</div>
													</div>
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="EXTERNAL_ID" name="EXTERNAL_ID" value="<?=$EXTERNAL_ID?>">
																<span class="bar"></span>
																<label for="EXTERNAL_ID"><?=EXTERNAL_ID?></label>
															</div>
														</div>
													</div>
													<!-- Ticket # 1372 -->
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<input type="text" class="form-control " id="LMS_COURSE_TEMPLATE_ID" name="LMS_COURSE_TEMPLATE_ID" value="<?=$LMS_COURSE_TEMPLATE_ID?>">
																<span class="bar"></span>
																<label for="LMS_COURSE_TEMPLATE_ID"><?=LMS_COURSE_TEMPLATE_ID?></label>
															</div>
														</div>
													</div>
													<!-- Ticket # 1372 -->
												</div>
												<div class="col-sm-6 theme-v-border">
													<div class="row">
														<div class="col-md-12">
															<div class="form-group m-b-40">
																<textarea rows="9" class="form-control " id="FULL_COURSE_DESCRIPTION" name="FULL_COURSE_DESCRIPTION"><?=$FULL_COURSE_DESCRIPTION?></textarea>
																<span class="bar"></span>
																<label for="FULL_COURSE_DESCRIPTION"><?=FULL_COURSE_DESCRIPTION?></label>
															</div>
														</div>
													</div>
													
													<? if($_GET['id'] == ''){ ?>
													<div class="row">
														<div class="col-md-12 text-right">
															<button type="button" onclick="show_tab('course')" class="btn waves-effect waves-light btn-info"><?=PREVIOUS?></button>
															
															<button type="button" onclick="show_tab('courseFeesTab')" class="btn waves-effect waves-light btn-info"><?=NEXT?></button>
															
															<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course'" ><?=CANCEL?></button>
														</div>
													</div>
													<? } ?>
												</div>
											</div>
										</div>
										
										<div class="tab-pane <?=$courseFeesTab?>" id="courseFeesTab" role="tabpanel">	
											<div class="row text-right">
												<div class="col-md-12">
													<button type="button" class="btn btn-primary" onclick="add_course_fees()"><?=ADD?></button>
												</div>
											</div>
											
											<div class="row ">
												<div class="col-md-2">
													<b><?=FEE?></b>
												</div> 
												<div class="col-md-2">
													<b><?=DESCRIPTION?></b>
												</div>
												<div class="col-md-2">
													<b><?=FEE_AMOUNT?></b>
												</div> 
												<div class="col-md-2">
													<b><?=ISBN_10?></b>
												</div>
												<div class="col-md-2">
													<b><?=ISBN_13?></b>
												</div>
												<div class="col-md-1">
													<b><?=SCHOOL_COST?></b>
												</div>
												<div class="col-md-1">
													<b><?=DELETE?></b>
												</div>
											</div>
											<div class="row ">
												<div class="col-md-12">
													<hr />
												</div>
											</div>
											<div id="Course_Fees_div">
												<? $cunt_course_fees = 1; 
												$result1 = $db->Execute("SELECT * FROM S_COURSE_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$_GET[id]' ");
												$reccnt = $result1->RecordCount();
												while (!$result1->EOF) {
													$_REQUEST['PK_COURSE_FEE'] 		= $result1->fields['PK_COURSE_FEE'];
													$_REQUEST['cunt_course_fees']  	= $cunt_course_fees;
													
													include('ajax_course_fees.php');
													
													$cunt_course_fees++;	
													$result1->MoveNext();
												} ?>
											</div>	
											<div class="row">
												<div class="col-md-12">
													<hr />
												</div>
											</div>
											<div class="row ">
												<div class="col-md-2">
													<b>&nbsp;</b>
												</div> 
												<div class="col-md-2">
													<b><?=TOTAL?></b>
												</div>
												<div class="col-md-2" id="TOTAL_FEE_AMT_DIV" style="font-weight:bold;text-align:right;" >
												</div> 
												<div class="col-md-2">
													<b>&nbsp;</b>
												</div>
												<div class="col-md-2">
													<b>&nbsp;</b>
												</div>
												<div class="col-md-1" id="TOTAL_SCHOOL_COST_DIV" style="font-weight:bold;text-align:right;" >
												</div>
											</div>
											
											<? if($_GET['id'] == ''){ ?>
											<div class="row">
												<div class="col-md-12 text-right">
													<button type="button" onclick="show_tab('courseDetails')" class="btn waves-effect waves-light btn-info"><?=PREVIOUS?></button>
													
													<button type="button" onclick="show_tab('gradeBookTab')" class="btn waves-effect waves-light btn-info"><?=NEXT?></button>
													
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course'" ><?=CANCEL?></button>
												</div>
											</div>
											<? } ?>
										</div>
										<div class="tab-pane <?=$gradeBookTab?>" id="gradeBookTab" role="tabpanel" >
											<div class="row text-right">
												<div class="col-md-12">
													<button type="button" class="btn btn-primary" onclick="copy_grade_book()"><?=COPY_FROM?></button><!-- Ticket #1161 --><!-- Ticket #1908 -->
													
													<button type="button" class="btn btn-primary" onclick="add_grade_book()"><?=ADD?></button>
												</div>
											</div>
											
											<div class="row ">
												<div class="col-md-1">
													<b><?=COLUMN?></b>
												</div> 
												<div class="col-md-1">
													<b><?=CODE?></b>
												</div> 
												<div class="col-md-2">
													<b><?=DESCRIPTION?></b>
												</div>
												<div class="col-md-2">
													<b><?=TYPE?></b>
												</div> 
												<div class="col-md-1">
													<b><?=PERIOD?></b>
												</div>
												<div class="col-md-1">
													<b><?=POINTS?></b>
												</div>
												<div class="col-md-1">
													<b><?=WEIGHT?></b>
												</div>
												<div class="col-md-2">
													<b><?=WEIGHT_POINTS?></b>
												</div>
												<div class="col-md-1">
													<b><?=DELETE?></b>
												</div>
											</div>
											<div class="row ">
												<div class="col-md-12">
													<hr />
												</div>
											</div>
											<div id="grade_book_div">
												<? $cunt_grade_book = 1; 
												$result1 = $db->Execute("SELECT * FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE = '$_GET[id]' ORDER BY COLUMN_NO ASC");//Ticket # 1253  
												$reccnt = $result1->RecordCount();
												while (!$result1->EOF) {
													$_REQUEST['PK_COURSE_GRADE_BOOK'] 	= $result1->fields['PK_COURSE_GRADE_BOOK'];
													$_REQUEST['cunt_grade_book']  		= $cunt_grade_book;
													
													include('ajax_grade_book.php');
													
													$cunt_grade_book++;	
													$result1->MoveNext();
												} ?>
											</div>	
											<div class="row">
												<div class="col-md-12">
													<hr />
												</div>
											</div>
											<div class="row ">
												<div class="col-md-6">
													<b>&nbsp;</b>
												</div> 
												<div class="col-md-1">
													<b><?=TOTALS?></b> <!-- Ticket # 1492-->
												</div>
												<div class="col-md-1" id="POINTS_DIV" style="font-weight:bold;">
												</div> 
												<div class="col-md-1" id="WEIGHT_DIV" style="font-weight:bold;">
												</div>
												<div class="col-md-2" id="WEIGHTED_POINTS_DIV" style="font-weight:bold;">
												</div>
											</div>
											
											<? if($_GET['id'] == ''){ ?>
											<div class="row">
												<div class="col-md-12 text-right">
													<button type="button" onclick="show_tab('courseFeesTab')" class="btn waves-effect waves-light btn-info"><?=PREVIOUS?></button>
													
													<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
													
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course'" ><?=CANCEL?></button>
												</div>
											</div>
											<? } ?>
										</div>
										
										<div class="tab-pane <?=$defaultScheduleTab?>" id="defaultScheduleTab" role="tabpanel" >
											<div class="row">
												<div class="col-md-7 text-right">
													<button onclick="create_schedule()" type="button" class="btn waves-effect waves-light btn-info"><?=CREATE_NEW?></button>
												</div>
											</div>
											<div id="default_schedule_div" >
												<? $schedule_count = 0; 
												$res_c_sch_1 = $db->Execute("SELECT PK_COURSE_DEFAULT_SCHEDULE FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
												while (!$res_c_sch_1->EOF) {
													$_REQUEST['PK_COURSE']					= $_GET['id'];
													$_REQUEST['schedule_count'] 			= $schedule_count;
													$_REQUEST['PK_CAMPUS_mul'] 				= $PK_CAMPUS_mul;
													$_REQUEST['PK_COURSE_DEFAULT_SCHEDULE'] = $res_c_sch_1->fields['PK_COURSE_DEFAULT_SCHEDULE']; 
													
													include('ajax_course_schedule.php');
													$schedule_count++;
													
													$res_c_sch_1->MoveNext();
												}?>
											</div>
										</div>
										
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<? if($_GET['id'] != ''){ ?>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group m-b-5"  style="text-align:right" >
								<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
								<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
								
								<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_course'" ><?=CANCEL?></button>
								
								<br /><br />
							</div>
						</div>
					</div>
					<? } ?>
					
					<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
					<input type="hidden" id="current_tab" name="current_tab" value="0" >
					
					<input type="text" id="form_edited" name="form_edited" value="0" style="display:none" />
					
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
	
	<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" id="delete_message" ></div>
					<input type="hidden" id="DELETE_ID" value="0" />
					<input type="hidden" id="DELETE_TYPE" value="0" />
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Ticket #1161 -->
	<div class="modal" id="importGradeBook" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="exampleModalLabel1"><?=COPY_GRADE_BOOK?></h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group" >
						<select id="IMPORT_PK_COURSE" name="IMPORT_PK_COURSE" class="form-control required-entry"  >
							<option value="" >Select Course</option>
							<? $res_type = $db->Execute("SELECT PK_COURSE,COURSE_CODE,COURSE_DESCRIPTION, TRANSCRIPT_CODE from S_COURSE WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by COURSE_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_COURSE'] ?>" ><?=$res_type->fields['COURSE_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="conf_copy_grade_book(1)" class="btn waves-effect waves-light btn-info"><?=COPY?></button>
					<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_copy_grade_book(0)" ><?=CANCEL?></button>
				</div>
			</div>
		</div>
	</div>
	<!-- Ticket #1161 -->
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/jquery.are-you-sure.js"></script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			/*jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			
			$('.timepicker').inputmask(
				"hh:mm t", {
					placeholder: "HH:MM AM/PM", 
					insertMode: false, 
					showMaskOnHover: false,
					hourFormat: 12
				}
			);*/
			duplicate_check()
			$('#form1').areYouSure();
		});
		
		
	</script>
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		var current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		var current_tab = 'generalTab';
	<? } ?>
	jQuery(document).ready(function($) { 
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
		
		$('.timepicker').inputmask(
			"hh:mm t", {
				placeholder: "HH:MM AM/PM", 
				insertMode: false, 
				showMaskOnHover: false,
				hourFormat: 12
			}
		);
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	
	/* Ticket # 1044 */
	function validate_form(val){
		jQuery(document).ready(function($) { 
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			if(document.getElementById('COURSE_CODE').value == '')
				alert('Please Enter Course Code');
			else {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true) {
					$("#form1").removeClass('dirty');
					document.form1.submit();
				}
			}
		});
	}
	
	jQuery(document).ready(function($) { 
		<? if($_GET['id'] != ''){ ?>
		calc_grad_book()
		calc_tot_fee()
		<? } ?>
	});
	
	var requirement_id = '<?=$requirement_id?>';
	function add_requirement(){
		jQuery(document).ready(function($) { 
			var data  = 'requirement_id='+requirement_id;
			var value = $.ajax({
				url: "ajax_program_requirement",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					$('#requirement_div').append(data);
					requirement_id++;
					
					set_form_edited()
				}		
			}).responseText;
		});
	}
	
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'COURSE_FEE')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.FEE?>?';
			else if(type == 'DEF_SCHEDULE')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.TAB_DEFAULT_SCHEDULE?>?';
			
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'COURSE_FEE') {
					$("#COURSE_FEE_"+$("#DELETE_ID").val()).remove();
					calc_tot_fee()
					
					set_form_edited()
				} else if($("#DELETE_TYPE").val() == 'GRADE_BOOK') {
					$("#GRADE_BOOK_"+$("#DELETE_ID").val()).remove();
					calc_grad_book()
					
					set_form_edited()
				} else if($("#DELETE_TYPE").val() == 'DEF_SCHEDULE') {
					$("#default_schedule_div_"+$("#DELETE_ID").val()).remove();
				
					set_form_edited()
				}
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	var cunt_course_fees = '<?=$cunt_course_fees?>';
	function add_course_fees(){
		jQuery(document).ready(function($) {
			var data = 'cunt_course_fees='+cunt_course_fees+'&ACTION=<?=$_GET['act']?>';
			var value = $.ajax({
				url: "ajax_course_fees",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					$("#Course_Fees_div").append(data);
					cunt_course_fees++;
					
					set_form_edited()
				}		
			}).responseText;
		});
	}
	
	var cunt_grade_book = '<?=$cunt_grade_book?>';
	function add_grade_book(){
		jQuery(document).ready(function($) {
			var data = 'cunt_grade_book='+cunt_grade_book+'&ACTION=<?=$_GET['act']?>';
			var value = $.ajax({
				url: "ajax_grade_book",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					$("#grade_book_div").append(data);
					cunt_grade_book++;
					
					set_form_edited()
				}		
			}).responseText;
		});
	}
	
	function calc_tot_fee(){
		var FEE_AMT_TOT 	= 0;
		var SCHOOL_COST_TOT = 0;
		
		var FEE_AMT = document.getElementsByName('FEE_AMT[]')
		for(var i = 0 ; i < FEE_AMT.length ; i++){
			var fee_amt = FEE_AMT[i].value
			if(fee_amt != '') {
				FEE_AMT[i].value = parseFloat(fee_amt).toFixed(2);
				FEE_AMT_TOT 	 = parseFloat(FEE_AMT_TOT) + parseFloat(fee_amt)
			}
		}
		
		var SCHOOL_COST = document.getElementsByName('SCHOOL_COST[]')
		for(var i = 0 ; i < SCHOOL_COST.length ; i++){
			var school_cost = SCHOOL_COST[i].value
			if(school_cost != '') {
				SCHOOL_COST[i].value = parseFloat(school_cost).toFixed(2);
				SCHOOL_COST_TOT 	 = parseFloat(SCHOOL_COST_TOT) + parseFloat(school_cost)
			}
		}
		
		document.getElementById('TOTAL_FEE_AMT_DIV').innerHTML 		= FEE_AMT_TOT.toFixed(2)
		document.getElementById('TOTAL_SCHOOL_COST_DIV').innerHTML 	= SCHOOL_COST_TOT.toFixed(2)
		
	}
	
	/* Ticket # 1492 */
	function calc_weighted_points(id){
		var POINTS = document.getElementById("POINTS_"+id).value
		var WEIGHT = document.getElementById("WEIGHT_"+id).value
		if(POINTS != '' && WEIGHT != '')
			document.getElementById("WEIGHTED_POINTS_"+id).value = (parseFloat(POINTS) * parseFloat(WEIGHT)).toFixed(4);
		else
			document.getElementById("WEIGHTED_POINTS_"+id).value = '';
			
		calc_grad_book()
	}
	
	function calc_grad_book(){
		var POINTS_TOT 			= 0;
		var WEIGHT_TOT 			= 0;
		var WEIGHTED_POINTS_TOT = 0;
		
		var POINTS = document.getElementsByName('POINTS[]')
		for(var i = 0 ; i < POINTS.length ; i++){
			var points = POINTS[i].value
			if(points != '') {
				POINTS[i].value = parseFloat(POINTS[i].value).toFixed(4);
				POINTS_TOT = parseFloat(POINTS_TOT) + parseFloat(points)
			}
		}
		
		var WEIGHT = document.getElementsByName('WEIGHT[]')
		for(var i = 0 ; i < WEIGHT.length ; i++){
			var weight = WEIGHT[i].value
			if(weight != '') {
				WEIGHT[i].value = parseFloat(WEIGHT[i].value).toFixed(4);
				WEIGHT_TOT = parseFloat(WEIGHT_TOT) + parseFloat(weight);
			}
		}
		
		var WEIGHTED_POINTS = document.getElementsByName('WEIGHTED_POINTS[]')
		for(var i = 0 ; i < WEIGHTED_POINTS.length ; i++){
			var weighted_points = WEIGHTED_POINTS[i].value
			if(weighted_points != '')
				WEIGHTED_POINTS_TOT = parseFloat(WEIGHTED_POINTS_TOT) + parseFloat(weighted_points)
		}
		
		document.getElementById('POINTS_DIV').innerHTML 			= POINTS_TOT.toFixed(4)
		document.getElementById('WEIGHT_DIV').innerHTML 			= WEIGHT_TOT.toFixed(4)
		document.getElementById('WEIGHTED_POINTS_DIV').innerHTML 	= WEIGHTED_POINTS_TOT.toFixed(4)
	}
	/* Ticket # 1492 */
	
	/* Ticket # 1044 */
	/* Ticket # 1491 */
	function show_tab(id){
		jQuery(document).ready(function($) {
			var flag = 1
			var error = "";
			if(id == 'courseDetails') {
				if(document.getElementById('COURSE_CODE').value == '') {
					flag = 0
					error += "Please Enter Course Code";
				}
				
				if(document.getElementById('TRANSCRIPT_CODE').value == '') {
					flag = 0
					if(error != '')
						error += "\n";
					error += "Please Enter Transcript Code";
				}
				
				if($("#PK_CAMPUS").val() == ''){
					flag = 0
					if(error != '')
						error += "\n";
						
					error += "Please Select Campus";
				}
			} 
			if(flag == 1)
				$('a[href="#'+id+'"]').tab('show');
			else
				alert(error);
		});
	}
	/* Ticket # 1491 */
	
	function set_form_edited(){
		document.getElementById('form_edited').focus(); 
		document.getElementById('form_edited').value = document.getElementById('form_edited').value + "a";
		document.getElementById('form_edited').focus(); 
		//alert(document.getElementById('form_edited').value)
	}
	
	function get_hour(id){
		var START_TIME = document.getElementById(id+'_START_TIME').value
		var END_TIME   = document.getElementById(id+'_END_TIME').value
		var HOURS	   = '';
		if(START_TIME != '' && END_TIME != ''){
			jQuery(document).ready(function($) { 
				var data  = 'START_TIME='+START_TIME+'&END_TIME='+END_TIME;
				var value = $.ajax({
					url: "ajax_get_hour_from_time",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id+'_HOURS').value = data;
						$("#"+id+"_HOURS").parent().addClass("focused");
						
						calc_total_scheduled_hours(1)
					}		
				}).responseText;
			});
		} else {
		}
		document.getElementById(id+'_HOURS').value = HOURS;
	}
	
	function enable_time(id){
		if(document.getElementById(id).checked == true){
			document.getElementById(id+'_START_TIME').disabled 	= false;
			document.getElementById(id+'_END_TIME').disabled 	= false
			
			document.getElementById(id+'_HOURS').disabled = false;
			document.getElementById(id+'_ROOM').disabled = false;

			if(document.getElementById(id+'_PK_ATTENDANCE_ACTIVITY_TYPE')) {
				document.getElementById(id+'_PK_ATTENDANCE_ACTIVITY_TYPE').disabled = false;
			}
		} else {
			document.getElementById(id+'_START_TIME').disabled 	= true;
			document.getElementById(id+'_END_TIME').disabled 	= true
			document.getElementById(id+'_HOURS').value 			= '';
			document.getElementById(id+'_ROOM').value 			= '';
			
			document.getElementById(id+'_START_TIME').value = '';
			document.getElementById(id+'_END_TIME').value 	= '';
			
			document.getElementById(id+'_HOURS').disabled = true;
			document.getElementById(id+'_ROOM').disabled  = true;
			
			if(document.getElementById(id+'_PK_ATTENDANCE_ACTIVITY_TYPE')) {
				document.getElementById(id+'_PK_ATTENDANCE_ACTIVITY_TYPE').disabled = true;
				document.getElementById(id+'_PK_ATTENDANCE_ACTIVITY_TYPE').value 	= '';
			}
		}
	}
	
	var schedule_count = '<?=$schedule_count?>'
	function create_schedule(){
	
		jQuery(document).ready(function($) {
			var data = 'schedule_count='+schedule_count+'&PK_COURSE=<?=$_GET['id']?>&PK_CAMPUS_mul=<?=$PK_CAMPUS_mul?>';
			var value = $.ajax({
				url: "ajax_course_schedule",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					$("#default_schedule_div").append(data);
					schedule_count++;
					
					$('.timepicker').inputmask(
						"hh:mm t", {
							placeholder: "HH:MM AM/PM", 
							insertMode: false, 
							showMaskOnHover: false,
							hourFormat: 12
						}
					);
				}		
			}).responseText;
		});
	}
	
	
	function change_dup_session(val,id){
		var flag = 1;
		var PK_SESSION = document.getElementsByClassName('sef_sch_session')
		for(var i = 0 ; i < PK_SESSION.length ; i++){
			//alert(PK_SESSION[i].value+'\n'+val+'\n'+PK_SESSION[i].id)
			if(PK_SESSION[i].value == val && PK_SESSION[i].id != 'DEF_SCH_PK_SESSION_'+id ){	
				flag = 0;
			}
		}
		if(flag == 0){ 
			alert('Selected Session is already Assigned');
			document.getElementById('DEF_SCH_PK_SESSION_'+id).value = '';
		}
	}
	
	/* Ticket # 1044  */
	function duplicate_check(){
		jQuery(document).ready(function($) {
			if (document.form1.COURSE_CODE.value  != ""){
				var COURSE_CODE = document.form1.COURSE_CODE.value;
				var data="COURSE_CODE="+COURSE_CODE+'&type=COURSE_CODE&k=<?=$_SESSION['PK_ACCOUNT']?>&id=<?=$_GET['id']?>';
				$.ajax({
					type: "POST",
					url:"../check_duplicate",
					data:data,
					success: function(result1){ 
						if(result1==1){
							document.getElementById('already_exit').style.display 	= "block";
							document.getElementById('COURSE_CODE').value 		= "";
							return false;
						}else{
							document.getElementById('already_exit').style.display = "none";
						}
					}
				});
			}
		});	
	}
	/* Ticket # 1044  */
	
	/* Ticket #1161 */
	function copy_grade_book(){
		jQuery(document).ready(function($) {
			$("#importGradeBook").modal()
		});
	}
	function conf_copy_grade_book(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var sid = $("#IMPORT_PK_COURSE").val()
				if(sid == '') {
					alert("Please Select Course");
				} else {
					var data = 'cid='+document.getElementById('IMPORT_PK_COURSE').value;
					var value = $.ajax({
						url: "ajax_copy_grade_book",	
						type: "POST",
						data: data,		
						async: false,
						cache :false,
						success: function (data) {
							//$("#grade_book_div").append(data);
							document.getElementById('grade_book_div').innerHTML = data
							
							$("#importGradeBook").modal("hide");
							
							var PK_COURSE_GRADE_BOOK = document.getElementsByName('PK_COURSE_GRADE_BOOK[]')
							cunt_grade_book = PK_COURSE_GRADE_BOOK.length + 1;
							cunt_grade_book++;
							set_form_edited()
							
							calc_grad_book()
						}		
					}).responseText;
				}
			} else
				$("#importGradeBook").modal("hide");
		});
	}
	
	
	/* Ticket #1161 */
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 3,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_PREREQUISITE_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PREREQUISITE?>',
			nonSelectedText: '<?=PREREQUISITE?>',
			numberDisplayed: 3,
			nSelectedText: '<?=PREREQUISITE?> selected'
		});
		
		$('#PK_COREQUISITES_COURSE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=COREQUISITES?>',
			nonSelectedText: '<?=COREQUISITES?>',
			numberDisplayed: 3,
			nSelectedText: '<?=COREQUISITES?> selected'
		});
	});
	</script>

</body>

</html>