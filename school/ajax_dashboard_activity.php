<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/dashboard.php");

if($_REQUEST['PK_EMPLOYEE_MASTER'] != '')
	$task_cond = " AND S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_REQUEST[PK_EMPLOYEE_MASTER]' ";
else
	$task_cond = " AND (S_STUDENT_TASK.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' OR PK_SUPERVISOR = '$_SESSION[PK_EMPLOYEE_MASTER]' ) ";

$task_date[] = " AND TASK_DATE < '".date("Y-m-d")."' ";
$task_title[] = PAST_DUE; 

$task_date[]  = " AND TASK_DATE = '".date("Y-m-d")."' ";
$task_title[] = DUE_TODAY; 

$task_date[]  = " AND TASK_DATE BETWEEN '".date("Y-m-d", strtotime("+1 days", strtotime(date("Y-m-d"))))."' AND '".date("Y-m-d", strtotime("+7 days", strtotime(date("Y-m-d"))))."'  ";
$task_title[] = DUE_7; ?>
<div style="height: 400px;overflow-y: auto;">
	<ul class="nav nav-tabs customtab" role="tablist">
		<? $i = 0;
		foreach($task_title as $title){ ?>
			<li class="nav-item"> <a class="nav-link <? if($i == 0) echo "active"; ?>" data-toggle="tab" href="#activitiesTab_<?=$i?>" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?=$title?></span></a> </li>
		<? $i++;
		} ?>
	</ul>
	
	<div class="tab-content">
		<? $i = 0;
		foreach($task_date as $date_cond){ ?>
		<div class="tab-pane <? if($i == 0) echo "active"; ?>" id="activitiesTab_<?=$i?>" role="tabpanel">
			<div class="to-do-widget m-t-20" id="todo" style="height: 300px;position: relative;">
				<ul class="list-task todo-list list-group m-b-0" data-role="tasklist">
					<? $date = date("Y-m-d");
					$res_type = $db->Execute("select PK_STUDENT_ENROLLMENT,PK_STUDENT_TASK,TASK_TIME, TASK_TYPE,TASK_STATUS,NOTES ,IF(TASK_DATE = '0000-00-00', '',  DATE_FORMAT(TASK_DATE,'%m/%d/%Y')) AS TASK_DATE1,S_STUDENT_TASK.PK_STUDENT_MASTER, S_STUDENT_TASK.PK_DEPARTMENT, IF(FOLLOWUP_DATE = '0000-00-00', '',  DATE_FORMAT(FOLLOWUP_DATE,'%m/%d/%Y')) AS FOLLOWUP_DATE, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS EMP_NAME  
					FROM 
					S_STUDENT_TASK 
					LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_STUDENT_TASK.PK_EMPLOYEE_MASTER 
					LEFT JOIN M_TASK_TYPE ON M_TASK_TYPE.PK_TASK_TYPE = S_STUDENT_TASK.PK_TASK_TYPE 
					LEFT JOIN M_TASK_STATUS ON M_TASK_STATUS.PK_TASK_STATUS = S_STUDENT_TASK.PK_TASK_STATUS 
					WHERE S_STUDENT_TASK.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND COMPLETED = 0  $task_cond $date_cond ORDER BY PK_STUDENT_TASK DESC "); 
					while (!$res_type->EOF) { 
						$PK_STUDENT_MASTER = $res_type->fields['PK_STUDENT_MASTER'];
						$res_stud = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) as STU_NAME FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ");
						$TASK_TIME = '';
						if($res_type->fields['TASK_TIME'] != '00-00-00') 
							$TASK_TIME = date("h:i A", strtotime($res_type->fields['TASK_TIME'])); 
							
						if($res_type->fields['PK_DEPARTMENT'] == -1) 
							$t = 2;
						else { 
							$res_dep = $db->Execute("SELECT PK_DEPARTMENT_MASTER FROM M_DEPARTMENT WHERE PK_DEPARTMENT = '".$res_type->fields['PK_DEPARTMENT']."' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
							$PK_DEPARTMENT_MASTER = $res_dep->fields['PK_DEPARTMENT_MASTER'];
							
							$edit_flag = 0;
							if($PK_DEPARTMENT_MASTER == 2) {
								//admission
								$t = 1;
							} else if($PK_DEPARTMENT_MASTER == 7) {
								//Registrar
								$t = 2;
							} else if($PK_DEPARTMENT_MASTER == 4) {
								//Finance
								$t = 3;
							} else if($PK_DEPARTMENT_MASTER == 1) {
								//Accounting
								$t = 5;
							} else if($PK_DEPARTMENT_MASTER == 6) {
								//Placement
								$t = 6;
							}
						} ?>
						<li class="list-group-item" data-role="task">
							<a href="student_task?sid=<?=$res_type->fields['PK_STUDENT_MASTER']?>&id=<?=$res_type->fields['PK_STUDENT_TASK']?>&eid=<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>&t=<?=$t?>&p=i" >
							<span>
								<b style="font-weight: bold;" ><?=EMPLOYEE_NAME?>: </b><?=$res_type->fields['EMP_NAME'] ?><br />
								
								<b style="font-weight: bold;" ><?=DUE?>: </b><?=$res_type->fields['TASK_DATE1'].' '.$TASK_TIME?>&nbsp;<b style="font-weight: bold;"><?=STATUS?>: </b><?=$res_type->fields['TASK_STATUS']?><br />
								
								<b style="font-weight: bold;"><?=STUDENT?>: </b><?=$res_stud->fields['STU_NAME']?> &nbsp;<b style="font-weight: bold;"><?=TYPE?>: </b><?=$res_type->fields['TASK_TYPE']?><br />
								
								<?=$res_type->fields['NOTES']?><br />
							</span> 
							</a>
							<hr />
						</li>
					<?	$res_type->MoveNext();
					} 
					if($res_type->RecordCount() == 0) { ?>
					<li class="list-group-item" data-role="task">
						<span>
							<?=NO_TASK_DUE?>
						</span> 
					</li>
					<? } ?>
				</ul>
			</div>
		</div>
		<? $i++;
		} ?>
	</div>
</div>