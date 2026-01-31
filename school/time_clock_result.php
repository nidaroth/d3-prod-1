<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/time_clock.php");
require_once("../language/attendance_entry.php");
require_once("function_attendance.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

if($_GET['act'] == 'del'){
	$db->Execute("DELETE FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR_DETAIL IN ($_GET[iid]) ");
	header("location:time_clock_result?id=".$_GET['id'].'&exclude='.$_GET['exclude'].'&t='.$_GET['t']);
}

if(!empty($_POST)){

	$res = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL, NOT_FOUND_ON_FILE FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' ");
	
if($_POST['T_POST_TYPE']==1){
	$_POST['SELECT_ID']=$_POST['PK_TIME_CLOCK_PROCESSOR_DETAIL'];
}
if(!empty($_POST['SELECT_ID']))
{
	foreach($_POST['SELECT_ID'] as $PK_TIME_CLOCK_PROCESSOR_DETAIL)
	{
		$PK_COURSE_OFFERING = $_POST['PK_COURSE_OFFERING_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		
		$TIME_CLOCK_PROCESSOR_DETAIL = array();
		$res = $db->Execute("SELECT PK_COURSE_OFFERING_SCHEDULE_DETAIL, NOT_FOUND_ON_FILE FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' ");
		$NOT_FOUND_ON_FILE = $res->fields['NOT_FOUND_ON_FILE'];
		
		if($res->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] == 0 && $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL] > 0)
			$TIME_CLOCK_PROCESSOR_DETAIL['MESSAGE'] = 'Schedule Manually Applied';
		
		$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING'] 					= $_POST['PK_COURSE_OFFERING_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		$TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] 	= $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_ACTIVITY_TYPE'] 		= $_POST['PK_ATTENDANCE_ACTIVITY_TYPE_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		$TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE']   				= $_POST['PK_ATTENDANCE_CODE_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] 					= $_POST['ATTENDANCE_HOUR_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		$TIME_CLOCK_PROCESSOR_DETAIL['BREAK_IN_MIN'] 						= $_POST['BREAK_IN_MIN_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
		db_perform('S_TIME_CLOCK_PROCESSOR_DETAIL', $TIME_CLOCK_PROCESSOR_DETAIL, 'update'," PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		
		if($TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] != '' && $TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] > 0 && $NOT_FOUND_ON_FILE == 0) {
			$PK_STUDENT_MASTER1 = $_POST['PK_STUDENT_MASTER_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			
			$res_d = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER1' AND NOT_FOUND_ON_FILE = 1 AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$TIME_CLOCK_PROCESSOR_DETAIL[PK_COURSE_OFFERING_SCHEDULE_DETAIL]' ");

			if($res_d->RecordCount() > 0) {
				$DEL_PK_TIME_CLOCK_PROCESSOR_DETAIL = $res_d->fields['PK_TIME_CLOCK_PROCESSOR_DETAIL'];
				$db->Execute("DELETE FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR_DETAIL = '$DEL_PK_TIME_CLOCK_PROCESSOR_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			}
		}
		
		if($PK_COURSE_OFFERING > 0 && $TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE'] != '' && $_POST['POST_ATTENDANCE'] == 2) {
		
			$PK_STUDENT_SCHEDULE 				= '';
			$PK_STUDENT_ATTENDANCE 				= '';
			$PK_STUDENT_MASTER 					= $_POST['PK_STUDENT_MASTER_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$DATE 								= $_POST['DATE_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$START_TIME 						= $_POST['START_TIME_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$END_TIME							= $_POST['END_TIME_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$BREAK_IN_MIN						= $_POST['BREAK_IN_MIN_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$POST_TYPE							= $_POST['POST_TYPE_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			$PK_COURSE_OFFERING_SCHEDULE_DETAIL	= $_POST['PK_COURSE_OFFERING_SCHEDULE_DETAIL_'.$PK_TIME_CLOCK_PROCESSOR_DETAIL];
			
			$time1  = $START_TIME;
			$time2  = $END_TIME;
			$array1 = explode(':', $time1);
			$array2 = explode(':', $time2);

			$minutes1 = ($array1[0] * 60.0 + $array1[1]);
			$minutes2 = ($array2[0] * 60.0 + $array2[1]);
			
			$ATTENDANCE_HOUR = '';
			if($_GET['t'] == 4)
				$ATTENDANCE_HOUR = $TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'];
			else if($_GET['t'] == 3) {
				$BREAK1 = 0;
				if($BREAK_IN_MIN > 0)
					$BREAK1 = $BREAK_IN_MIN / 60 * 100;
				$ATTENDANCE_HOUR = $TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'] - $BREAK1;
			}else {
				$ATTENDANCE_HOUR = $TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'];
				if($ATTENDANCE_HOUR > 0 && $BREAK_IN_MIN > 0) {
					$ATTENDANCE_HOUR = number_format_value_checker(($ATTENDANCE_HOUR - ($BREAK_IN_MIN / 60)),2);
				}
			}
			//echo $ATTENDANCE_HOUR."<br />".$minutes2."<br />".$minutes1."<br />".$BREAK_IN_MIN."<br />".$TIME_CLOCK_PROCESSOR_DETAIL['ATTENDANCE_HOUR'];exit;
			
			$res_co = $db->Execute("SELECT S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,PK_STUDENT_COURSE FROM S_STUDENT_COURSE, S_COURSE_OFFERING WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' ");
			if($res_co->RecordCount() > 0) {
				$DATE_TIME 			= $DATE.' '.$START_TIME;
				$FROM_DATE_TIME 	= date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($DATE_TIME)));
				$TO_DATE_TIME 		= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($DATE_TIME)));

				if($TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] > 0){
					
					$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $TIME_CLOCK_PROCESSOR_DETAIL['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
				
					$res_sch = $db->Execute("select PK_STUDENT_SCHEDULE, PK_STUDENT_ENROLLMENT from S_STUDENT_SCHEDULE, S_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER AND S_STUDENT_SCHEDULE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
					
					if($res_sch->RecordCount() > 0) {
						$PK_STUDENT_SCHEDULE = $res_sch->fields['PK_STUDENT_SCHEDULE'];
						
						$res_att = $db->Execute("SELECT PK_STUDENT_ATTENDANCE FROM S_STUDENT_ATTENDANCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_SCHEDULE = '$PK_STUDENT_SCHEDULE' ");
						if($res_att->RecordCount() > 0)
							$PK_STUDENT_ATTENDANCE = $res_att->fields['PK_STUDENT_ATTENDANCE'];
						
					} else {
						$STUDENT_SCHEDULE = array();
						$STUDENT_SCHEDULE['PK_SCHEDULE_TYPE'] 		 			= 1;
						$STUDENT_SCHEDULE['SCHEDULE_DATE'] 			 			= $DATE;
						$STUDENT_SCHEDULE['START_TIME'] 			 			= $START_TIME;
						$STUDENT_SCHEDULE['END_TIME'] 	 			 			= $END_TIME;
						$STUDENT_SCHEDULE['HOURS'] 					 			= $ATTENDANCE_HOUR;
						$STUDENT_SCHEDULE['PK_COURSE_OFFERING_SCHEDULE_DETAIL'] = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
						$STUDENT_SCHEDULE['PK_STUDENT_COURSE'] 	 	 			= $res_co->fields['PK_STUDENT_COURSE'];		
						$STUDENT_SCHEDULE['PK_STUDENT_ENROLLMENT'] 	 			= $res_co->fields['PK_STUDENT_ENROLLMENT'];
						$STUDENT_SCHEDULE['PK_STUDENT_MASTER'] 	 	 			= $PK_STUDENT_MASTER;
						$STUDENT_SCHEDULE['PK_ACCOUNT'] 			 			= $_SESSION['PK_ACCOUNT'];
						$STUDENT_SCHEDULE['CREATED_BY']  			 			= $_SESSION['PK_USER'];
						$STUDENT_SCHEDULE['CREATED_ON']  			 			= date("Y-m-d H:i");
						db_perform('S_STUDENT_SCHEDULE', $STUDENT_SCHEDULE, 'insert');
						$PK_STUDENT_SCHEDULE = $db->insert_ID();
					}
				
					if($TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE'] == 1){
						$ATTENDANCE_HOUR = 0;
					}
					//echo $PK_COURSE_OFFERING_SCHEDULE_DETAIL;exit;
					attendance_log("Blocl 1",$PK_COURSE_OFFERING_SCHEDULE_DETAIL);
					$PK_STUDENT_ATTENDANCE = attendance_entry($PK_COURSE_OFFERING_SCHEDULE_DETAIL,1,$PK_STUDENT_ATTENDANCE,$PK_STUDENT_MASTER,$res_co->fields['PK_STUDENT_ENROLLMENT'],$PK_STUDENT_SCHEDULE,$ATTENDANCE_HOUR , $TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE'],$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER'],1);
					
					$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET PK_ATTENDANCE_ACTIVITY_TYPESS = '$TIME_CLOCK_PROCESSOR_DETAIL[PK_ATTENDANCE_ACTIVITY_TYPE]' WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					
				} else {
					$res_ns = $db->Execute("select PK_STUDENT_ATTENDANCE, START_TIME, END_TIME, HOURS, ATTENDANCE_HOURS, PK_ATTENDANCE_CODE, S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT, S_STUDENT_MASTER.PK_STUDENT_MASTER, S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  
					from 
					S_STUDENT_ATTENDANCE, S_STUDENT_SCHEDULE, S_STUDENT_COURSE, S_STUDENT_MASTER, S_STUDENT_ENROLLMENT   
					WHERE 
					S_STUDENT_ATTENDANCE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
					S_STUDENT_ATTENDANCE.PK_STUDENT_SCHEDULE  = S_STUDENT_SCHEDULE.PK_STUDENT_SCHEDULE AND 
					CONCAT(S_STUDENT_SCHEDULE.SCHEDULE_DATE,' ',S_STUDENT_SCHEDULE.START_TIME) BETWEEN '$FROM_DATE_TIME' and '$TO_DATE_TIME' AND 
					S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
					PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND 
					PK_SCHEDULE_TYPE = 2 AND 
					S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND 
					S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
					S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND 
					S_STUDENT_COURSE.PK_STUDENT_COURSE = S_STUDENT_SCHEDULE.PK_STUDENT_COURSE ");
					if($res_ns->RecordCount() > 0) {
						$PK_STUDENT_SCHEDULE 	= $res_ns->fields['PK_STUDENT_SCHEDULE'];
						$PK_STUDENT_ATTENDANCE 	= $res_ns->fields['PK_STUDENT_ATTENDANCE'];
					}
					
					$PK_STUDENT_SCHEDULE = create_non_schedule($PK_STUDENT_SCHEDULE,$PK_COURSE_OFFERING,$DATE,$START_TIME,$END_TIME,'0',$PK_STUDENT_MASTER,$res_co->fields['PK_STUDENT_ENROLLMENT'], 1,$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER']);
					
					if($TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE'] == 1){
						$ATTENDANCE_HOUR = 0;
					}

					attendance_log("Blocl 2","Hee");

					$PK_STUDENT_ATTENDANCE = attendance_entry('',1,$PK_STUDENT_ATTENDANCE,$PK_STUDENT_MASTER,$res_co->fields['PK_STUDENT_ENROLLMENT'],$PK_STUDENT_SCHEDULE,$ATTENDANCE_HOUR, $TIME_CLOCK_PROCESSOR_DETAIL['PK_ATTENDANCE_CODE'],$_SESSION['PK_ACCOUNT'],$_SESSION['PK_USER'],1);
					
					$db->Execute("UPDATE S_STUDENT_ATTENDANCE SET PK_ATTENDANCE_ACTIVITY_TYPESS = '$TIME_CLOCK_PROCESSOR_DETAIL[PK_ATTENDANCE_ACTIVITY_TYPE]' WHERE PK_STUDENT_ATTENDANCE = '$PK_STUDENT_ATTENDANCE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
				}
				
				$db->Execute("UPDATE S_TIME_CLOCK_PROCESSOR_DETAIL SET POSTED=1 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TIME_CLOCK_PROCESSOR_DETAIL = '$PK_TIME_CLOCK_PROCESSOR_DETAIL' ");
			}
		
		}
	}

}

	// FOREACH COMPLETED HERE
	
	$res_clock = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR= '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$TIME_CLOCK_PROCESSOR1['TOTAL_COUNT'] = $res_clock->RecordCount();

	db_perform('S_TIME_CLOCK_PROCESSOR', $TIME_CLOCK_PROCESSOR1, 'update'," PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$res_clock = $db->Execute("SELECT POSTED FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR= '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND POSTED = 0");
	if($res_clock->RecordCount() == 0) {
		$TIME_CLOCK_PROCESSOR['POSTED'] = 1;
		$TIME_CLOCK_PROCESSOR['POSTED_BY'] = $_SESSION['PK_USER'];
		$TIME_CLOCK_PROCESSOR['POSTED_ON'] = date("Y-m-d H:i");
		db_perform('S_TIME_CLOCK_PROCESSOR', $TIME_CLOCK_PROCESSOR, 'update'," PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	//exit;
	header("location:time_clock_result?id=".$_GET['id'].'&t='.$_GET['t']);
	exit;
}
$res_clock = $db->Execute("SELECT PK_TIME_CLOCK_PROCESSOR, IMPORTED_COUNT, TOTAL_COUNT, POSTED FROM S_TIME_CLOCK_PROCESSOR WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_clock->RecordCount() == 0) {
	header("location:../index");
	exit;
}
$IMPORTED_COUNT = $res_clock->fields['IMPORTED_COUNT'];
$TOTAL_COUNT 	= $res_clock->fields['TOTAL_COUNT'];
$POSTED			= $res_clock->fields['POSTED'];

$CLOCK_PK_COURSE_OFFERING = '';
$res = $db->Execute("SELECT PK_COURSE_OFFERING FROM S_TIME_CLOCK_PROCESSOR_DETAIL WHERE PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY PK_COURSE_OFFERING");
while (!$res->EOF) {
	if($CLOCK_PK_COURSE_OFFERING != '')
		$CLOCK_PK_COURSE_OFFERING .= ',';
		
	$CLOCK_PK_COURSE_OFFERING .= $res->fields['PK_COURSE_OFFERING'];
	$res->MoveNext();
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
	<style>
		.popup-table {
			border: 1px solid #ddd;
		}
	</style>
	<? require_once("css.php"); ?>
	<title><?=TIME_CLOCK_IMPORT_RESULT ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><?=TIME_CLOCK_IMPORT_RESULT ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data">
									<? if($msg1 != '' ){ ?>
									<div class="row">
										<div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-6" style="color:red">
											<?=$msg1?>
										</div>
                                    </div>
									<? } ?>
									<!-- DIAM-1349 -->
									<? if($POSTED == 0) { ?>
									<div class="row">
                                        <div class="col-md-5">
											<div class="form-group m-b-5 text-left" >
												<button type="button" onclick="add_break()" name="btn" class="btn waves-effect waves-light btn-info" >Add Break</button>
												<button type="button" onclick="round_times()" name="btn" class="btn waves-effect waves-light btn-info" >Round Times</button>												
											</div>
										</div>
									</div>
									<? } ?>
									<!-- End DIAM-1349 -->
									<div class="row">
										<div class="col-md-12">
											<table data-toggle="table" data-mobile-responsive="true" class="table-striped">
												<thead>
													<tr>
														<th >
															
														<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
															Select All
														</th>
														<th ><?=STUDENT_ID ?></th>
														<th ><?=BATCH_ID ?></th>
														<th ><?=STUDENT?></th>
														<th ><?=MESSAGE?></th>
														<th ><?=COURSE_OFFERING?></th>
														<th ><?=SCHEDULED?></th>
														<th ><?=IMPORTED?></th>
														<? /*if($_GET['t'] != 3){ ?>
															<th >
																<? if($_GET['t'] == 4) echo CLASS_START_TIME; else echo IN; ?>
															</th>
															<th >
																<? if($_GET['t'] == 4) echo CLASS_END_TIME; else echo OUT; ?>
															</th>
														<? }*/ ?>
														
														<th ><?=HOURS?></th>
														<th ><?=BREAK_IN_MIN?></th>
														<th >H:M</th>
														
														<th ><?=ATTENDANCE_CODE?></th>
														
														<? $res_set = $db->Execute("SELECT ENABLE_ATTENDANCE_ACTIVITY_TYPES FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
														if($res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){ ?>
															<th ><?=ACTIVITY_TYPE?></th>
														<? } ?>
													</tr>
												</thead>
												<tbody>
													<? $cond = "";
													if($POSTED == 0)
													{
														$cond = " AND POSTED = 0 ";
													}
													//DIAM-1349 : ATTENDANCE_HOUR = TOTAL HOUR , CHECK_IN_TIME & CHECK_OUT_TIME 
													$query = "SELECT PK_TIME_CLOCK_PROCESSOR_DETAIL, CONCAT(LAST_NAME,' ',FIRST_NAME) as NAME, MESSAGE,SCHEDULE_FOUND, CHECK_IN_DATE, CHECK_IN_TIME, CHECK_OUT_TIME, ATTENDANCE_HOUR, PK_ATTENDANCE_CODE, S_STUDENT_MASTER.PK_STUDENT_MASTER, PK_STUDENT_ENROLLMENT, PK_COURSE_OFFERING, STUDENT_ID, SUBTIME(CHECK_OUT_TIME, CHECK_IN_TIME) AS TIME_DIFF, S_TIME_CLOCK_PROCESSOR_DETAIL.BADGE_ID, BREAK_IN_MIN, NOT_FOUND_ON_FILE, PK_ATTENDANCE_ACTIVITY_TYPE, PK_COURSE_OFFERING_SCHEDULE_DETAIL   
													FROM 
													S_TIME_CLOCK_PROCESSOR_DETAIL 
													LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_TIME_CLOCK_PROCESSOR_DETAIL.PK_STUDENT_MASTER 
													WHERE 
													S_TIME_CLOCK_PROCESSOR_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_TIME_CLOCK_PROCESSOR_DETAIL.PK_TIME_CLOCK_PROCESSOR = '$_GET[id]' $cond ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME), CHECK_IN_DATE, CHECK_IN_TIME ASC  ";
													
													
													$res_clock = $db->Execute($query);
													$total = 0;
													while (!$res_clock->EOF) {  
														$PK_STUDENT_MASTER 				= $res_clock->fields['PK_STUDENT_MASTER'];
														$PK_STUDENT_ENROLLMENT 			= $res_clock->fields['PK_STUDENT_ENROLLMENT'];
														$PK_COURSE_OFFERING 			= $res_clock->fields['PK_COURSE_OFFERING'];
														$PK_TIME_CLOCK_PROCESSOR_DETAIL = $res_clock->fields['PK_TIME_CLOCK_PROCESSOR_DETAIL'];
														$DATE 							= $res_clock->fields['CHECK_IN_DATE'];
														$START_TIME						= $res_clock->fields['CHECK_IN_TIME'];
														$END_TIME						= $res_clock->fields['CHECK_OUT_TIME'];
														$BREAK_IN_MIN					= $res_clock->fields['BREAK_IN_MIN'];
														$NOT_FOUND_ON_FILE				= $res_clock->fields['NOT_FOUND_ON_FILE'];
														$SCHEDULE_FOUND					= $res_clock->fields['SCHEDULE_FOUND'];
														
														$PK_COURSE_OFFERING_SCHEDULE_DETAIL = $res_clock->fields['PK_COURSE_OFFERING_SCHEDULE_DETAIL'];
														
														$color_style = '';
														if($PK_COURSE_OFFERING == 0 || $res_clock->fields['PK_ATTENDANCE_CODE'] == 0 )
															$color_style = 'color:red !important;';
														?>
														<tr>
															<td>
																<? //if($PK_STUDENT_MASTER > 0){ ?>
																<!-- <input type="checkbox" name="DELETE_ID[]" id="DELETE_ID_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" /> -->

																<!-- DIAM-1349 -->
																<input type="checkbox" class="delete_if_not_selected" name="SELECT_ID[]" id="SELECT_ID_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" />
																<!-- End DIAM-1349 -->	
																<? //} ?>
															</td>
															<td>
																<div style="width:80px;<?=$color_style?>" ><?=$res_clock->fields['STUDENT_ID']?></div>
																
																<input type="hidden" name="PK_TIME_CLOCK_PROCESSOR_DETAIL[]" id="PK_TIME_CLOCK_PROCESSOR_DETAIL_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" />
																
																<input type="hidden" name="PK_STUDENT_MASTER_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$PK_STUDENT_MASTER?>" >
																<input type="hidden" name="DATE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$DATE?>" >
																<input type="hidden" name="START_TIME_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$START_TIME?>" >
																<input type="hidden" name="END_TIME_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$END_TIME?>" >
															</td>
															<td>
																<div style="width:80px<?=$color_style?>" ><?=$res_clock->fields['BADGE_ID']?></div>
															</td>	
															<td><div style="width:120px;<?=$color_style?>" ><?=$res_clock->fields['NAME']?></div></td>
															<td>
																<div style="width:140px;<?=$color_style?>" ><?=$res_clock->fields['MESSAGE']?></div>
															</td>
															<td>
																<?php if($_SESSION['PK_ACCOUNT']==505)
																		{
																		   $SCHEDULE_FOUND = 2;
																		}
															   ?>
																<? if($SCHEDULE_FOUND == 1){ 
																	$POST_TYPE = 1;
																	$PK_COURSE_OFFERING = $res_clock->fields['PK_COURSE_OFFERING'];
																	$res_type = $db->Execute("SELECT COURSE_CODE,SESSION,SESSION_NO, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE FROM S_COURSE_OFFERING LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION, S_COURSE WHERE S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_COURSE_OFFERING = '$PK_COURSE_OFFERING' AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE GROUP BY S_COURSE_OFFERING.PK_COURSE_OFFERING "); // Ticket # 1740 ?>
																	<div style="<?=$color_style?>" >
																		<?=$res_type->fields['COURSE_CODE'].' ('.substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION_NO'].') '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION'].' - '.$res_type->fields['TERM_BEGIN_DATE']; // Ticket # 1740  ?>
																	</div>
																	
																	<input type="hidden" name="PK_COURSE_OFFERING_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$res_clock->fields['PK_COURSE_OFFERING']?>" >
																<? } else if($PK_STUDENT_MASTER > 0) {
																	$POST_TYPE = 2; ?>
																	<select name="PK_COURSE_OFFERING_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="PK_COURSE_OFFERING_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" class="form-control" onchange="get_schedule(this.value,'<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>','<?=$DATE?>','<?=$PK_STUDENT_MASTER?>')" >
																		<option value=""></option>
																		<? $DATE_TIME 		= $DATE.' '.$START_TIME;
																		$FROM_DATE_TIME = date("Y-m-d H:i:00", strtotime("-15 minutes", strtotime($DATE_TIME)));
																		$TO_DATE_TIME 	= date("Y-m-d H:i:00", strtotime("+15 minutes", strtotime($DATE_TIME)));
																		/* Ticket # 1740 */
																		$res_type = $db->Execute("SELECT S_STUDENT_COURSE.PK_COURSE_OFFERING,COURSE_CODE,SESSION,SESSION_NO, TRANSCRIPT_CODE, COURSE_DESCRIPTION, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE FROM S_STUDENT_ENROLLMENT, S_STUDENT_COURSE, S_COURSE_OFFERING LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION , S_COURSE,  S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_COURSE.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE_DETAIL.PK_COURSE_OFFERING AND S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT AND IS_ACTIVE_ENROLLMENT = 1 GROUP BY S_STUDENT_COURSE.PK_COURSE_OFFERING ORDER BY S_TERM_MASTER.BEGIN_DATE DESC, COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ");
																		//AND S_COURSE_OFFERING.PK_COURSE_OFFERING IN ($CLOCK_PK_COURSE_OFFERING)
																		//AND CONCAT(SCHEDULE_DATE,' ',START_TIME) BETWEEN '$FROM_DATE_TIME' and '$TO_DATE_TIME'
																		while (!$res_type->EOF) { ?>
																			<option value="<?=$res_type->fields['PK_COURSE_OFFERING'] ?>" <? if($res_clock->fields['PK_COURSE_OFFERING'] == $res_type->fields['PK_COURSE_OFFERING']) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'].' ('.substr($res_type->fields['SESSION'],0,1).'-'.$res_type->fields['SESSION_NO'].') '.$res_type->fields['TRANSCRIPT_CODE'].' - '.$res_type->fields['COURSE_DESCRIPTION'].' - '.$res_type->fields['TERM_BEGIN_DATE'] ?></option>
																		<?	$res_type->MoveNext();
																		} /* Ticket # 1740 */ ?>
																	</select>
																<? } ?>
																<input type="hidden" name="POST_TYPE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="POST_TYPE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$POST_TYPE?>" >
															</td>
															<td>
																<div style="width:140px;<?=$color_style?>" id="SCHEDULE_DIV_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" >
																	<? if($SCHEDULE_FOUND == 1) {
																		$res_sch = $db->Execute("SELECT SCHEDULE_DATE, START_TIME, END_TIME FROM S_COURSE_OFFERING_SCHEDULE_DETAIL WHERE PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$PK_COURSE_OFFERING_SCHEDULE_DETAIL'  "); 
																		echo date("m/d/Y D",strtotime($res_sch->fields['SCHEDULE_DATE'])).'<br />';
																		echo date("h:i A",strtotime($res_sch->fields['START_TIME'])).' - '.date("h:i A",strtotime($res_sch->fields['END_TIME'])); ?>
																		<input type="hidden" name="PK_COURSE_OFFERING_SCHEDULE_DETAIL_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$PK_COURSE_OFFERING_SCHEDULE_DETAIL ?>" >
																		
																	<? } else if($PK_STUDENT_MASTER > 0) { 
																		$_REQUEST['PK_COURSE_OFFERING1'] = $PK_COURSE_OFFERING;
																		$_REQUEST['count_id'] 			 = $PK_TIME_CLOCK_PROCESSOR_DETAIL;
																		$_REQUEST['batch_id'] 			 = $_GET['id'];
																		$_REQUEST['def'] 			 	 = $PK_COURSE_OFFERING_SCHEDULE_DETAIL;
																		$_REQUEST['date'] 			 	 = $DATE;
																		$_REQUEST['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER; 

																		include("ajax_get_time_clock_schedule.php");
																	} ?>
																</div>
															</td>
															
															<td>
																<div style="width:140px;<?=$color_style?>" >
																	<? if($NOT_FOUND_ON_FILE == 0) {
																		if($DATE != '' && $DATE != '0000-00-00')
																			echo date("m/d/Y D",strtotime($DATE)).'<br />';
																		
																		if($res_clock->fields['CHECK_IN_TIME'] != '' && $res_clock->fields['CHECK_IN_TIME'] != '00:00:00')
																			echo date("h:i A",strtotime($res_clock->fields['CHECK_IN_TIME'])).' - '; 
																			
																		if($res_clock->fields['CHECK_OUT_TIME'] != '' && $res_clock->fields['CHECK_OUT_TIME'] != '00:00:00')
																			echo date("h:i A",strtotime($res_clock->fields['CHECK_OUT_TIME'])); 
																	} ?>
																</div>
															</td>
															
															<? /*if($_GET['t'] != 3){ ?>
																<td>
																	<div style="width:70px;<?=$color_style?>" >
																	<? if($res_clock->fields['CHECK_IN_TIME'] != '' && $res_clock->fields['CHECK_IN_TIME'] != '00:00:00')
																		echo date("h:i A",strtotime($res_clock->fields['CHECK_IN_TIME'])); ?>
																	</div>
																</td>
																<td>
																	<div style="width:70px;<?=$color_style?>" >
																	<? if($res_clock->fields['CHECK_OUT_TIME'] != '' && $res_clock->fields['CHECK_OUT_TIME'] != '00:00:00')
																		echo date("h:i A",strtotime($res_clock->fields['CHECK_OUT_TIME'])); ?>
																	</div>
																</td>
															<? }*/ ?>
															
															<td>
																<input type="text" class="" name="ATTENDANCE_HOUR_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="ATTENDANCE_HOUR_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$res_clock->fields['ATTENDANCE_HOUR']?>" style="width:70px" readonly /> 
															</td>
															<td>
																<input type="text" class="" name="BREAK_IN_MIN_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="BREAK_IN_MIN_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" value="<?=$BREAK_IN_MIN?>" style="width:100px" onchange="get_new_time(<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>)" /> 
															</td>
															<td>
																<div style="width:50px;<?=$color_style?>" id="HM_DIV_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" >
																	<? if($NOT_FOUND_ON_FILE == 0){
																		//if($_GET['t'] == 3){ 
																			/*$temp = explode(".",$res_clock->fields['ATTENDANCE_HOUR']);
																			if($temp[1] == '' || $temp[1] == 0)
																				echo $temp[0].':00';
																			else
																				echo $temp[0].':'.round((60 / (100 / $temp[1])));*/
																				
																			$_REQUEST['ATTENDANCE_HOUR'] = 	$res_clock->fields['ATTENDANCE_HOUR'];
																			$_REQUEST['BREAK_IN_MIN'] 	 = 	$BREAK_IN_MIN;
																			include("ajax_get_new_time.php");
																		/*} else {
																			echo date("H:i",strtotime($res_clock->fields['TIME_DIFF']));
																		} */
																	} else
																		echo "00:00"; ?>
																</div>
															</td>
															
															<td>
																<? if($PK_STUDENT_MASTER > 0) { ?>
																<select name="PK_ATTENDANCE_CODE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="PK_ATTENDANCE_CODE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" class="form-control <? if($POST_TYPE == 1 || ($POST_TYPE == 2 && $PK_COURSE_OFFERING_SCHEDULE_DETAIL > 0) ) //echo " required-entry "; ?> ">
																	<option value=""></option>
																	<? /* Ticket #1145  */
																	$res_type = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE, CONCAT(CODE,' - ',S_ATTENDANCE_CODE.DESCRIPTION) AS ATTENDANCE_CODE, CODE from M_ATTENDANCE_CODE, S_ATTENDANCE_CODE WHERE M_ATTENDANCE_CODE.ACTIVE = 1 AND S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND S_ATTENDANCE_CODE.ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' GROUP BY S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_ATTENDANCE_CODE'] ?>" <? if($res_clock->fields['PK_ATTENDANCE_CODE'] == $res_type->fields['PK_ATTENDANCE_CODE']) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_CODE']?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<? } ?>
															</td>
															<? if($res_set->fields['ENABLE_ATTENDANCE_ACTIVITY_TYPES'] == 1){ ?>
															<td>
																<? if($PK_STUDENT_MASTER > 0) { ?>
																<select name="PK_ATTENDANCE_ACTIVITY_TYPE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" id="PK_ATTENDANCE_ACTIVITY_TYPE_<?=$PK_TIME_CLOCK_PROCESSOR_DETAIL?>" class="form-control ">
																	<option value=""></option>
																	<? $res_type = $db->Execute("select PK_ATTENDANCE_ACTIVITY_TYPE, ATTENDANCE_ACTIVITY_TYPE from M_ATTENDANCE_ACTIVITY_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ATTENDANCE_ACTIVITY_TYPE ASC");
																	while (!$res_type->EOF) { ?>
																		<option value="<?=$res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] ?>" <? if($res_clock->fields['PK_ATTENDANCE_ACTIVITY_TYPE'] == $res_type->fields['PK_ATTENDANCE_ACTIVITY_TYPE']) echo "selected"; ?> ><?=$res_type->fields['ATTENDANCE_ACTIVITY_TYPE'] ?></option>
																	<?	$res_type->MoveNext();
																	} ?>
																</select>
																<? } ?>
															</td>
															<? } ?>
														</tr>
													<? $res_clock->MoveNext();
													} ?>
											
												</tbody>
											</table>
										</div>
                                    </div>
									<br />
									<div class="row">
										<div class="col-md-2">
											<?=IMPORTED_COUNT.': '.$IMPORTED_COUNT ?>
										</div>
										<div class="col-md-2">
											<?=TOTAL_COUNT.': '.$TOTAL_COUNT ?>
										</div>
										<div class="col-md-1">
										</div>
                                        <div class="col-md-7">
											<div class="form-group m-b-5 text-right" >
												<? if($POSTED == 0) { ?>
													<button type="button" onclick="delete_row()" name="btn" class="btn waves-effect waves-light btn-info" ><?=DELETE_SELECTED_RECORDS?></button>
													
													<button type="button" onclick="validate_form(1)" name="btn" class="btn waves-effect waves-light btn-info" ><?=SAVE?></button>
													<button type="button" onclick="validate_form(2)" name="btn" class="btn waves-effect waves-light btn-info" ><?=POST?></button>
													
													<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_time_clock_import_review'" ><?=CANCEL?></button>
												<? } ?>
												
												<input type="hidden" name="POST_ATTENDANCE" id="POST_ATTENDANCE" value="" >
												<input type="hidden" name="T_POST_TYPE" id="T_POST_TYPE" value="" >

												
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<?=DELETE_MESSAGE_GENERAL?>
							</div>
							<input type="hidden" id="STUD_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete_row(1)" class="btn waves-effect waves-light btn-info"><?=DELETE?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete_row(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>

		<!-- DIAM-1349 -->
		<div class="modal" id="addBreakModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Add Break Times</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<table class="popup-table" width="100%" cellpadding="10" cellspacing="0" >
									<tr style="border-bottom: 1px solid #c4ccd5;">
										<td width="40%"><b>Attendance Date</b></td>
										<td width="30%"><input type="text" id="attendance_date" name="attendance_date" value="" class="form-control date" /></td>
									</tr>
									<tr>
										<td width="40%"><b>Minimum In Time</b></td>
										<td align="center" width="30%"><input onkeypress="return isNumberKey(this, event)" type="text" id="min_time" name="min_time" value="4.00" class="form-control" /><span><b>Hours</b></span></td>
									</tr>
									<tr style="border-bottom: 1px solid #c4ccd5;">
										<td colspan="2">The "Minimum In Time" is how long a student is clocked in without any breaks</td>
									</tr>
									<tr>
										<td width="40%"><b>Break Begin</b></td>
										<td width="30%"><input type="text" id="break_begin" name="break_begin" value="" class="form-control timepicker" /></td>
									</tr>
									<tr>
										<td width="40%"><b>Break End</b></td>
										<td width="30%"><input type="text" id="break_end" name="break_end" value="" class="form-control timepicker" /></td>
									</tr>
									<tr>
									<td colspan="2">A break will be added to a student only if they are checked in before the break begins and clocked out after the break ends and they have been in for the "Minimum In Time"</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_add_break_time(1)" class="btn waves-effect waves-light btn-info">Add Break</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_add_break_time(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal" id="roundTimesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
			<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Round Times</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="col-12 col-sm-12 form-group">
								<table class="popup-table" width="100%" cellpadding="10" cellspacing="0" >
									<tr style="border-bottom: 1px solid #c4ccd5;">
										<td width="40%"><b>Attendance Date</b></td>
										<td width="60%"><input type="text" id="att_date" name="att_date" value="" class="form-control date" /></td>
									</tr>
									<tr>
										<td colspan="2"><b>Clock-In Time</b></td>
									</tr>
									<tr>
										<td width="40%"><b>From</b></td>
										<td width="60%">
											<table cellpadding="3">
												<tr>
													<td><input type="text" id="clock_in_from_date" name="clock_in_from_date" value="" style="width: 120px;" class="form-control timepicker" /></td>
													<td><b>to</b></td>
													<td><input type="text" id="clock_in_to_date" name="clock_in_to_date" value="" style="width: 120px;" class="form-control timepicker" /></td>
												</tr>
											</table>
										</td>
									</tr>
									<tr style="border-bottom: 1px solid #c4ccd5;">
										<td width="40%"><b>Round to</b></td>
										<td width="60%" align="center"><input type="text" id="round_to_clock_in_date" name="round_to_clock_in_date" value="" class="form-control timepicker" style="width: 125px;" /></td>
									</tr>	
									<tr>
										<td colspan="2"><b>Clock-Out Time</b></td>
									</tr>
									<tr>
										<td width="40%"><b>From</b></td>
										<td width="60%">
											<table cellpadding="3">
												<tr>
													<td><input type="text" id="clock_out_from_date" name="clock_out_from_date" value="" style="width: 120px;" class="form-control timepicker" /> </td>
													<td><b>to</b></td>
													<td><input type="text" id="clock_out_to_date" name="clock_out_to_date" value="" style="width: 120px;"  class="form-control timepicker" /></td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td width="40%"><b>Round to</b></td>
										<td width="60%" align="center"><input type="text" id="round_to_clock_out_date"  name="round_to_clock_out_date" value="" style="width: 125px;" class="form-control timepicker"/></td>
									</tr>									
									
								</table>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_add_round_time(1)" class="btn waves-effect waves-light btn-info">Round Time</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_add_round_time(0)" ><?=CANCEL?></button>
					</div>
				</div>
			</div>
		</div>
		<!-- End DIAM-1349 -->

    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.date').datepicker({
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
	});
	function fun_select_all(){
		var str = '';
		if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
			str = true;
		else
			str = false;
			
		// var DELETE_ID = document.getElementsByName('DELETE_ID[]')
		// for(var i = 0 ; i < DELETE_ID.length ; i++){
		// 	DELETE_ID[i].checked = str
		// }

		 // DIAM - 1349
		var SELECT_ID = document.getElementsByName('SELECT_ID[]')
		for(var i = 0 ; i < SELECT_ID.length ; i++){
			SELECT_ID[i].checked = str
		}
	}

	// DIAM - 1349
	function show_only_selected(){
		//RUN DELETE ONLY IF ANY SINGLE IS SELECTED  
		//alert(jQuery(".delete_if_not_selected:checked").length);
		if( jQuery(".delete_if_not_selected:checked").length> 0)
		{
			jQuery(".delete_if_not_selected:not(:checked)").parent().parent().remove();
		} 
	}
	// DIAM - 1349
	function set_validation(val,id){
		if(val == '')
			document.getElementById('PK_ATTENDANCE_CODE_'+id).className = 'form-control';
		else
			document.getElementById('PK_ATTENDANCE_CODE_'+id).className = 'form-control required-entry';
	}
	function checkbox_chk(){
			var str = '';
			var SELECT_ID = document.getElementsByName('SELECT_ID[]') 
			for(var i = 0 ; i < SELECT_ID.length ; i++){
				if(SELECT_ID[i].checked == true) {
					if(str != '')
						str += ',';
						set_validation(SELECT_ID[i].value,SELECT_ID[i].value);
					str += SELECT_ID[i].value
				}
			}
			
			if(str == ''){
				alert('Please select a record to post data.');
				return false;
			}else{
				return true;
			}
	}
	function validate_form(type){
		document.getElementById('T_POST_TYPE').value = type

		if(type == 1) {
			document.form1.submit();
		} else {
			var valid = new Validation('form1', {onSubmit:false});

			// DIAM - 1349
			if(checkbox_chk()==true) {
				var result = valid.validate();
				if(result==true){
				show_only_selected();
				document.getElementById('POST_ATTENDANCE').value = type
				
				document.form1.submit();
				}
			}
		}
	}

	// DIAM - 1349
	function add_break(){
		jQuery(document).ready(function($) {
			var str = '';
			var SELECT_ID = document.getElementsByName('SELECT_ID[]') 
			for(var i = 0 ; i < SELECT_ID.length ; i++)
			{
				if(SELECT_ID[i].checked == true) 
				{
					if(str != '')
					{
						str += ',';
					}				
					str += SELECT_ID[i].value
				}
			}
			
			if(str == '')
				alert('Please Select a Record.');
			else
				$("#addBreakModal").modal()
		});
	}

	function conf_add_break_time(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var str = '';
				var SELECT_ID = document.getElementsByName('SELECT_ID[]')
				for(var i = 0 ; i < SELECT_ID.length ; i++)
				{
					if(SELECT_ID[i].checked == true) 
					{
						if(str != '')
						{
							str += ',';
						}				
						str += SELECT_ID[i].value
					}
				}

				//Ajax Call
				jQuery.ajax({
					url: "ajax_add_break_time_clock_result",
					type: "POST",
					data: {
						act : 'add_break_time',
						t: <?=$_GET['t']?>,
						posted: <?=$POSTED?>,
						PK_TIME_CLOCK_PROCESSOR : '<?=$_GET['id']?>',
						PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS : str,
						attendance_date : $('#attendance_date').val(),
						min_time : $('#min_time').val(),
						break_begin : $('#break_begin').val(),
						break_end : $('#break_end').val(),
						DATATYPE: 'json'
					},
					async: false,
					cache: false,
					success: function(data) { 
						//console.log(data);
						if(data.success == 'success')
						{
							window.location.reload();
						}
						else if(data.error != '' && data.error !== undefined)
						{
							alert(data.error);
							return false;
						}
						else{
							window.location.reload();
						}
					}
				}).responseText;
				//End Of Ajax Call

				// window.location.href = 'time_clock_result?act=add_break_time&id=<?=$_GET['id']?>&t=<?=$_GET['t']?>&iid='+str;
			} else
				$("#addBreakModal").modal("hide");
		});
	}

	function round_times(){
		jQuery(document).ready(function($) {
			var str = '';
			var SELECT_ID = document.getElementsByName('SELECT_ID[]') 
			for(var i = 0 ; i < SELECT_ID.length ; i++)
			{
				if(SELECT_ID[i].checked == true) 
				{
					if(str != '')
					{
						str += ',';
					}				
					str += SELECT_ID[i].value
				}
			}
			
			if(str == '')
				alert('Please Select a Record.');
			else
				$("#roundTimesModal").modal()
		});
	}

	function conf_add_round_time(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var str = '';
				var SELECT_ID = document.getElementsByName('SELECT_ID[]')
				for(var i = 0 ; i < SELECT_ID.length ; i++)
				{
					if(SELECT_ID[i].checked == true) 
					{
						if(str != '')
						{
							str += ',';
						}				
						str += SELECT_ID[i].value
					}
				}

				//AJAX CALL
				jQuery.ajax({
					url: "ajax_round_off_time_clock_result",
					type: "GET",
					data: {
						act : 'add_round_time',
						POSTED : <?=$POSTED?>,
						t: <?=$_GET['t']?>,
						PK_TIME_CLOCK_PROCESSOR : '<?=$_GET['id']?>',
						PK_TIME_CLOCK_PROCESSOR_DETAIL_IDS : str,
						att_date : $('#att_date').val(),
						clock_in_from_date : $('#clock_in_from_date').val(),
						clock_in_to_date : $('#clock_in_to_date').val(),
						round_to_clock_in_date : $('#round_to_clock_in_date').val(),
						clock_out_from_date : $('#clock_out_from_date').val(),
						clock_out_to_date : $('#clock_out_to_date').val(),
						round_to_clock_out_date : $('#round_to_clock_out_date').val(),
						DATATYPE: 'json'
					},
					async: false,
					cache: false,
					success: function(data) { 
						if (data.success !== undefined) {
							//alert("Operation Successful !");
							location.reload();
						} 
						if (data.error !== undefined) {
							alert(data.error);
							location.reload();
						}
						if (data.simple_error !== undefined) {
							alert(data.simple_error); 
						}
					}
				}).responseText;
				//END OF AJAX CALL

				// window.location.href = 'time_clock_result?act=add_round_time&id=<?=$_GET['id']?>&t=<?=$_GET['t']?>&iid='+str;
			} else{
				$("#roundTimesModal").modal("hide");
			}
				
		});
	}

	function isNumberKey(txt, evt) 
	{
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode == 46) {
        //Check if the text already contains the . character
        if (txt.value.indexOf('.') === -1) {
          return true;
        } else {
          return false;
        }
      } else {
        if ( charCode > 31 && (charCode < 48 || charCode > 57) ){
		   return false;
		}
          
      }
      return true;
    }
	// End DIAM - 1349

	function delete_row(id){
		jQuery(document).ready(function($) {
			var str = '';
			var SELECT_ID = document.getElementsByName('SELECT_ID[]') 
			for(var i = 0 ; i < SELECT_ID.length ; i++){
				if(SELECT_ID[i].checked == true) {
					if(str != '')
						str += ',';
						
					str += SELECT_ID[i].value
				}
			}
			
			if(str == '')
				alert('Please Select a Record to Delete Data.');
			else
				$("#deleteModal").modal()
		});
	}
	function conf_delete_row(val){
		jQuery(document).ready(function($) {
			if(val == 1) {
				var str = '';
				var SELECT_ID = document.getElementsByName('SELECT_ID[]')
				for(var i = 0 ; i < SELECT_ID.length ; i++){
					if(SELECT_ID[i].checked == true) {
						if(str != '')
							str += ',';
							
						str += SELECT_ID[i].value
					}
				}
				window.location.href = 'time_clock_result?act=del&id=<?=$_GET['id']?>&exclude=<?=$_GET['exclude']?>&t=<?=$_GET['t']?>&iid='+str;
			} else
				$("#deleteModal").modal("hide");
		});
	}
	function get_new_time(id){
		jQuery(document).ready(function($) {
			var data = 'ATTENDANCE_HOUR='+document.getElementById('ATTENDANCE_HOUR_'+id).value+'&BREAK_IN_MIN='+document.getElementById('BREAK_IN_MIN_'+id).value;
			var value = $.ajax({
				url: "ajax_get_new_time",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					document.getElementById('HM_DIV_'+id).innerHTML 	= data;
				}
			}).responseText;
		});
	}
	
	function get_schedule(val,id,date,PK_STUDENT_MASTER){
		jQuery(document).ready(function($) {
			var data = 'PK_COURSE_OFFERING1='+val+'&count_id='+id+'&batch_id=<?=$_GET['id']?>&date='+date+'&PK_STUDENT_MASTER='+PK_STUDENT_MASTER;
			var value = $.ajax({
				url: "ajax_get_time_clock_schedule",	
				type: "POST",
				data: data,		
				async: false,
				cache :false,
				success: function (data) {
					document.getElementById('SCHEDULE_DIV_'+id).innerHTML 	= data;
				}
			}).responseText;
		});
	}

	</script>
</body>

</html>
