<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../global/mail.php"); 
require_once("../global/texting.php"); 
require_once("check_access.php");
require_once("function_make_attendance_inactive.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$PK_COURSE_OFFERING  = $_POST['coid'];
$PK_COURSE  		 = $_POST['cid'];
$PK_SESSION 		 = $_POST['sid'];

$res = $db->Execute("SELECT * FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION = '$PK_SESSION' "); 
if($res->RecordCount() > 0) {
	$OFFERING_SCHEDULE['SUNDAY']  	= $res->fields['SUNDAY'];
	$OFFERING_SCHEDULE['MONDAY']  	= $res->fields['MONDAY'];
	$OFFERING_SCHEDULE['TUESDAY']  	= $res->fields['TUESDAY'];
	$OFFERING_SCHEDULE['WEDNESDAY'] = $res->fields['WEDNESDAY'];
	$OFFERING_SCHEDULE['THURSDAY']  = $res->fields['THURSDAY'];
	$OFFERING_SCHEDULE['FRIDAY']  	= $res->fields['FRIDAY'];
	$OFFERING_SCHEDULE['SATURDAY']  = $res->fields['SATURDAY'];

	$OFFERING_SCHEDULE['SUN_ROOM']  = $res->fields['SUN_ROOM'];
	$OFFERING_SCHEDULE['MON_ROOM']  = $res->fields['MON_ROOM'];
	$OFFERING_SCHEDULE['TUE_ROOM']  = $res->fields['TUE_ROOM'];
	$OFFERING_SCHEDULE['WED_ROOM']  = $res->fields['WED_ROOM'];
	$OFFERING_SCHEDULE['THU_ROOM']  = $res->fields['THU_ROOM'];
	$OFFERING_SCHEDULE['FRI_ROOM']  = $res->fields['FRI_ROOM'];
	$OFFERING_SCHEDULE['SAT_ROOM']  = $res->fields['SAT_ROOM'];
	
	$OFFERING_SCHEDULE['SUN_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['MON_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['MON_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['TUE_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['WED_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['WED_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['THU_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['THU_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['FRI_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'];
	$OFFERING_SCHEDULE['SAT_PK_ATTENDANCE_ACTIVITY_TYPE']  = $res->fields['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'];

	$OFFERING_SCHEDULE['SUN_START_TIME'] 	= $res->fields['SUN_START_TIME'];
	$OFFERING_SCHEDULE['SUN_END_TIME']  	= $res->fields['SUN_END_TIME'];
	$OFFERING_SCHEDULE['SUN_HOURS']  		= $res->fields['SUN_HOURS'];

	$OFFERING_SCHEDULE['MON_START_TIME'] 	= $res->fields['MON_START_TIME'];
	$OFFERING_SCHEDULE['MON_END_TIME']  	= $res->fields['MON_END_TIME'];
	$OFFERING_SCHEDULE['MON_HOURS']  		= $res->fields['MON_HOURS'];

	$OFFERING_SCHEDULE['TUE_START_TIME'] 	= $res->fields['TUE_START_TIME'];
	$OFFERING_SCHEDULE['TUE_END_TIME']  	= $res->fields['TUE_END_TIME'];
	$OFFERING_SCHEDULE['TUE_HOURS']  		= $res->fields['TUE_HOURS'];

	$OFFERING_SCHEDULE['WED_START_TIME'] 	= $res->fields['WED_START_TIME'];
	$OFFERING_SCHEDULE['WED_END_TIME']  	= $res->fields['WED_END_TIME'];
	$OFFERING_SCHEDULE['WED_HOURS']  		= $res->fields['WED_HOURS'];

	$OFFERING_SCHEDULE['THU_START_TIME'] 	= $res->fields['THU_START_TIME'];
	$OFFERING_SCHEDULE['THU_END_TIME']  	= $res->fields['THU_END_TIME'];
	$OFFERING_SCHEDULE['THU_HOURS']  		= $res->fields['THU_HOURS'];

	$OFFERING_SCHEDULE['FRI_START_TIME'] 	= $res->fields['FRI_START_TIME'];
	$OFFERING_SCHEDULE['FRI_END_TIME']  	= $res->fields['FRI_END_TIME'];
	$OFFERING_SCHEDULE['FRI_HOURS']  		= $res->fields['FRI_HOURS'];

	$OFFERING_SCHEDULE['SAT_START_TIME'] 	= $res->fields['SAT_START_TIME'];
	$OFFERING_SCHEDULE['SAT_END_TIME']  	= $res->fields['SAT_END_TIME'];
	$OFFERING_SCHEDULE['SAT_HOURS']  		= $res->fields['SAT_HOURS'];
	
	$res = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0) {
		$OFFERING_SCHEDULE['PK_COURSE_OFFERING']  	= $PK_COURSE_OFFERING;
		$OFFERING_SCHEDULE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$OFFERING_SCHEDULE['CREATED_BY']  			= $_SESSION['PK_USER'];
		$OFFERING_SCHEDULE['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'insert');
	} else {
		$PK_COURSE_OFFERING_SCHEDULE = $res->fields['PK_COURSE_OFFERING_SCHEDULE'];
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'update'," PK_COURSE_OFFERING_SCHEDULE = '$PK_COURSE_OFFERING_SCHEDULE' ");
	}
	echo "Imported";
} else
	echo "No Default Schedule Found";

