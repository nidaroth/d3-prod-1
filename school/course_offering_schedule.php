<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2) ){ 
	header("location:../index.php");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$OFFERING_SCHEDULE['START_DATE']  		= $_POST['START_DATE'];
	$OFFERING_SCHEDULE['END_DATE']  		= $_POST['END_DATE'];
	$OFFERING_SCHEDULE['DEF_START_TIME']  	= $_POST['DEF_START_TIME'];
	$OFFERING_SCHEDULE['DEF_END_TIME']  	= $_POST['DEF_END_TIME'];
	$OFFERING_SCHEDULE['DEF_HOURS']  		= $_POST['DEF_HOURS'];
	
	if($OFFERING_SCHEDULE['DEF_START_TIME'] != '' && $OFFERING_SCHEDULE['DEF_END_TIME'] != '') {
		$starttimestamp 					= strtotime($OFFERING_SCHEDULE['DEF_START_TIME']);
		$endtimestamp 						= strtotime($OFFERING_SCHEDULE['DEF_END_TIME']);
		//$OFFERING_SCHEDULE['DEF_HOURS'] 	= abs($endtimestamp - $starttimestamp)/3600;
	}
	
	if($OFFERING_SCHEDULE['START_DATE'] != '')
		$OFFERING_SCHEDULE['START_DATE'] = date("Y-m-d",strtotime($OFFERING_SCHEDULE['START_DATE']));
		
	if($OFFERING_SCHEDULE['END_DATE'] != '')
		$OFFERING_SCHEDULE['END_DATE'] = date("Y-m-d",strtotime($OFFERING_SCHEDULE['END_DATE']));
		
	if($OFFERING_SCHEDULE['DEF_START_TIME'] != '')
		$OFFERING_SCHEDULE['DEF_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['DEF_START_TIME']));
		
	if($OFFERING_SCHEDULE['DEF_END_TIME'] != '')
		$OFFERING_SCHEDULE['DEF_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['DEF_END_TIME']));
		
	if($OFFERING_SCHEDULE['DEF_START_TIME'] == '' || $OFFERING_SCHEDULE['DEF_END_TIME'] == '')
		$OFFERING_SCHEDULE['DEF_HOURS'] = '';
		
	$OFFERING_SCHEDULE['SUNDAY']  	= $_POST['SUNDAY'];
	$OFFERING_SCHEDULE['MONDAY']  	= $_POST['MONDAY'];
	$OFFERING_SCHEDULE['TUESDAY']  	= $_POST['TUESDAY'];
	$OFFERING_SCHEDULE['WEDNESDAY'] = $_POST['WEDNESDAY'];
	$OFFERING_SCHEDULE['THURSDAY']  = $_POST['THURSDAY'];
	$OFFERING_SCHEDULE['FRIDAY']  	= $_POST['FRIDAY'];
	$OFFERING_SCHEDULE['SATURDAY']  = $_POST['SATURDAY'];
	
	if($OFFERING_SCHEDULE['SUNDAY'] == 1) {
		$OFFERING_SCHEDULE['SUN_START_TIME'] 	= $_POST['SUN_START_TIME'];
		$OFFERING_SCHEDULE['SUN_END_TIME']  	= $_POST['SUN_END_TIME'];
		$OFFERING_SCHEDULE['SUN_HOURS']  		= $_POST['SUN_HOURS'];
		
		if($OFFERING_SCHEDULE['SUN_START_TIME'] != '')
			$OFFERING_SCHEDULE['SUN_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SUN_START_TIME']));
			
		if($OFFERING_SCHEDULE['SUN_END_TIME'] != '')
			$OFFERING_SCHEDULE['SUN_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SUN_END_TIME']));
			
		if($OFFERING_SCHEDULE['SUN_START_TIME'] == '' || $OFFERING_SCHEDULE['SUN_END_TIME'] == '')
			$OFFERING_SCHEDULE['SUN_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['SUN_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['SUN_END_TIME']  	= '';
		$OFFERING_SCHEDULE['SUN_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['MONDAY'] == 1) {
		$OFFERING_SCHEDULE['MON_START_TIME'] 	= $_POST['MON_START_TIME'];
		$OFFERING_SCHEDULE['MON_END_TIME']  	= $_POST['MON_END_TIME'];
		$OFFERING_SCHEDULE['MON_HOURS']  		= $_POST['MON_HOURS'];
		
		if($OFFERING_SCHEDULE['MON_START_TIME'] != '')
			$OFFERING_SCHEDULE['MON_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['MON_START_TIME']));
			
		if($OFFERING_SCHEDULE['MON_END_TIME'] != '')
			$OFFERING_SCHEDULE['MON_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['MON_END_TIME']));
			
		if($OFFERING_SCHEDULE['MON_START_TIME'] == '' || $OFFERING_SCHEDULE['MON_END_TIME'] == '')
			$OFFERING_SCHEDULE['MON_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['MON_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['MON_END_TIME']  	= '';
		$OFFERING_SCHEDULE['MON_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['TUESDAY'] == 1) {
		$OFFERING_SCHEDULE['TUE_START_TIME'] 	= $_POST['TUE_START_TIME'];
		$OFFERING_SCHEDULE['TUE_END_TIME']  	= $_POST['TUE_END_TIME'];
		$OFFERING_SCHEDULE['TUE_HOURS']  		= $_POST['TUE_HOURS'];
		
		if($OFFERING_SCHEDULE['TUE_START_TIME'] != '')
			$OFFERING_SCHEDULE['TUE_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['TUE_START_TIME']));
			
		if($OFFERING_SCHEDULE['TUE_END_TIME'] != '')
			$OFFERING_SCHEDULE['TUE_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['TUE_END_TIME']));
			
		if($OFFERING_SCHEDULE['TUE_START_TIME'] == '' || $OFFERING_SCHEDULE['TUE_END_TIME'] == '')
			$OFFERING_SCHEDULE['TUE_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['TUE_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['TUE_END_TIME']  	= '';
		$OFFERING_SCHEDULE['TUE_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['WEDNESDAY'] == 1) {
		$OFFERING_SCHEDULE['WED_START_TIME'] 	= $_POST['WED_START_TIME'];
		$OFFERING_SCHEDULE['WED_END_TIME']  	= $_POST['WED_END_TIME'];
		$OFFERING_SCHEDULE['WED_HOURS']  		= $_POST['WED_HOURS'];
		
		if($OFFERING_SCHEDULE['WED_START_TIME'] != '')
			$OFFERING_SCHEDULE['WED_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['WED_START_TIME']));
			
		if($OFFERING_SCHEDULE['WED_END_TIME'] != '')
			$OFFERING_SCHEDULE['WED_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['WED_END_TIME']));
			
		if($OFFERING_SCHEDULE['WED_START_TIME'] == '' || $OFFERING_SCHEDULE['WED_END_TIME'] == '')
			$OFFERING_SCHEDULE['WED_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['WED_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['WED_END_TIME']  	= '';
		$OFFERING_SCHEDULE['WED_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['THURSDAY'] == 1) {
		$OFFERING_SCHEDULE['THU_START_TIME'] 	= $_POST['THU_START_TIME'];
		$OFFERING_SCHEDULE['THU_END_TIME']  	= $_POST['THU_END_TIME'];
		$OFFERING_SCHEDULE['THU_HOURS']  		= $_POST['THU_HOURS'];
		
		if($OFFERING_SCHEDULE['THU_START_TIME'] != '')
			$OFFERING_SCHEDULE['THU_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['THU_START_TIME']));
			
		if($OFFERING_SCHEDULE['THU_END_TIME'] != '')
			$OFFERING_SCHEDULE['THU_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['THU_END_TIME']));
			
		if($OFFERING_SCHEDULE['THU_START_TIME'] == '' || $OFFERING_SCHEDULE['THU_END_TIME'] == '')
			$OFFERING_SCHEDULE['THU_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['THU_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['THU_END_TIME']  	= '';
		$OFFERING_SCHEDULE['THU_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['FRIDAY'] == 1) {
		$OFFERING_SCHEDULE['FRI_START_TIME'] 	= $_POST['FRI_START_TIME'];
		$OFFERING_SCHEDULE['FRI_END_TIME']  	= $_POST['FRI_END_TIME'];
		$OFFERING_SCHEDULE['FRI_HOURS']  		= $_POST['FRI_HOURS'];
		
		if($OFFERING_SCHEDULE['FRI_START_TIME'] != '')
			$OFFERING_SCHEDULE['FRI_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['FRI_START_TIME']));
			
		if($OFFERING_SCHEDULE['FRI_END_TIME'] != '')
			$OFFERING_SCHEDULE['FRI_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['FRI_END_TIME']));
			
		if($OFFERING_SCHEDULE['FRI_START_TIME'] == '' || $OFFERING_SCHEDULE['FRI_END_TIME'] == '')
			$OFFERING_SCHEDULE['FRI_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['FRI_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['FRI_END_TIME']  	= '';
		$OFFERING_SCHEDULE['FRI_HOURS']  		= '';
	}
	
	if($OFFERING_SCHEDULE['SATURDAY'] == 1) {
		$OFFERING_SCHEDULE['SAT_START_TIME'] 	= $_POST['SAT_START_TIME'];
		$OFFERING_SCHEDULE['SAT_END_TIME']  	= $_POST['SAT_END_TIME'];
		$OFFERING_SCHEDULE['SAT_HOURS']  		= $_POST['SAT_HOURS'];
		
		if($OFFERING_SCHEDULE['SAT_START_TIME'] != '')
			$OFFERING_SCHEDULE['SAT_START_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SAT_START_TIME']));
			
		if($OFFERING_SCHEDULE['SAT_END_TIME'] != '')
			$OFFERING_SCHEDULE['SAT_END_TIME'] = date("H:i:s",strtotime($OFFERING_SCHEDULE['SAT_END_TIME']));
			
		if($OFFERING_SCHEDULE['SAT_START_TIME'] == '' || $OFFERING_SCHEDULE['SAT_END_TIME'] == '')
			$OFFERING_SCHEDULE['SAT_HOURS'] = '';
	} else {
		$OFFERING_SCHEDULE['SAT_START_TIME'] 	= '';
		$OFFERING_SCHEDULE['SAT_END_TIME']  	= '';
		$OFFERING_SCHEDULE['SAT_HOURS']  		= '';
	}
	
	$OFFERING_SCHEDULE['SCHEDULE_ON_HOLIDAY']  		= $_POST['SCHEDULE_ON_HOLIDAY'];
	$OFFERING_SCHEDULE['OVERWRITE_SCHEDULE_DATE'] 	= $_POST['OVERWRITE_SCHEDULE_DATE'];

	$res = $db->Execute("SELECT PK_COURSE FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$_GET[cid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($_GET['id'] == ''){
		$OFFERING_SCHEDULE['PK_COURSE_OFFERING']  	= $_GET['cid'];
		$OFFERING_SCHEDULE['PK_COURSE']  			= $res->fields['PK_COURSE'];
		$OFFERING_SCHEDULE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$OFFERING_SCHEDULE['CREATED_BY']  			= $_SESSION['PK_USER'];
		$OFFERING_SCHEDULE['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'insert');
		$PK_COURSE_OFFERING_SCHEDULE = $db->insert_ID();
	} else {
		$PK_COURSE_OFFERING_SCHEDULE 	  = $_GET['id'];
		$OFFERING_SCHEDULE['EDITED_BY']   = $_SESSION['PK_USER'];
		$OFFERING_SCHEDULE['EDITED_ON']   = date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'update'," PK_COURSE_OFFERING_SCHEDULE = '$PK_COURSE_OFFERING_SCHEDULE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	$i = 0;
	foreach($_POST['SCHEDULE_HID'] as $HID){
		$SCHEDULE_DETAIL = array();
		
		$SCHEDULE_DETAIL['SCHEDULE_DATE']  	= $_POST['SCHEDULE_DATE'][$i];
		$SCHEDULE_DETAIL['START_TIME']  	= $_POST['SCHEDULE_START_TIME'][$i];
		$SCHEDULE_DETAIL['END_TIME']  		= $_POST['SCHEDULE_END_TIME'][$i];
		$SCHEDULE_DETAIL['HOURS']  			= $_POST['SCHEDULE_HOURS'][$i];
		$SCHEDULE_DETAIL['PK_CAMPUS_ROOM']  = $_POST['SCHEDULE_PK_CAMPUS_ROOM'][$i];
		$SCHEDULE_DETAIL['COMPLETED']  		= $_POST['COMPLETED_'.$HID];
		
		if($SCHEDULE_DETAIL['HOURS'] == '' && ($SCHEDULE_DETAIL['START_TIME'] != '' && $SCHEDULE_DETAIL['END_TIME'] != '')) {
			$starttimestamp 			= strtotime($SCHEDULE_DETAIL['START_TIME']);
			$endtimestamp 				= strtotime($SCHEDULE_DETAIL['END_TIME']);
			$SCHEDULE_DETAIL['HOURS'] 	= abs($endtimestamp - $starttimestamp)/3600;
		}
		
		if($SCHEDULE_DETAIL['SCHEDULE_DATE'] != '')
			$SCHEDULE_DETAIL['SCHEDULE_DATE'] = date("Y-m-d",strtotime($SCHEDULE_DETAIL['SCHEDULE_DATE']));
			
		if($SCHEDULE_DETAIL['START_TIME'] != '')
			$SCHEDULE_DETAIL['START_TIME'] = date("H:i:s",strtotime($SCHEDULE_DETAIL['START_TIME']));
			
		if($SCHEDULE_DETAIL['END_TIME'] != '')
			$SCHEDULE_DETAIL['END_TIME'] = date("H:i:s",strtotime($SCHEDULE_DETAIL['END_TIME']));
		
		if($_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'][$i] == ''){
			$SCHEDULE_DETAIL['PK_COURSE_OFFERING_SCHEDULE'] = $PK_COURSE_OFFERING_SCHEDULE;
			$SCHEDULE_DETAIL['PK_COURSE_OFFERING']  		= $_GET['cid'];
			$SCHEDULE_DETAIL['PK_COURSE']  					= $res->fields['PK_COURSE'];
			$SCHEDULE_DETAIL['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
			$SCHEDULE_DETAIL['CREATED_BY']  				= $_SESSION['PK_USER'];
			$SCHEDULE_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_SCHEDULE_DETAIL', $SCHEDULE_DETAIL, 'insert');
			
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = $db->insert_ID();
		} else {
			$SCHEDULE_DETAIL['EDITED_BY']	= $_SESSION['PK_USER'];
			$SCHEDULE_DETAIL['EDITED_ON']	= date("Y-m-d H:i");
			db_perform('S_COURSE_OFFERING_SCHEDULE_DETAIL', $SCHEDULE_DETAIL, 'update'," PK_COURSE_OFFERING_SCHEDULE_DETAIL = ".$_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'][$i]." AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'][$i];
		}

		$i++;
	}
	
	$cond = "";
	if(!empty($PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR))
		$cond = " AND PK_COURSE_OFFERING_SCHEDULE_DETAIL NOT IN (".implode(",",$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR).") ";
	
	$db->Execute("DELETE FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING = '$_GET[cid]' $cond "); 
	
	if($_POST['SAVE_CONTINUE'] == 1 )
		header("location:course_offering_schedule.php?cid=".$_GET['cid']."&id=".$PK_COURSE_OFFERING_SCHEDULE);
	else
		header("location:course_offering.php?id=".$_GET['cid']."&tab=scheduleTab");
	exit;
}

if($_GET['id'] == ''){
	$START_DATE 	= '';
	$END_DATE 		= '';
	$DEF_START_TIME = '';
	$DEF_END_TIME 	= '';
	$DEF_HOURS 		= '';
	
	$SUNDAY 		= '';
	$SUN_START_TIME = '';
	$SUN_END_TIME 	= '';
	$SUN_HOURS 		= '';
	
	$MONDAY 		= '';
	$MON_START_TIME = '';
	$MON_END_TIME 	= '';
	$MON_HOURS 		= '';
	
	$TUESDAY 		= '';
	$TUE_START_TIME = '';
	$TUE_END_TIME 	= '';
	$TUE_HOURS 		= '';
	
	$WEDNESDAY 		= '';
	$WED_START_TIME = '';
	$WED_END_TIME 	= '';
	$WED_HOURS 		= '';
	
	$THURSDAY 		= '';
	$THU_START_TIME = '';
	$THU_END_TIME 	= '';
	$THU_HOURS 		= '';
	
	$FRIDAY 		= '';
	$FRI_START_TIME = '';
	$FRI_END_TIME 	= '';
	$FRI_HOURS 		= '';
	
	$SATURDAY 		= '';
	$SAT_START_TIME = '';
	$SAT_END_TIME 	= '';
	$SAT_HOURS 		= '';
	
	$SCHEDULE_ON_HOLIDAY 		= '';
	$OVERWRITE_SCHEDULE_DATE 	= '';
	
	$res = $db->Execute("SELECT PK_COURSE,PK_CAMPUS,PK_CAMPUS_ROOM FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$_GET[cid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_CAMPUS 		= $res->fields['PK_CAMPUS'];
	$PK_COURSE 		= $res->fields['PK_COURSE'];
	$PK_CAMPUS_ROOM = $res->fields['PK_CAMPUS_ROOM'];
	
} else {

	$res = $db->Execute("SELECT  * FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING_SCHEDULE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_course_offering.php");
		exit;
	}
	
	$PK_COURSE_OFFERING	= $res->fields['PK_COURSE_OFFERING'];
	$PK_COURSE 			= $res->fields['PK_COURSE'];
	$PK_CAMPUS 			= $res->fields['PK_CAMPUS'];
	$START_DATE 		= $res->fields['START_DATE'];
	$END_DATE 			= $res->fields['END_DATE'];
	$DEF_START_TIME 	= $res->fields['DEF_START_TIME'];
	$DEF_END_TIME 		= $res->fields['DEF_END_TIME'];
	$DEF_HOURS 			= $res->fields['DEF_HOURS'];
	
	if($START_DATE != '0000-00-00')
		$START_DATE = date("m/d/Y",strtotime($START_DATE));
		
	if($END_DATE != '0000-00-00')
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
	
	if($DEF_START_TIME != '00:00:00')
		$DEF_START_TIME = date("h:i A",strtotime($DEF_START_TIME));
			
	if($DEF_END_TIME != '00:00:00')
		$DEF_END_TIME = date("h:i A",strtotime($DEF_END_TIME));
	
	$SUNDAY	 	= $res->fields['SUNDAY'];
	$MONDAY 	= $res->fields['MONDAY'];
	$TUESDAY 	= $res->fields['TUESDAY'];
	$WEDNESDAY 	= $res->fields['WEDNESDAY'];
	$THURSDAY 	= $res->fields['THURSDAY'];
	$FRIDAY 	= $res->fields['FRIDAY'];
	$SATURDAY 	= $res->fields['SATURDAY'];
	
	if($SUNDAY == 1) {
		$SUN_START_TIME = $res->fields['SUN_START_TIME'];
		$SUN_END_TIME 	= $res->fields['SUN_END_TIME'];
		$SUN_HOURS 		= $res->fields['SUN_HOURS'];
		
		if($SUN_START_TIME != '00:00:00')
			$SUN_START_TIME = date("h:i A",strtotime($SUN_START_TIME));
			
		if($SUN_END_TIME != '00:00:00')
			$SUN_END_TIME = date("h:i A",strtotime($SUN_END_TIME));
	} else {
		$SUN_START_TIME = '';
		$SUN_END_TIME 	= '';
		$SUN_HOURS 		= '';
	}
	
	if($MONDAY == 1) {
		$MON_START_TIME = $res->fields['MON_START_TIME'];
		$MON_END_TIME 	= $res->fields['MON_END_TIME'];
		$MON_HOURS 		= $res->fields['MON_HOURS'];
		
		if($MON_START_TIME != '00:00:00')
			$MON_START_TIME = date("h:i A",strtotime($MON_START_TIME));
			
		if($MON_END_TIME != '00:00:00')
			$MON_END_TIME = date("h:i A",strtotime($MON_END_TIME));
	} else {
		$MON_START_TIME = '';
		$MON_END_TIME 	= '';
		$MON_HOURS 		= '';
	}
	
	if($TUESDAY == 1) {
		$TUE_START_TIME = $res->fields['TUE_START_TIME'];
		$TUE_END_TIME 	= $res->fields['TUE_END_TIME'];
		$TUE_HOURS 		= $res->fields['TUE_HOURS'];
		
		if($TUE_START_TIME != '00:00:00')
			$TUE_START_TIME = date("h:i A",strtotime($TUE_START_TIME));
			
		if($TUE_END_TIME != '00:00:00')
			$TUE_END_TIME = date("h:i A",strtotime($TUE_END_TIME));
	} else {
		$TUE_START_TIME = '';
		$TUE_END_TIME 	= '';
		$TUE_HOURS 		= '';
	}
	
	if($WEDNESDAY == 1) {
		$WED_START_TIME = $res->fields['WED_START_TIME'];
		$WED_END_TIME 	= $res->fields['WED_END_TIME'];
		$WED_HOURS 		= $res->fields['WED_HOURS'];
		
		if($WED_START_TIME != '00:00:00')
			$WED_START_TIME = date("h:i A",strtotime($WED_START_TIME));
			
		if($WED_END_TIME != '00:00:00')
			$WED_END_TIME = date("h:i A",strtotime($WED_END_TIME));
	} else {
		$WED_START_TIME = '';
		$WED_END_TIME 	= '';
		$WED_HOURS 		= '';
	}
	
	if($THURSDAY == 1) {
		$THU_START_TIME = $res->fields['THU_START_TIME'];
		$THU_END_TIME 	= $res->fields['THU_END_TIME'];
		$THU_HOURS 		= $res->fields['THU_HOURS'];
		
		if($THU_START_TIME != '00:00:00')
			$THU_START_TIME = date("h:i A",strtotime($THU_START_TIME));
			
		if($THU_END_TIME != '00:00:00')
			$THU_END_TIME = date("h:i A",strtotime($THU_END_TIME));
	} else {
		$THU_START_TIME = '';
		$THU_END_TIME 	= '';
		$THU_HOURS 		= '';
	}

	if($FRIDAY == 1) {
		$FRI_START_TIME = $res->fields['FRI_START_TIME'];
		$FRI_END_TIME 	= $res->fields['FRI_END_TIME'];
		$FRI_HOURS 		= $res->fields['FRI_HOURS'];
		
		if($FRI_START_TIME != '00:00:00')
			$FRI_START_TIME = date("h:i A",strtotime($FRI_START_TIME));
			
		if($FRI_END_TIME != '00:00:00')
			$FRI_END_TIME = date("h:i A",strtotime($FRI_END_TIME));
	} else {
		$FRI_START_TIME = '';
		$FRI_END_TIME 	= '';
		$FRI_HOURS 		= '';
	}
	
	if($SATURDAY == 1) {
		$SAT_START_TIME = $res->fields['SAT_START_TIME'];
		$SAT_END_TIME 	= $res->fields['SAT_END_TIME'];
		$SAT_HOURS 		= $res->fields['SAT_HOURS'];
		
		if($SAT_START_TIME != '00:00:00')
			$SAT_START_TIME = date("h:i A",strtotime($SAT_START_TIME));
			
		if($SAT_END_TIME != '00:00:00')
			$SAT_END_TIME = date("h:i A",strtotime($SAT_END_TIME));
	} else {
		$SAT_START_TIME = '';
		$SAT_END_TIME 	= '';
		$SAT_HOURS 		= '';
	}
	
	
	$SCHEDULE_ON_HOLIDAY 		= $res->fields['SCHEDULE_ON_HOLIDAY'];
	$OVERWRITE_SCHEDULE_DATE 	= $res->fields['OVERWRITE_SCHEDULE_DATE'];
	
	$res = $db->Execute("SELECT PK_COURSE,PK_CAMPUS,PK_CAMPUS_ROOM FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$PK_CAMPUS 		= $res->fields['PK_CAMPUS'];
	$PK_CAMPUS_ROOM = $res->fields['PK_CAMPUS_ROOM'];
}
$res = $db->Execute("SELECT HOURS FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$COURSE_HOUR = $res->fields['HOURS'];
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
	<link href="../backend_assets/node_modules/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet">
	<link href="../backend_assets/dist/css/pages/user-card.css" rel="stylesheet">
	<title><?=COURSE_OFFERING_SCHEDULE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=COURSE_OFFERING_SCHEDULE_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels mt-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
                                <div class="p-20">
									<div class="row">
                                		<div class="col-md-5">
											<div class="row">
												<div class="col-md-2"><b><?=DATES?></b></div>
												<div class="col-md-3 form-group">
	                                                <input type="text" id="START_DATE" name="START_DATE" value="<?=$START_DATE?>" class="form-control date required-entry">
	                                                <span class="bar"></span> 
                                                    <label for="START_DATE"><?=START_DATE?></label>
	                                            </div>
	                                            <div class="col-md-3 d-flex align-items-center justify-content-center form-group">to</div>
	                                            <div class="col-md-3 form-group">
	                                                <input type="text" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" class="form-control date required-entry">
	                                                <span class="bar"></span> 
                                                    <label for="END_DATE"><?=END_DATE?></label>
	                                            </div>
											</div>
											
											<div class="row">
												<div class="col-md-2"><b><?=DEFAULT_TIMES?></b></div>
												<div class="col-md-3 form-group">
	                                                <input type="text" id="DEF_START_TIME" name="DEF_START_TIME" value="<?=$DEF_START_TIME?>" class="form-control timepicker" onchange="get_hour('DEF')" >
	                                                <span class="bar"></span> 
                                                    <label for="DEF_START_TIME"><?=START_TIME?></label>
	                                            </div>
	                                           
	                                            <div class="col-md-3 form-group">
	                                                <input type="text" id="DEF_END_TIME" name="DEF_END_TIME" value="<?=$DEF_END_TIME?>" class="form-control timepicker" onchange="get_hour('DEF')" >
	                                                <span class="bar"></span> 
                                                    <label for="DEF_END_TIME"><?=END_TIME?></label>
	                                            </div>
												
												<div class="col-md-3 form-group">
													<input type="text" id="DEF_HOURS" name="DEF_HOURS" value="<?=$DEF_HOURS?>" class="form-control" >
	                                                <span class="bar"></span> 
                                                    <label for="DEF_HOURS"><?=HOUR?></label>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-5"></div>
												<div class="col-md-6 form-group">
													<button type="button" onclick="apply_default()" class="btn waves-effect waves-light btn-info"><?=APPLY_DEFAULT?></button>
													<button type="button" onclick="reset_blank()" class="btn waves-effect waves-light btn-info"><?=RESET_BLANK?></button>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="SUN" name="SUNDAY" value="1" <? if($SUNDAY == 1) echo "checked"; ?> onclick="enable_time('SUN')" >
	                                                <label class="custom-control-label" for="SUN"><?=SUNDAY?></label>
												</div>
												
												<? if($SUNDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-3 form-group">
													<input type="text" id="SUN_START_TIME" name="SUN_START_TIME" value="<?=$SUN_START_TIME?>" class="form-control timepicker" onchange="get_hour('SUN')" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="SUN_END_TIME" name="SUN_END_TIME" value="<?=$SUN_END_TIME?>" class="form-control timepicker" onchange="get_hour('SUN')" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="SUN_HOURS" name="SUN_HOURS" value="<?=$SUN_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($MONDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="MON" name="MONDAY" value="1" <? if($MONDAY == 1) echo "checked"; ?> onclick="enable_time('MON')" >
	                                                <label class="custom-control-label" for="MON"><?=MONDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="MON_START_TIME" name="MON_START_TIME" value="<?=$MON_START_TIME?>" class="form-control timepicker" onchange="get_hour('MON')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="MON_END_TIME" name="MON_END_TIME" value="<?=$MON_END_TIME?>" class="form-control timepicker" onchange="get_hour('MON')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="MON_HOURS" name="MON_HOURS" value="<?=$MON_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($TUESDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="TUE" name="TUESDAY" value="1" <? if($TUESDAY == 1) echo "checked"; ?> onclick="enable_time('TUE')" >
	                                                <label class="custom-control-label" for="TUE"><?=TUESDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="TUE_START_TIME" name="TUE_START_TIME" value="<?=$TUE_START_TIME?>" class="form-control timepicker" onchange="get_hour('TUE')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="TUE_END_TIME" name="TUE_END_TIME" value="<?=$TUE_END_TIME?>" class="form-control timepicker" onchange="get_hour('TUE')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="TUE_HOURS" name="TUE_HOURS" value="<?=$TUE_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($WEDNESDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="WED" name="WEDNESDAY" value="1" <? if($WEDNESDAY == 1) echo "checked"; ?> onclick="enable_time('WED')" >
	                                                <label class="custom-control-label" for="WED"><?=WEDNESDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="WED_START_TIME" name="WED_START_TIME" value="<?=$WED_START_TIME?>" class="form-control timepicker" onchange="get_hour('WED')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="WED_END_TIME" name="WED_END_TIME" value="<?=$WED_END_TIME?>" class="form-control timepicker" onchange="get_hour('WED')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="WED_HOURS" name="WED_HOURS" value="<?=$WED_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($THURSDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="THU" name="THURSDAY" value="1" <? if($THURSDAY == 1) echo "checked"; ?> onclick="enable_time('THU')" >
	                                                <label class="custom-control-label" for="THU"><?=THURSDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="THU_START_TIME" name="THU_START_TIME" value="<?=$THU_START_TIME?>" class="form-control timepicker" onchange="get_hour('THU')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="THU_END_TIME" name="THU_END_TIME" value="<?=$THU_END_TIME?>" class="form-control timepicker" onchange="get_hour('THU')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="THU_HOURS" name="THU_HOURS" value="<?=$THU_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($FRIDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="FRI" name="FRIDAY" value="1" <? if($FRIDAY == 1) echo "checked"; ?> onclick="enable_time('FRI')" >
	                                                <label class="custom-control-label" for="FRI"><?=FRIDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="FRI_START_TIME" name="FRI_START_TIME" value="<?=$FRI_START_TIME?>" class="form-control timepicker" onchange="get_hour('FRI')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="FRI_END_TIME" name="FRI_END_TIME" value="<?=$FRI_END_TIME?>" class="form-control timepicker" onchange="get_hour('FRI')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="FRI_HOURS" name="FRI_HOURS" value="<?=$FRI_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<? if($SATURDAY == 1) $disable=""; else  $disable="disabled"; ?>
												<div class="col-md-2 form-group custom-control custom-checkbox form-group">
													<input type="checkbox" class="custom-control-input" id="SAT" name="SATURDAY" value="1" <? if($SATURDAY == 1) echo "checked"; ?> onclick="enable_time('SAT')" >
	                                                <label class="custom-control-label" for="SAT"><?=SATURDAY?></label>
												</div>
												
												<div class="col-3 form-group">
													<input type="text" id="SAT_START_TIME" name="SAT_START_TIME" value="<?=$SAT_START_TIME?>" class="form-control timepicker" onchange="get_hour('SAT')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="SAT_END_TIME" name="SAT_END_TIME" value="<?=$SAT_END_TIME?>" class="form-control timepicker" onchange="get_hour('SAT')" <?=$disable ?>  >
	                                                <span class="bar"></span> 
												</div>
												<div class="col-3 form-group">
													<input type="text" id="SAT_HOURS" name="SAT_HOURS" value="<?=$SAT_HOURS?>" class="form-control" <?=$disable ?> >
	                                                <span class="bar"></span> 
												</div>
											</div>
											
											<div class="row">
												<div class="col-6 form-group">
													<div class="row">
														<div class="col-12 form-group custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="SCHEDULE_ON_HOLIDAY" name="SCHEDULE_ON_HOLIDAY" <? if($SCHEDULE_ON_HOLIDAY == 1) echo "checked"; ?> value="1" >
															<label class="custom-control-label" for="SCHEDULE_ON_HOLIDAY"><?=SCHEDULE_ON_HOLIDAY?></label>
														</div>
													</div>
													
													<div class="row">
														<div class="col-12 form-group custom-control custom-checkbox form-group">
															<input type="checkbox" class="custom-control-input" id="OVERWRITE_SCHEDULE_DATE" name="OVERWRITE_SCHEDULE_DATE" <? if($OVERWRITE_SCHEDULE_DATE == 1) echo "checked"; ?> value="1" >
															<label class="custom-control-label" for="OVERWRITE_SCHEDULE_DATE"><?=OVERWRITE_SCHEDULE_DATE?></label>
														</div>
													</div>
												</div>
												<div class="col-6 form-group ">
													<br />
													<button type="button" onclick="build_schedule(1)" class="btn waves-effect waves-light btn-info"><?=BUILD_SCHEDULE?></button>
												</div>
											</div>
											
										</div>
										<div class="col-md-7 theme-v-border">
											<div class="row">
												<div class="col-md-2"><b><?=DATE?></b></div>
												<div class="col-md-1" style="max-width:7%" ><b><?=DD?></b></div>
												<div class="col-md-2" style="max-width:13.667%" ><b><?=START_TIME?></b></div>
												<div class="col-md-2" style="max-width:13.667%" ><b><?=END_TIME?></b></div>
												<div class="col-md-1"><b><?=HOUR?></b></div>
												<div class="col-md-2"><b><?=ROOM?></b></div>
												<div class="col-md-1"><b><?=COMPLETED?></b></div>
												<div class="col-md-1" style="padding:0" ><b><?=OPTIONS?></b></div>
											</div>
											
											<div id="schedule_div" style="overflow-y: auto;height: 695px;overflow-x: hidden;" >
												<? $count = 0;
												$TOTAL_SCHEDULED_HOURS = 0;
												if($_GET['id'] != ''){ 
													/*$res_type = $db->Execute("select PK_COURSE_OFFERING_SCHEDULE_DETAIL from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSIOn[PK_ACCOUNT]' ");
													while (!$res_type->EOF) {
														$_REQUEST['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] = $res_type->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
														$_REQUEST['count'] 								= $count;
														$_REQUEST['BUILD'] 								= 0;
														
														$count++;
														
														$res_type->MoveNext();
													}*/
													
													$_REQUEST['PK_COURSE_OFFERING_SCHEDULE'] = $_GET['id'];
													$_REQUEST['BUILD'] 						 = 0;
													include("ajax_build_course_offering_schedule.php");
													
													$res_type = $db->Execute("select SUM(HOURS) AS TOTAL_SCHEDULED_HOURS from S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
													$TOTAL_SCHEDULED_HOURS = $res_type->fields['TOTAL_SCHEDULED_HOURS'];
												} ?>
											</div>
											
											<div class="row">
												<input type="hidden" name="COURSE_HOUR" id="COURSE_HOUR" value="<?=$COURSE_HOUR?>" >
												<div class="col-md-4"><b><?=COURSE_HOURS?>: <?=$COURSE_HOUR?></b></div>
												<div class="col-md-4" ><b><?=TOTAL_SCHEDULED_HOURS?>: <span id="TOTAL_SCHEDULED_HOURS_SPAN"><?=$TOTAL_SCHEDULED_HOURS?></span></b></div>
											</div>
											<br />
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-sm-6 form-group">
										</div>
										<div class="col-sm-6 form-group">
											<button type="button" onclick="validate_form(1)" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
											
											<button type="button" onclick="validate_form(0)" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
											
											<button type="button" onclick="window.location.href='course_offering.php?id=<?=$_GET['cid']?>&tab=scheduleTab'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
											
											 <input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
										</div>
									</div>
								
                            	</div>
                            </form>                           
                        </div>
					</div>
				</div>
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
			
        </div>
        <? require_once("footer.php"); ?>
		
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
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
		);
		
		//calc_total_scheduled_hours()
	});
	
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function validate_form(val){
		document.getElementById("SAVE_CONTINUE").value  = val;
		
		var valid = new Validation('form1', {onSubmit:false});
		var result = valid.validate();
		if(result == true)
			document.form1.submit();
	}
	
	function apply_default(){
		var DEF_START_TIME = document.getElementById('DEF_START_TIME').value
		var DEF_END_TIME   = document.getElementById('DEF_END_TIME').value
		var DEF_HOURS      = document.getElementById('DEF_HOURS').value
		
		if(DEF_START_TIME != '' || DEF_END_TIME != ''){
			document.getElementById('SUN').checked 	= true;
			document.getElementById('MON').checked 	= true;
			document.getElementById('TUE').checked 	= true;
			document.getElementById('WED').checked 	= true;
			document.getElementById('THU').checked 	= true;
			document.getElementById('FRI').checked 	= true;
			document.getElementById('SAT').checked 	= true;
		}
		
		if(DEF_START_TIME != ''){
			document.getElementById('SUN_START_TIME').value = DEF_START_TIME;
			document.getElementById('MON_START_TIME').value = DEF_START_TIME;
			document.getElementById('TUE_START_TIME').value = DEF_START_TIME;
			document.getElementById('WED_START_TIME').value = DEF_START_TIME;
			document.getElementById('THU_START_TIME').value = DEF_START_TIME;
			document.getElementById('FRI_START_TIME').value = DEF_START_TIME;
			document.getElementById('SAT_START_TIME').value = DEF_START_TIME;
			
			document.getElementById('SUN_START_TIME').disabled = false;
			document.getElementById('MON_START_TIME').disabled = false;
			document.getElementById('TUE_START_TIME').disabled = false;
			document.getElementById('WED_START_TIME').disabled = false;
			document.getElementById('THU_START_TIME').disabled = false;
			document.getElementById('FRI_START_TIME').disabled = false;
			document.getElementById('SAT_START_TIME').disabled = false;
		}
		
		if(DEF_END_TIME != ''){
			document.getElementById('SUN_END_TIME').value = DEF_END_TIME;
			document.getElementById('MON_END_TIME').value = DEF_END_TIME;
			document.getElementById('TUE_END_TIME').value = DEF_END_TIME;
			document.getElementById('WED_END_TIME').value = DEF_END_TIME;
			document.getElementById('THU_END_TIME').value = DEF_END_TIME;
			document.getElementById('FRI_END_TIME').value = DEF_END_TIME;
			document.getElementById('SAT_END_TIME').value = DEF_END_TIME;
			
			document.getElementById('SUN_END_TIME').disabled = false;
			document.getElementById('MON_END_TIME').disabled = false;
			document.getElementById('TUE_END_TIME').disabled = false;
			document.getElementById('WED_END_TIME').disabled = false;
			document.getElementById('THU_END_TIME').disabled = false;
			document.getElementById('FRI_END_TIME').disabled = false;
			document.getElementById('SAT_END_TIME').disabled = false;
		}
		
		if(DEF_HOURS != ''){
			document.getElementById('SUN_HOURS').value = DEF_HOURS;
			document.getElementById('MON_HOURS').value = DEF_HOURS;
			document.getElementById('TUE_HOURS').value = DEF_HOURS;
			document.getElementById('WED_HOURS').value = DEF_HOURS;
			document.getElementById('THU_HOURS').value = DEF_HOURS;
			document.getElementById('FRI_HOURS').value = DEF_HOURS;
			document.getElementById('SAT_HOURS').value = DEF_HOURS;
			
			document.getElementById('SUN_HOURS').disabled = false;
			document.getElementById('MON_HOURS').disabled = false;
			document.getElementById('TUE_HOURS').disabled = false;
			document.getElementById('WED_HOURS').disabled = false;
			document.getElementById('THU_HOURS').disabled = false;
			document.getElementById('FRI_HOURS').disabled = false;
			document.getElementById('SAT_HOURS').disabled = false;
		}
	}
	
	function reset_blank(){
		document.getElementById('SUN_START_TIME').value = '';
		document.getElementById('MON_START_TIME').value = '';
		document.getElementById('TUE_START_TIME').value = '';
		document.getElementById('WED_START_TIME').value = '';
		document.getElementById('THU_START_TIME').value = '';
		document.getElementById('FRI_START_TIME').value = '';
		document.getElementById('SAT_START_TIME').value = '';
		
		document.getElementById('SUN_END_TIME').value = '';
		document.getElementById('MON_END_TIME').value = '';
		document.getElementById('TUE_END_TIME').value = '';
		document.getElementById('WED_END_TIME').value = '';
		document.getElementById('THU_END_TIME').value = '';
		document.getElementById('FRI_END_TIME').value = '';
		document.getElementById('SAT_END_TIME').value = '';
	}
	function get_hour(id){
		var START_TIME = document.getElementById(id+'_START_TIME').value
		var END_TIME   = document.getElementById(id+'_END_TIME').value
		var HOURS	   = '';
		if(START_TIME != '' && END_TIME != ''){
			jQuery(document).ready(function($) { 
				var data  = 'START_TIME='+START_TIME+'&END_TIME='+END_TIME;
				var value = $.ajax({
					url: "ajax_get_hour_from_time.php",	
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
		} else {
			document.getElementById(id+'_START_TIME').disabled 	= true;
			document.getElementById(id+'_END_TIME').disabled 	= true
			document.getElementById(id+'_HOURS').value 			= '';
			
			document.getElementById(id+'_START_TIME').value = '';
			document.getElementById(id+'_END_TIME').value 	= '';
			
			document.getElementById(id+'_HOURS').disabled = true;
		}
	}
	
	function build_schedule(val){
		jQuery(document).ready(function($) { 
			var sun = 0;
			var mon = 0;
			var tue = 0;
			var wed = 0;
			var thu = 0;
			var fri = 0;
			var sat = 0;
			
			var sun_st = '';
			var sun_et = '';
			var sun_hr = '';
			
			var mon_st = '';
			var mon_et = '';
			var mon_hr = '';
			
			var tue_st = '';
			var tue_et = '';
			var tue_hr = '';
			
			var wed_st = '';
			var wed_et = '';
			var wed_hr = '';
			
			var thu_st = '';
			var thu_et = '';
			var thu_hr = '';
			
			var fri_st = '';
			var fri_et = '';
			var fri_hr = '';
			
			var sat_st = '';
			var sat_et = '';
			var sat_hr = '';
			
			if(document.getElementById('SUN').checked == true) {
				sun = 1;
				sun_st = $('#SUN_START_TIME').val();
				sun_et = $('#SUN_END_TIME').val();
				sun_hr = $('#SUN_HOURS').val();
			}
			
			if(document.getElementById('MON').checked == true) {
				mon = 1;
				mon_st = $('#MON_START_TIME').val();
				mon_et = $('#MON_END_TIME').val();
				mon_hr = $('#MON_HOURS').val();
			}
			
			if(document.getElementById('TUE').checked == true) {
				tue = 1;
				tue_st = $('#TUE_START_TIME').val();
				tue_et = $('#TUE_END_TIME').val();
				tue_hr = $('#TUE_HOURS').val();
			}
			
			if(document.getElementById('WED').checked == true) {
				wed = 1;
				wed_st = $('#WED_START_TIME').val();
				wed_et = $('#WED_END_TIME').val();
				wed_hr = $('#WED_HOURS').val();
			}
			
			if(document.getElementById('THU').checked == true) {
				thu = 1;
				thu_st = $('#THU_START_TIME').val();
				thu_et = $('#THU_END_TIME').val();
				thu_hr = $('#THU_HOURS').val();
			}
			
			if(document.getElementById('FRI').checked == true) {
				fri = 1;
				fri_st = $('#FRI_START_TIME').val();
				fri_et = $('#FRI_END_TIME').val();
				fri_hr = $('#FRI_HOURS').val();
			}
			
			if(document.getElementById('SAT').checked == true) {
				sat = 1;
				sat_st = $('#SAT_START_TIME').val();
				sat_et = $('#SAT_END_TIME').val();
				sat_hr = $('#SAT_HOURS').val();
			}
			
			var data  = 'sd='+$('#START_DATE').val()+'&ed='+$('#END_DATE').val()+'&sun='+sun+'&sun_st='+sun_st+'&sun_et='+sun_et+'&sun_hr='+sun_hr+'&mon='+mon+'&mon_st='+mon_st+'&mon_et='+mon_et+'&mon_hr='+mon_hr+'&tue='+tue+'&tue_st='+tue_st+'&tue_et='+tue_et+'&tue_hr='+tue_hr+'&wed='+wed+'&wed_st='+wed_st+'&wed_et='+wed_et+'&wed_hr='+wed_hr+'&thu='+thu+'&thu_st='+thu_st+'&thu_et='+thu_et+'&thu_hr='+thu_hr+'&fri='+fri+'&fri_st='+fri_st+'&fri_et='+fri_et+'&fri_hr='+fri_hr+'&sat='+sat+'&sat_st='+sat_st+'&sat_et='+sat_et+'&sat_hr='+sat_hr+'&PK_CAMPUS=<?=$PK_CAMPUS?>&PK_CAMPUS_ROOM=<?=$PK_CAMPUS_ROOM?>&BUILD='+val;
			var value = $.ajax({
				url: "ajax_build_course_offering_schedule.php",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					//alert(data)
					document.getElementById('schedule_div').innerHTML = data;
					calc_total_scheduled_hours(1)
				}		
			}).responseText;
		});
		
	}
	
	function delete_row(id,type){
		jQuery(document).ready(function($) {
			if(type == 'schedule_det')
				document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.SCHEDULE?>?';
			
				
			$("#deleteModal").modal()
			$("#DELETE_ID").val(id)
			$("#DELETE_TYPE").val(type)
		});
	}
	function conf_delete(val,id){
		jQuery(document).ready(function($) {
			if(val == 1) {
				if($("#DELETE_TYPE").val() == 'schedule_det') {
					var id = $("#DELETE_ID").val()
					$("#SCHEDULE_DIV_"+id).remove()
					calc_total_scheduled_hours(0)
				}
			}
			$("#deleteModal").modal("hide");
		});
	}
	
	function calc_total_scheduled_hours(show_warning){
		var TOT_SCHEDULE_HOURS 	= 0;
		var SCHEDULE_HOURS 		= document.getElementsByName('SCHEDULE_HOURS[]')
		
		for(var i = 0 ; i < SCHEDULE_HOURS.length ; i++){
			var SCHEDULE_HOURS_1 = SCHEDULE_HOURS[i].value
			if(SCHEDULE_HOURS_1 != '')
				TOT_SCHEDULE_HOURS = parseFloat(TOT_SCHEDULE_HOURS) + parseFloat(SCHEDULE_HOURS_1)
		}
		TOT_SCHEDULE_HOURS = TOT_SCHEDULE_HOURS.toFixed(2)
		document.getElementById('TOTAL_SCHEDULED_HOURS_SPAN').innerHTML = TOT_SCHEDULE_HOURS
		
		var COURSE_HOUR = document.getElementById('COURSE_HOUR').value
		if(COURSE_HOUR != '' && TOT_SCHEDULE_HOURS != ''){
			if(parseFloat(TOT_SCHEDULE_HOURS) > parseFloat(COURSE_HOUR) && show_warning == 1)
				alert('<?=TOTAL_SCHEDULED_HOURS_EXCEEDS_ERROR?>')
		}
		
	}
	</script>
</body>

</html>