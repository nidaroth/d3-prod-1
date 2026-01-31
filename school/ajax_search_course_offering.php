<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}


$cond 		= "";
	
//if($_REQUEST['PK_CAMPUS'] != '')
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN ($_REQUEST[PK_CAMPUS]) ";
	
//if($_REQUEST['PK_TERM_MASTER'] != '')
	$cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER IN ($_REQUEST[PK_TERM_MASTER]) ";
	
if($_REQUEST['PK_SESSION'] != '')
	$cond .= " AND S_COURSE_OFFERING.PK_SESSION IN ($_REQUEST[PK_SESSION]) ";

$res_co_1 = $db->Execute("SELECT S_COURSE_OFFERING.PK_COURSE_OFFERING,COURSE_CODE, LMS_CODE, IF(S_TERM_MASTER.BEGIN_DATE != '0000-00-00',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE,'%m/%d/%Y'),'') AS TERM_BEGIN_DATE, CAMPUS_CODE ,CONCAT(EMP_INSTRUCTOR.FIRST_NAME,' ',EMP_INSTRUCTOR.LAST_NAME) AS INSTRUCTOR_NAME,SESSION,SESSION_NO, CONCAT(ROOM_NO,' - ', ROOM_DESCRIPTION) AS ROOM_NO, IF(S_COURSE_OFFERING.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, IF(S_COURSE_OFFERING.LMS_ACTIVE = 1, 'Yes', 'No') as LMS_ACTIVE, COURSE_OFFERING_STATUS, S_COURSE_OFFERING.INSTRUCTOR, S_COURSE_OFFERING.PK_CAMPUS, S_COURSE_OFFERING.PK_COURSE, S_COURSE_OFFERING.PK_CAMPUS_ROOM, S_COURSE_OFFERING.PK_SESSION, S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS, DEF_END_TIME, DEF_START_TIME, S_COURSE_OFFERING_SCHEDULE.START_DATE, S_COURSE_OFFERING_SCHEDULE.END_DATE  
FROM 
S_COURSE_OFFERING 
LEFT JOIN S_COURSE_OFFERING_SCHEDULE ON S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING 
LEFT JOIN M_COURSE_OFFERING_STATUS ON M_COURSE_OFFERING_STATUS.PK_COURSE_OFFERING_STATUS = S_COURSE_OFFERING.PK_COURSE_OFFERING_STATUS 
LEFT JOIN M_CAMPUS_ROOM ON M_CAMPUS_ROOM.PK_CAMPUS_ROOM = S_COURSE_OFFERING.PK_CAMPUS_ROOM  
LEFT JOIN S_COURSE ON S_COURSE_OFFERING.PK_COURSE = S_COURSE.PK_COURSE 
LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER 
LEFT JOIN M_SESSION ON M_SESSION.PK_SESSION = S_COURSE_OFFERING.PK_SESSION 
LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_COURSE_OFFERING.PK_CAMPUS 
LEFT JOIN S_EMPLOYEE_MASTER AS EMP_INSTRUCTOR ON EMP_INSTRUCTOR.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR 
WHERE 
S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ORDER BY COURSE_CODE ASC, SESSION ASC, SESSION_NO ASC ");	
?>
<div class="">
	<div class="row">
        <div class="col-12" style="text-align:right" >
			<b><?=TOTAL_COUNT.': '.$res_co_1->RecordCount() ?></b>
		</div>
		<div class="col-12" style="text-align:right" >
			<b><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT">0</span></b>
		</div>
	</div>
	<table class="table table-hover lessPadding" >
		<thead>
			<tr>
				<th>
					<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
				</th>
				<th><?=CAMPUS?></th>
				<th><?=TERM?></th>
				<th><?=COURSE_CODE?></th>
				<th><?=ROOM?></th>
				<th><?=SESSION?></th>
				<th><?=SESSION_NUMBER?></th>
				<th><?=COURSE_OFFERING_STATUS?></th>
				<th>Custom<br />Start & <br />End Time</th>
				<th><?=START_TIME?></th>
				<th><?=END_TIME?></th>
				<th>Use<br />Default<br />Schedule</th>
				<th><?=START_DATE?></th>
				<th><?=END_DATE?></th>
				<th><?=INSTRUCTOR?></th>
			</tr>
		</thead>
		<tbody>
			<? while (!$res_co_1->EOF) { 
				$PK_CAMPUS 						= $res_co_1->fields['PK_CAMPUS']; 
				$PK_COURSE 						= $res_co_1->fields['PK_COURSE'];
				$PK_COURSE_OFFERING 			= $res_co_1->fields['PK_COURSE_OFFERING'];
				$PK_CAMPUS_ROOM 				= $res_co_1->fields['PK_CAMPUS_ROOM'];
				$PK_SESSION 					= $res_co_1->fields['PK_SESSION']; 
				$PK_COURSE_OFFERING_STATUS 		= $res_co_1->fields['PK_COURSE_OFFERING_STATUS']; 
				$DEF_START_TIME 				= $res_co_1->fields['DEF_START_TIME']; 
				$DEF_END_TIME 					= $res_co_1->fields['DEF_END_TIME']; 
				$START_DATE 					= $res_co_1->fields['START_DATE']; 
				$END_DATE 						= $res_co_1->fields['END_DATE']; 
				$INSTRUCTOR						= $res_co_1->fields['INSTRUCTOR']; 
				
				if($DEF_START_TIME != '' && $DEF_START_TIME != '00:00:00')
					$DEF_START_TIME = date("h:i A",strtotime($DEF_START_TIME));
				else
					$DEF_START_TIME = '';
					
				if($DEF_END_TIME != '' && $DEF_END_TIME != '00:00:00')
					$DEF_END_TIME = date("h:i A",strtotime($DEF_END_TIME));
				else
					$DEF_END_TIME = '';
					
				if($START_DATE != '' && $START_DATE != '0000-00-00')
					$START_DATE = date("m/d/Y",strtotime($START_DATE));
				else
					$START_DATE = '';
					
				if($END_DATE != '' && $END_DATE != '0000-00-00')
					$END_DATE = date("m/d/Y",strtotime($END_DATE));
				else
					$END_DATE = '';
				?>
				<tr>
					<th>
						<input type="checkbox" name="PK_COURSE_OFFERING[]" id="PK_COURSE_OFFERING_<?=$PK_COURSE_OFFERING ?>" value="<?=$PK_COURSE_OFFERING ?>" onclick="get_count()" style="margin-top:15px" />
					</th>
					<td >
						<div style="margin-top:10px"><?=$res_co_1->fields['CAMPUS_CODE']?></div>
						<input type="hidden" name="PK_CAMPUS_<?=$PK_COURSE_OFFERING?>"  id="PK_CAMPUS_<?=$PK_COURSE_OFFERING?>" value="<?=$PK_CAMPUS ?>" />
					</td>
					<td ><div style="margin-top:10px"><?=$res_co_1->fields['TERM_BEGIN_DATE']?></div></td>
					<td >
						<? $res_type = $db->Execute("select S_COURSE.PK_COURSE, COURSE_CODE FROM S_COURSE, S_COURSE_CAMPUS WHERE S_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE.PK_COURSE = S_COURSE_CAMPUS.PK_COURSE AND PK_CAMPUS = '$PK_CAMPUS' AND (S_COURSE.ACTIVE = 1 OR S_COURSE.PK_COURSE = '$PK_COURSE' ) ORDER BY COURSE_CODE ASC ");  ?>
						<select id="PK_COURSE_<?=$PK_COURSE_OFFERING?>" name="PK_COURSE_<?=$PK_COURSE_OFFERING?>" class="form-control " style="padding: 0px;width:130px;" >
							<? while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['PK_COURSE'] == $PK_COURSE) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'] ?></option>
								<? $res_type->MoveNext();
							} ?>
						</select>
					</td>
					<td >
						<select id="PK_CAMPUS_ROOM_<?=$PK_COURSE_OFFERING?>" name="PK_CAMPUS_ROOM_<?=$PK_COURSE_OFFERING?>" class="form-control" style="padding: 0px;width:80px;" >
							<option value=""></option>
							<? $camp_cond = " AND ACTIVE = 1 ";
							if($PK_CAMPUS_ROOM > 0)
								$camp_cond = " AND (ACTIVE = 1 OR PK_CAMPUS_ROOM IN ($PK_CAMPUS_ROOM) )";
								
							$res_type = $db->Execute("select PK_CAMPUS_ROOM,ROOM_NO, ROOM_DESCRIPTION from M_CAMPUS_ROOM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS) $camp_cond order by ROOM_NO ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_CAMPUS_ROOM'] ?>" <? if($PK_CAMPUS_ROOM == $res_type->fields['PK_CAMPUS_ROOM']) echo "selected"; ?> ><?=$res_type->fields['ROOM_NO'] ?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</td>
					<td >
						<select id="PK_SESSION_<?=$PK_COURSE_OFFERING?>" name="PK_SESSION_<?=$PK_COURSE_OFFERING?>" class="form-control " style="padding: 0px;width:100px;" >
							<option value="" ></option>
							<? $act_type_cond = " AND ACTIVE = 1 ";
							if($PK_SESSION > 0)
								$act_type_cond = " AND (ACTIVE = 1 OR PK_SESSION = '$PK_SESSION') ";
								
							$res_type = $db->Execute("select PK_SESSION,SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $act_type_cond order by DISPLAY_ORDER ASC");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_SESSION'] ?>" <? if($PK_SESSION == $res_type->fields['PK_SESSION']) echo "selected"; ?> ><?=$res_type->fields['SESSION']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</td>
					<td >
						<input type="text" class="form-control " style="padding: 2px;width:60px;" name="SESSION_NO_<?=$PK_COURSE_OFFERING?>"  id="SESSION_NO_<?=$PK_COURSE_OFFERING?>" value="<?=$res_co_1->fields['SESSION_NO']?>" readonly />
					</td>
					<td >
						<select id="PK_COURSE_OFFERING_STATUS_<?=$PK_COURSE_OFFERING?>" style="padding: 0px;width:100px;" name="PK_COURSE_OFFERING_STATUS_<?=$PK_COURSE_OFFERING?>" class="form-control " >
							<option value="" ></option>
							<? $act_type_cond = " AND ACTIVE = 1 ";
							if($PK_COURSE_OFFERING_STATUS > 0)
								$act_type_cond = " AND (ACTIVE = 1 OR PK_COURSE_OFFERING_STATUS = '$PK_COURSE_OFFERING_STATUS') ";
								
							$res_type = $db->Execute("select PK_COURSE_OFFERING_STATUS,COURSE_OFFERING_STATUS from M_COURSE_OFFERING_STATUS WHERE 1 = 1 $act_type_cond order by COURSE_OFFERING_STATUS ASC"); 
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_COURSE_OFFERING_STATUS'] ?>" <? if($PK_COURSE_OFFERING_STATUS == $res_type->fields['PK_COURSE_OFFERING_STATUS']) echo "selected"; ?> ><?=$res_type->fields['COURSE_OFFERING_STATUS']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					</td>
					
					<td >
						<select id="CUSTOM_START_END_TIME_<?=$PK_COURSE_OFFERING?>" name="CUSTOM_START_END_TIME_<?=$PK_COURSE_OFFERING?>" class="form-control" onchange="disable_default_sch(this.value, '<?=$PK_COURSE_OFFERING?>', '<?=$DEF_START_TIME?>', '<?=$DEF_END_TIME ?>', '<?=$START_DATE?>', '<?=$END_DATE ?>')" style="padding: 0px;width:70px;" >
							<option value="1" >Yes</option>
							<option value="2" >No</option>
						</select>
					</td>
					<td >
						<input type="text" class="form-control timepicker" name="DEF_START_TIME_<?=$PK_COURSE_OFFERING?>"  id="DEF_START_TIME_<?=$PK_COURSE_OFFERING?>" value="<?=$DEF_START_TIME?>" style="padding: 0px;width:75px;" />
					</td>
					<td >
						<input type="text" class="form-control timepicker" name="DEF_END_TIME_<?=$PK_COURSE_OFFERING?>"  id="DEF_END_TIME_<?=$PK_COURSE_OFFERING?>" value="<?=$DEF_END_TIME ?>" style="padding: 0px;width:75px;" />
					</td>
					
					<td >
						<select id="USE_DEFAULT_SCHEDULE_<?=$PK_COURSE_OFFERING?>" name="USE_DEFAULT_SCHEDULE_<?=$PK_COURSE_OFFERING?>" class="form-control " onchange="disable_default_time(this.value, '<?=$PK_COURSE_OFFERING?>', '<?=$DEF_START_TIME?>', '<?=$DEF_END_TIME ?>', '<?=$START_DATE?>', '<?=$END_DATE ?>')" disabled style="padding: 0px;width:60px;" >
							<option value="1" >Yes</option>
							<option value="2" selected >No</option>
						</select>
					</td>
					<td >
						<input type="text" class="form-control date" name="START_DATE_<?=$PK_COURSE_OFFERING?>" id="START_DATE_<?=$PK_COURSE_OFFERING?>" value="<?=$START_DATE?>" disabled style="padding: 1px;width:90px;" />
					</td>
					<td >
						<input type="text" class="form-control date" name="END_DATE_<?=$PK_COURSE_OFFERING?>" id="END_DATE_<?=$PK_COURSE_OFFERING?>" value="<?=$END_DATE?>" disabled style="padding: 1px;width:90px;" />
					</td>
					
					<td >
						<? $_REQUEST['campus'] 		= $PK_CAMPUS;
						$_REQUEST['id'] 			= 'INSTRUCTOR_'.$PK_COURSE_OFFERING;
						$_REQUEST['SELECTED_VALUE'] = $INSTRUCTOR;
						include("ajax_get_teacher_from_campus.php"); ?>
					</td>
				</tr>
			<?	$res_co_1->MoveNext();
			} ?>
		</tbody>
	</table>
</div>