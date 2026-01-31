<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

function displayDates($date1, $date2, $format, $SCHEDULE_ON_HOLIDAY, $PK_SESSION ) {
	global $db;
	
	$dates = array();
	$current = strtotime($date1);
	$date2 	 = strtotime($date2);
	$stepVal = '+1 day';
	while( $current <= $date2 ) {
	
		$temp_date = date($format, $current);
		if($SCHEDULE_ON_HOLIDAY == 1)
			$dates[] = $temp_date;
		else {
			$temp_date1 = date("Y-m-d",strtotime($temp_date));
			$res_type = $db->Execute("select PK_ACADEMIC_CALENDAR_SESSION from M_ACADEMIC_CALENDAR_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION = '$PK_SESSION' AND ACADEMY_DATE = '$temp_date1'; "); 
			
			if($res_type->RecordCount() == 0)
				$dates[] = $temp_date;
		}
		
		$current = strtotime($stepVal, $current);
	}
	return $dates;
}

$msg = "";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	foreach($_POST['PK_COURSE_OFFERING'] as $PK_COURSE_OFFERING_COPY) {
		$COURSE_OFFERING 	= array();
		$OFFERING_SCHEDULE 	= array();
		$COURSE_OFFERING['PK_TERM_MASTER']  			= $_POST['PK_TERM_MASTER_TO_1'];
		$COURSE_OFFERING['PK_COURSE']  					= $_POST['PK_COURSE_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['PK_CAMPUS']  					= $_POST['PK_CAMPUS_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['PK_SESSION']  				= $_POST['PK_SESSION_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['PK_COURSE_OFFERING_STATUS']  	= $_POST['PK_COURSE_OFFERING_STATUS_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['PK_CAMPUS_ROOM']  			= $_POST['PK_CAMPUS_ROOM_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['INSTRUCTOR']  				= $_POST['INSTRUCTOR_'.$PK_COURSE_OFFERING_COPY];
		$COURSE_OFFERING['ACTIVE']  					= 1;
		
		$res = $db->Execute("SELECT MAX(SESSION_NO) AS SESSION_NO FROM S_COURSE_OFFERING WHERE PK_COURSE = '$COURSE_OFFERING[PK_COURSE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$COURSE_OFFERING[PK_TERM_MASTER]' AND PK_CAMPUS = '$COURSE_OFFERING[PK_CAMPUS]' AND PK_SESSION = '$COURSE_OFFERING[PK_SESSION]' "); 
		$SESSION_NO = $res->fields['SESSION_NO'];
		if($SESSION_NO == '' || $SESSION_NO == 0)
			$SESSION_NO = 1;
		else
			$SESSION_NO += 1;
			
		$COURSE_OFFERING['SESSION_NO'] = $SESSION_NO;
		
		$res = $db->Execute("SELECT * FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_COPY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$COURSE_OFFERING['LMS_CODE']  						= $res->fields['LMS_CODE'];
		$COURSE_OFFERING['LMS_COURSE_TEMPLATE_ID']  		= $res->fields['LMS_COURSE_TEMPLATE_ID'];
		$COURSE_OFFERING['PK_ATTENDANCE_TYPE']  			= $res->fields['PK_ATTENDANCE_TYPE'];
		$COURSE_OFFERING['PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['PK_ATTENDANCE_ACTIVITY_TYPE'];
		$COURSE_OFFERING['PK_ATTENDANCE_CODE']  			= $res->fields['PK_ATTENDANCE_CODE'];
		$COURSE_OFFERING['CLASS_SIZE']  					= $res->fields['CLASS_SIZE'];
		$COURSE_OFFERING['ROOM_SIZE']  						= $res->fields['ROOM_SIZE'];
		$COURSE_OFFERING['CO_EXTERNAL_ID']  				= $res->fields['CO_EXTERNAL_ID'];
		$COURSE_OFFERING['OLD_DIAMOND_ID']  				= $res->fields['OLD_DIAMOND_ID'];
		$COURSE_OFFERING['LMS_ACTIVE']  					= $res->fields['LMS_ACTIVE'];
		
		$OFFERING_SCHEDULE['PK_COURSE']  = $COURSE_OFFERING['PK_COURSE'];
		if($_POST['CUSTOM_START_END_TIME_'.$PK_COURSE_OFFERING_COPY] == 1){
			$OFFERING_SCHEDULE['DEF_START_TIME']  	= $_POST['DEF_START_TIME_'.$PK_COURSE_OFFERING_COPY];
			$OFFERING_SCHEDULE['DEF_END_TIME']  	= $_POST['DEF_END_TIME_'.$PK_COURSE_OFFERING_COPY];
		}

		if($_POST['USE_DEFAULT_SCHEDULE_'.$PK_COURSE_OFFERING_COPY] == 1){
			$OFFERING_SCHEDULE['START_DATE']  	= $_POST['START_DATE_'.$PK_COURSE_OFFERING_COPY];
			$OFFERING_SCHEDULE['END_DATE']  	= $_POST['END_DATE_'.$PK_COURSE_OFFERING_COPY];
			
			$res = $db->Execute("SELECT DEF_START_TIME,DEF_END_TIME  FROM S_COURSE_OFFERING_SCHEDULE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_COPY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$OFFERING_SCHEDULE['DEF_START_TIME']  	= $res->fields['DEF_START_TIME'];
			$OFFERING_SCHEDULE['DEF_END_TIME']  	= $res->fields['DEF_END_TIME'];
		}

		if($OFFERING_SCHEDULE['DEF_START_TIME'] != '' && $OFFERING_SCHEDULE['DEF_END_TIME'] != '') {
			$starttimestamp 					= strtotime($OFFERING_SCHEDULE['DEF_START_TIME']);
			$endtimestamp 						= strtotime($OFFERING_SCHEDULE['DEF_END_TIME']);
			$OFFERING_SCHEDULE['DEF_HOURS'] 	= abs($endtimestamp - $starttimestamp)/3600;
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

		$COURSE_OFFERING['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
		$COURSE_OFFERING['CREATED_BY']  				= $_SESSION['PK_USER'];
		$COURSE_OFFERING['CREATED_ON']  				= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING', $COURSE_OFFERING, 'insert');
		$PK_COURSE_OFFERING = $db->insert_ID();
		
		$res_ass = $db->Execute("select * from S_COURSE_OFFERING_ASSISTANT WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_COPY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		while (!$res_ass->EOF) {
			$COURSE_OFFERING_ASSISTANT['PK_COURSE_OFFERING'] 	= $PK_COURSE_OFFERING;
			$COURSE_OFFERING_ASSISTANT['ASSISTANT'] 			= $res_ass->fields['ASSISTANT'];
			$COURSE_OFFERING_ASSISTANT['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$COURSE_OFFERING_ASSISTANT['CREATED_BY'] 			= $_SESSION['PK_USER'];
			$COURSE_OFFERING_ASSISTANT['CREATED_ON'] 			= date("Y-m-d H:i:s");
			db_perform('S_COURSE_OFFERING_ASSISTANT', $COURSE_OFFERING_ASSISTANT, 'insert');
			
			$res_ass->MoveNext();
		}
				
		if($_POST['USE_DEFAULT_SCHEDULE_'.$PK_COURSE_OFFERING_COPY] == 1){
			$res = $db->Execute("SELECT * FROM S_COURSE_DEFAULT_SCHEDULE WHERE PK_COURSE = '$COURSE_OFFERING[PK_COURSE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_SESSION ='$COURSE_OFFERING[PK_SESSION]'");
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
				
				$OFFERING_SCHEDULE['SUN_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['MON_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['MON_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['TUE_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['WED_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['WED_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['THU_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['THU_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['FRI_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'];
				$OFFERING_SCHEDULE['SAT_PK_ATTENDANCE_ACTIVITY_TYPE']  	= $res->fields['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'];
			}
		}
		$OFFERING_SCHEDULE['PK_COURSE_OFFERING']  	= $PK_COURSE_OFFERING;
		$OFFERING_SCHEDULE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
		$OFFERING_SCHEDULE['CREATED_BY']  			= $_SESSION['PK_USER'];
		$OFFERING_SCHEDULE['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_COURSE_OFFERING_SCHEDULE', $OFFERING_SCHEDULE, 'insert');
		$PK_COURSE_OFFERING_SCHEDULE = $db->insert_ID();
		
		if($_POST['USE_DEFAULT_SCHEDULE_'.$PK_COURSE_OFFERING_COPY] == 1){
			$START_DATE = $OFFERING_SCHEDULE['START_DATE'];
			$END_DATE 	= $OFFERING_SCHEDULE['END_DATE'];

			$PK_CAMPUS = $COURSE_OFFERING['PK_CAMPUS'];

			if($START_DATE != '')
				$START_DATE = date("Y-m-d",strtotime($START_DATE));

			if($END_DATE != '')
				$END_DATE = date("Y-m-d",strtotime($END_DATE));
				
			$res_type = $db->Execute("select HOURS from S_COURSE WHERE PK_COURSE = '$COURSE_OFFERING[PK_COURSE]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$COURSE_HOUR 	= $res_type->fields['HOURS'];
			$TOTAL_HOUR 	= 0;
			
			$HAS_END_DATE = 1;
			if($END_DATE == ''){
				$HAS_END_DATE 	= 0;
				$END_DATE 		= date('Y-m-d', strtotime($START_DATE. ' + 5 years'));
			}

			if($START_DATE != '' && $END_DATE != '') {
				$SCHEDULE_DATES = displayDates($START_DATE, $END_DATE,'m/d/Y', 0, $COURSE_OFFERING['PK_SESSION']);
				
				$sun	= $OFFERING_SCHEDULE['SUNDAY'];
				$mon 	= $OFFERING_SCHEDULE['MONDAY'];
				$tue 	= $OFFERING_SCHEDULE['TUESDAY'];
				$wed 	= $OFFERING_SCHEDULE['WEDNESDAY'];
				$thu	= $OFFERING_SCHEDULE['THURSDAY'];
				$fri 	= $OFFERING_SCHEDULE['FRIDAY'];
				$sat 	= $OFFERING_SCHEDULE['SATURDAY'];
				
				$i = 0;
				foreach($SCHEDULE_DATES as $SCHEDULE_DATE){ 
					$day = date("N",strtotime($SCHEDULE_DATE)); 
					$i++;
					
					if(($day == 1 && $mon == 1) || ($day == 2 && $tue == 1) || ($day == 3 && $wed == 1) || ($day == 4 && $thu == 1) || ($day == 5 && $fri == 1) || ($day == 6 && $sat == 1) || ($day == 7 && $sun == 1) ){ 
						if($day == 1 && $mon == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['MON_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['MON_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['MON_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['MON_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['MON_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 2 && $tue == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['TUE_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['TUE_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['TUE_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['TUE_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['TUE_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 3 && $wed == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['WED_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['WED_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['WED_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['WED_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['WED_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 4 && $thu == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['THU_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['THU_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['THU_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['THU_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['THU_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 5 && $fri == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['FRI_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['FRI_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['FRI_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['FRI_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['FRI_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 6 && $sat == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['SAT_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['SAT_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['SAT_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['SAT_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['SAT_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} else if($day == 7 && $sun == 1) {
							$START_TIME 	= $OFFERING_SCHEDULE['SUN_START_TIME'] ;
							$END_TIME 		= $OFFERING_SCHEDULE['SUN_END_TIME'];
							$HOURS 			= $OFFERING_SCHEDULE['SUN_HOURS'];
							$PK_CAMPUS_ROOM = $OFFERING_SCHEDULE['SUN_ROOM'];
							$PK_ATTENDANCE_ACTIVITY_TYPES = $OFFERING_SCHEDULE['SUN_PK_ATTENDANCE_ACTIVITY_TYPE'];
						} 
						$starttimestamp = strtotime($START_TIME);
						$endtimestamp 	= strtotime($END_TIME);
						if($HOURS == '' && ($starttimestamp != '' && $endtimestamp != '') )
							$HOURS 	= abs($endtimestamp - $starttimestamp)/3600;
						
						$PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR[] = '0';
						$PK_ATTENDANCE_ACTIVITY_TYPES_ARR[] = $PK_ATTENDANCE_ACTIVITY_TYPES;
						$PK_CAMPUS_ROOM_ARR[]				= $PK_CAMPUS_ROOM;
						$SCHEDULE_DATE_ARR[] 				= $SCHEDULE_DATE;
						$START_TIME_ARR[] 					= $START_TIME;
						$END_TIME_ARR[] 					= $END_TIME;
						$HOURS_ARR[] 						= $HOURS;
						$COMP_ARR[] 						= '';
						
						//if($HAS_END_DATE == 0) {
							$TOTAL_HOUR += $HOURS;
							if($TOTAL_HOUR >= $COURSE_HOUR)
								break;
						//}
					}
				} 
				foreach($PK_COURSE_OFFERING_SCHEDULE_DETAIL_ARR as $key => $val) {
					
					$SCHEDULE_DETAIL = array();
					$SCHEDULE_DETAIL['SCHEDULE_DATE']  					= $SCHEDULE_DATE_ARR[$key];
					$SCHEDULE_DETAIL['START_TIME']  					= $START_TIME_ARR[$key];
					$SCHEDULE_DETAIL['END_TIME']  						= $END_TIME_ARR[$key];
					$SCHEDULE_DETAIL['HOURS']  							= $HOURS_ARR[$key];
					$SCHEDULE_DETAIL['PK_CAMPUS_ROOM']  				= $PK_CAMPUS_ROOM_ARR[$key];
					$SCHEDULE_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPES']  	= $PK_ATTENDANCE_ACTIVITY_TYPES_ARR[$key];
					$SCHEDULE_DETAIL['COMPLETED']  						= 0;
					$SCHEDULE_DETAIL['PK_COURSE']  						= $COURSE_OFFERING['PK_COURSE'];
					
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

					$SCHEDULE_DETAIL['PK_COURSE_OFFERING_SCHEDULE'] = $PK_COURSE_OFFERING_SCHEDULE;
					$SCHEDULE_DETAIL['PK_COURSE_OFFERING']  		= $PK_COURSE_OFFERING;
					$SCHEDULE_DETAIL['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
					$SCHEDULE_DETAIL['CREATED_BY']  				= $_SESSION['PK_USER'];
					$SCHEDULE_DETAIL['CREATED_ON']  				= date("Y-m-d H:i");
					db_perform('S_COURSE_OFFERING_SCHEDULE_DETAIL', $SCHEDULE_DETAIL, 'insert');
				}
			}
		}
		
		$res = $db->Execute("SELECT PK_COURSE FROM S_COURSE_OFFERING WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_COPY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		if($COURSE_OFFERING['PK_COURSE'] == $res->fields['PK_COURSE']) {
			$res = $db->Execute("SELECT * FROM S_COURSE_OFFERING_GRADE WHERE PK_COURSE_OFFERING = '$PK_COURSE_OFFERING_COPY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res->EOF) {
				$COURSE_OFFERING_GRADE = array();
				$COURSE_OFFERING_GRADE['GRADE_ORDER'] 		 	= $res->fields['GRADE_ORDER'];
				$COURSE_OFFERING_GRADE['CODE'] 				 	= $res->fields['CODE'];
				$COURSE_OFFERING_GRADE['DESCRIPTION'] 		 	= $res->fields['DESCRIPTION'];
				$COURSE_OFFERING_GRADE['PK_GRADE_BOOK_TYPE'] 	= $res->fields['PK_GRADE_BOOK_TYPE'];
				$COURSE_OFFERING_GRADE['DATE'] 				 	= $res->fields['DATE'];
				$COURSE_OFFERING_GRADE['PERIOD'] 			 	= $res->fields['PERIOD'];
				$COURSE_OFFERING_GRADE['POINTS'] 			 	= $res->fields['POINTS'];
				$COURSE_OFFERING_GRADE['WEIGHT'] 			 	= $res->fields['WEIGHT'];
				$COURSE_OFFERING_GRADE['WEIGHTED_POINTS'] 		= $res->fields['WEIGHTED_POINTS'];
				$COURSE_OFFERING_GRADE['SORT_ORDER'] 		 	= $res->fields['SORT_ORDER'];
				$COURSE_OFFERING_GRADE['PK_COURSE_OFFERING']  	= $PK_COURSE_OFFERING;
				$COURSE_OFFERING_GRADE['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
				$COURSE_OFFERING_GRADE['CREATED_BY']  			= $_SESSION['PK_USER'];
				$COURSE_OFFERING_GRADE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_COURSE_OFFERING_GRADE', $COURSE_OFFERING_GRADE, 'insert');
				
				$res->MoveNext();
			}
		}
	}
	$msg = "Copied Successfully";
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
	<title><?=COURSE_OFFERING_COPY_BY_TERM?> | <?=$title?></title>
	<style>
	.lessPadding th, .lessPadding td{padding: 0.3rem;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center" >
                        <h4 class="text-themecolor"><?=COURSE_OFFERING_COPY_BY_TERM?> </h4>
                    </div>
					
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
							<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS'] ?>" <? if($res_type->RecordCount() == 1) echo "selected"; ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_TERM_MASTER" name="PK_TERM_MASTER" class="form-control " >
							<option value=""><?=TERM?></option>
							<? /* Ticket #1149 - term */
							$res_type = $db->Execute("select PK_TERM_MASTER, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, TERM_DESCRIPTION, ACTIVE from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
							while (!$res_type->EOF) { 
								$str = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'];
								if($res_type->fields['ACTIVE'] == 0)
									$str .= ' (Inactive)'; ?>
								<option value="<?=$res_type->fields['PK_TERM_MASTER']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$str ?></option>
							<?	$res_type->MoveNext();
							} /* Ticket #1149 - term */ ?>
						</select>
					</div> 
					
					<div class="col-md-2 align-self-center text-right" >
						<select id="PK_SESSION" name="PK_SESSION" class="form-control" >
							<option value="" ><?=SESSION?></option>
							<? $res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_SESSION'] ?>"  ><?=$res_type->fields['SESSION']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</div>
					
                    <div class="col-md-1 align-self-center text-right" >
						<a href="javascript:void(0)" onclick="search_fun()" class="btn btn-info d-none d-lg-block m-l-15" style="margin-top: -8px;" ><?=SHOW?></a>
                    </div>
                </div>
				
				<form method="post" name="form1" id="form1" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12" id="result_div">
											<? if($msg != ''){ ?>
												<span style="color:red" ><?=$msg?></span>
											<? } ?>
											
											<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-12 text-right" style="display:none" id="btn_div" >
											<input type="hidden" name="PK_TERM_MASTER_TO_1" id="PK_TERM_MASTER_TO_1" value="" >
											<button type="button" onclick="validate_form()" class="btn waves-effect waves-light btn-info"><?=COPY ?></button>
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=CANCEL?></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>

        <? require_once("footer.php"); ?>
		
		<div class="modal" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=COURSE_OFFERING_COPY_BY_TERM?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group row">
							<div class="col-5 col-sm-5" >
								<?=TERM_TO_COPY_FROM ?>
							</div>
							<div class="col-7 col-sm-7" id="TERM_TO_COPY_FROM_DIV" >
							</div>
						</div>
						
						<div class="form-group row">
							<div class="col-5 col-sm-5" >
								<?=TERM_TO_COPY_TO ?>
							</div>
							<div class="col-7 col-sm-7" >
								<select id="PK_TERM_MASTER_TO" name="PK_TERM_MASTER_TO" class="form-control " >
									<option value=""></option>
									<? $res_type = $db->Execute("select PK_TERM_MASTER,DATE_FORMAT(BEGIN_DATE,'%m/%d/%Y') AS BEGIN_DATE_1, TERM_DESCRIPTION from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by BEGIN_DATE DESC");
									while (!$res_type->EOF) { ?>
										<option value="<?=$res_type->fields['PK_TERM_MASTER'] ?>" ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['TERM_DESCRIPTION'] ?></option>
									<?	$res_type->MoveNext();
									} ?>
								</select>
							</div>
						</div>
						
						<div class="form-group row">
							<span style="color:red" >Once 'Proceed' is clicked this process cannot be undone. </span>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_procced(1)" class="btn waves-effect waves-light btn-info"><?=PROCEED?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_procced(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">
	
	function search_fun(){
		jQuery(document).ready(function($) {
			var error = "";
			if($('#PK_CAMPUS').val() == '') {
				if(error != '')
					error += "\n";
				error += "Please select a Campus";
			}
			
			if($('#PK_TERM_MASTER').val() == '') {
				if(error != '')
					error += "\n";
				error += "Please select a Term";
			}
			
			if(error != '')
				alert(error)
			else {
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_TERM_MASTER='+$('#PK_TERM_MASTER').val()+'&PK_SESSION='+$('#PK_SESSION').val();
				var value = $.ajax({
					url: "ajax_search_course_offering",
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						document.getElementById('result_div').innerHTML = data
						
						$('.timepicker').inputmask(
							"hh:mm t", {
								placeholder: "HH:MM AM/PM", 
								insertMode: false, 
								showMaskOnHover: false,
								hourFormat: 12
							}
						);
						
						jQuery('.date').datepicker({
							todayHighlight: true,
							orientation: "bottom auto"
						});
						
						if(data != '')
							document.getElementById('btn_div').style.display = 'block'
						else
							document.getElementById('btn_div').style.display = 'none'
					}		
				}).responseText;
			}
		});
	}
	
	function disable_default_sch(val, id, START_TIME, END_TIME, START_DATE, END_DATE){
		if(val == 1) {
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).value = 2;
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).disabled = true
			
			document.getElementById('DEF_START_TIME_'+id).disabled 		 = false
			document.getElementById('DEF_END_TIME_'+id).disabled 		 = false
			
			
			document.getElementById('DEF_START_TIME_'+id).value = START_TIME;
			document.getElementById('DEF_END_TIME_'+id).value 	= END_TIME;
		} else {
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).disabled = false
			document.getElementById('DEF_START_TIME_'+id).disabled 	= true
			document.getElementById('DEF_END_TIME_'+id).disabled 	= true
			
		}
		
		/*if(val == 2) {
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).value = 1;
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).disabled = true
			
			document.getElementById('DEF_START_TIME_'+id).disabled 	= true
			document.getElementById('DEF_END_TIME_'+id).disabled 	= true
			
			document.getElementById('DEF_START_TIME_'+id).value = START_TIME;
			document.getElementById('DEF_END_TIME_'+id).value 	= END_TIME;
			
			document.getElementById('START_DATE_'+id).disabled 	= true
			document.getElementById('END_DATE_'+id).disabled 	= true
			
			document.getElementById('START_DATE_'+id).value = START_DATE;
			document.getElementById('END_DATE_'+id).value 	= END_DATE;
		} else {
			document.getElementById('USE_DEFAULT_SCHEDULE_'+id).disabled = false
			document.getElementById('DEF_START_TIME_'+id).disabled 		 = false
			document.getElementById('DEF_END_TIME_'+id).disabled 		 = false
			
			document.getElementById('START_DATE_'+id).disabled 		 = false
			document.getElementById('END_DATE_'+id).disabled 		 = false
		}*/
	}
	
	function disable_default_time(val, id, START_TIME, END_TIME, START_DATE, END_DATE){
		if(val == 1) {			
			document.getElementById('START_DATE_'+id).disabled 		 = false
			document.getElementById('END_DATE_'+id).disabled 		 = false
		} else {
			document.getElementById('START_DATE_'+id).disabled 	= true
			document.getElementById('END_DATE_'+id).disabled 	= true
			
			document.getElementById('START_DATE_'+id).value = START_DATE;
			document.getElementById('END_DATE_'+id).value 	= END_DATE;
		}
	}
	
	function copy_co(){
		jQuery(document).ready(function($) {
			var flag = 0;
			var PK_COURSE_OFFERING = document.getElementsByName('PK_COURSE_OFFERING[]')
			for(var i = 0 ; i < PK_COURSE_OFFERING.length ; i++){
				if(PK_COURSE_OFFERING[i].checked == true){
					flag = 1;
					break;
				}
			}
			
			if(flag == 0)
				alert('Please select at least one Course Offering to continue');
			else {
				document.getElementById('TERM_TO_COPY_FROM_DIV').innerHTML = $("#PK_TERM_MASTER option:selected").text();
				$("#assignModal").modal()
			}
		});
	}
	
	function conf_procced(val){
		jQuery(document).ready(function($) {
			if(val == 0)
				$("#assignModal").modal("hide")
			else {
				if(document.getElementById('PK_TERM_MASTER_TO').value == '')
					alert("Please Select Term To Copy To")
				else {
					document.getElementById('PK_TERM_MASTER_TO_1').value = document.getElementById('PK_TERM_MASTER_TO').value
					document.form1.submit();
				}	
			}
		});
	}
	
	function set_required(id){
		if(document.getElementById('PK_COURSE_OFFERING_'+id).checked == true){
			document.getElementById('PK_COURSE_'+id).className 					= "form-control required-entry";
			document.getElementById('PK_SESSION_'+id).className 				= "form-control required-entry";
			document.getElementById('SESSION_NO_'+id).className 				= "form-control required-entry";
			document.getElementById('PK_COURSE_OFFERING_STATUS_'+id).className 	= "form-control required-entry";
			
			/*if(document.getElementById('DEF_START_TIME_'+id).disabled == false)
				document.getElementById('DEF_START_TIME_'+id).className 	= "form-control timepicker required-entry";
			else
				document.getElementById('DEF_START_TIME_'+id).className 	= "form-control timepicker";
				
			if(document.getElementById('DEF_END_TIME_'+id).disabled == false)
				document.getElementById('DEF_END_TIME_'+id).className 	= "form-control timepicker required-entry";
			else
				document.getElementById('DEF_END_TIME_'+id).className 	= "form-control timepicker";*/
				
			if(document.getElementById('START_DATE_'+id).disabled == false)
				document.getElementById('START_DATE_'+id).className 	= "form-control timepicker required-entry";
			else
				document.getElementById('START_DATE_'+id).className 	= "form-control timepicker";
				
			if(document.getElementById('END_DATE_'+id).disabled == false)
				document.getElementById('END_DATE_'+id).className 	= "form-control timepicker required-entry";
			else
				document.getElementById('END_DATE_'+id).className 	= "form-control timepicker";
			//alert('aa')
			
			//alert(document.getElementById('END_DATE_'+id).className)
		} else {
			document.getElementById('PK_COURSE_'+id).className 					= "form-control ";
			document.getElementById('PK_SESSION_'+id).className 				= "form-control ";
			document.getElementById('SESSION_NO_'+id).className 				= "form-control ";
			document.getElementById('PK_COURSE_OFFERING_STATUS_'+id).className 	= "form-control ";
			
			document.getElementById('DEF_START_TIME_'+id).className 	= "form-control timepicker";
			document.getElementById('DEF_END_TIME_'+id).className 		= "form-control timepicker";
			document.getElementById('START_DATE_'+id).className 		= "form-control timepicker";
			document.getElementById('END_DATE_'+id).className 			= "form-control timepicker";
			
			document.getElementById('DEF_START_TIME_'+id).className 	= "form-control timepicker";
			document.getElementById('DEF_END_TIME_'+id).className 		= "form-control timepicker";
			document.getElementById('START_DATE_'+id).className 		= "form-control timepicker";
			document.getElementById('END_DATE_'+id).className 			= "form-control timepicker";
			
			//alert('bb')
		}
	}
	
	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		var PK_COURSE_OFFERING = document.getElementsByName('PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < PK_COURSE_OFFERING.length ; i++){
			PK_COURSE_OFFERING[i].checked = str
		}
		get_count()
	}
	function get_count(){
		var tot = 0
		var PK_COURSE_OFFERING = document.getElementsByName('PK_COURSE_OFFERING[]')
		for(var i = 0 ; i < PK_COURSE_OFFERING.length ; i++){
			if(PK_COURSE_OFFERING[i].checked == true) {
				tot++;
			}
			set_required(PK_COURSE_OFFERING[i].value)
		}
		document.getElementById('SELECTED_COUNT').innerHTML = tot
	}

	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function validate_form(){
		jQuery(document).ready(function($) { 
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true) {
				copy_co()
			}
		});
	}
	</script>

</body>

</html>