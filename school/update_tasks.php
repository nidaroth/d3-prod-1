<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student_task.php");
require_once("../language/student.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 && check_access('MANAGEMENT_BULK_UPDATE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	if($_POST['PK_TASK_TYPE'] != -1)
		$STUDENT_TASK['PK_TASK_TYPE'] = $_POST['PK_TASK_TYPE'];
	
	if($_POST['PK_TASK_STATUS'] != -1)
		$STUDENT_TASK['PK_TASK_STATUS'] = $_POST['PK_TASK_STATUS'];
	
	if($_POST['PK_EVENT_OTHER'] != -1)
		$STUDENT_TASK['PK_EVENT_OTHER'] = $_POST['PK_EVENT_OTHER'];
	
	if($_POST['PK_NOTES_PRIORITY_MASTER'] != -1)
		$STUDENT_TASK['PK_NOTES_PRIORITY_MASTER'] = $_POST['PK_NOTES_PRIORITY_MASTER'];
		
	if($_POST['COMPLETED'] != -1)
		$STUDENT_TASK['COMPLETED'] = $_POST['COMPLETED'];	
		
	if($_POST['PK_EMPLOYEE_MASTER'] != -1)
		$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] = $_POST['PK_EMPLOYEE_MASTER'];

	if($_POST['TASK_DATE'] != '')
		$STUDENT_TASK['TASK_DATE'] = date("Y-m-d",strtotime($_POST['TASK_DATE']));
		
	if($_POST['TASK_TIME'] != '')
		$STUDENT_TASK['TASK_TIME'] = date("H:i:s",strtotime($_POST['TASK_TIME']));
		
	if($_POST['FOLLOWUP_DATE'] != '')
		$STUDENT_TASK['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($_POST['FOLLOWUP_DATE']));
		
	if($_POST['FOLLOWUP_TIME'] != '')
		$STUDENT_TASK['FOLLOWUP_TIME'] = date("H:i:s",strtotime($_POST['FOLLOWUP_TIME']));
		
	if(trim($_POST['NOTES']) != '')
		$STUDENT_TASK['NOTES'] = $_POST['NOTES'];
		
	$PK_STUDENT_TASK_ARR = explode(",",$_POST['PK_STUDENT_TASK']);
	foreach($PK_STUDENT_TASK_ARR as $PK_STUDENT_TASK) {
		$STUDENT_TASK['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_TASK['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_TASK', $STUDENT_TASK, 'update', " PK_STUDENT_TASK = '$PK_STUDENT_TASK' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' " );
	}
		
	?>
	<script type="text/javascript">window.opener.close_win(this)</script>
<? } ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_UPDATE_TASKS ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper" style="padding-top: 0;" >
            <div class="container-fluid">
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<form class="floating-labels" method="post" name="form1" id="form1" >
									<div class="row">
										<div class="col-md-12" style="background-color:#022561;color:#FFFFFF;" >
											<center><b><?=MNU_UPDATE_TASKS ?></b><center>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_TASK_TYPE" name="PK_TASK_TYPE" class="form-control required-entry">
													<option value="-1" >No Update</option>
													<? $res_type = $db->Execute("select PK_TASK_TYPE,TASK_TYPE,DESCRIPTION from M_TASK_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by TASK_TYPE ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TASK_TYPE']?>" ><?=$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_TASK_TYPE">
													<?=TASK_TYPE?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_TASK_STATUS" name="PK_TASK_STATUS" class="form-control required-entry">
													<option value="-1" >No Update</option>
													<? $res_type = $db->Execute("select PK_TASK_STATUS,TASK_STATUS,DESCRIPTION from M_TASK_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by TASK_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_TASK_STATUS']?>" ><?=$res_type->fields['TASK_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_TASK_STATUS">
													<?=TASK_STATUS?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
													<option value="-1" >No Update</option>
													<? $res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by EVENT_OTHER ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_EVENT_OTHER">
													<?=TASK_OTHER?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_NOTES_PRIORITY_MASTER" name="PK_NOTES_PRIORITY_MASTER" class="form-control">
													<option value="-1" >No Update</option>
													<? $res_type = $db->Execute("select PK_NOTES_PRIORITY_MASTER,NOTES_PRIORITY from M_NOTES_PRIORITY_MASTER WHERE ACTIVE = 1 ");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_NOTES_PRIORITY_MASTER']?>" ><?=$res_type->fields['NOTES_PRIORITY']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_NOTES_PRIORITY_MASTER">
													<?=PRIORITY?>
												</label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40 " id="TASK_DATE_LABEL">
												<input type="text" class="form-control required-entry date" id="TASK_DATE" name="TASK_DATE" value="<?=$TASK_DATE?>" onchange="check_date()" >
												<span class="bar"></span>
												<label for="TASK_DATE"><?=TASK_DATE?></label>
												<div id="date_error" style="color:red" ></div>
											</div>
											
										</div>
								   
										<div class="col-md-3">
											<div class="form-group m-b-40 " id="TASK_TIME_LABEL" >
												<input type="text" class="form-control required-entry timepicker" id="TASK_TIME" name="TASK_TIME" value="<?=$TASK_TIME?>" >
												<span class="bar"></span>
												<label for="TASK_TIME"><?=TASK_TIME?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control date" id="FOLLOWUP_DATE" name="FOLLOWUP_DATE" value="<?=$FOLLOWUP_DATE?>" >
												<span class="bar"></span>
												<label for="FOLLOWUP_DATE"><?=FOLLOWUP_DATE?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control timepicker" id="FOLLOWUP_TIME" name="FOLLOWUP_TIME" value="<?=$FOLLOWUP_TIME?>" >
												<span class="bar"></span>
												<label for="FOLLOWUP_TIME"><?=TASK_TIME?></label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-9">
											<div class="form-group m-b-40">
												<textarea class="form-control" rows="2" id="NOTES" name="NOTES"><?=$NOTES?></textarea>
												<span class="bar"></span>
												<label for="NOTES"><?=COMMENTS?></label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_EMPLOYEE_MASTER" name="PK_EMPLOYEE_MASTER" class="form-control">
													<option value="-1" selected>No Update</option>
													<? if($_GET['t'] != '') {
														$PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
														$emp_cond = " AND PK_DEPARTMENT = '$PK_DEPARTMENT11' ";
													}
													
													$res_type = $db->Execute("SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER $emp_cond ) AS TEMP order by NAME ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>"  ><?=$res_type->fields['NAME']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_EMPLOYEE_MASTER">
													<?=EMPLOYEE?>
												</label>
											</div>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="COMPLETED" name="COMPLETED" class="form-control">
													<option value="-1" selected>No Update</option>
													<option value="1" >Yes</option>
													<option value="0" >No</option>
												</select>
												<span class="bar"></span> 
												<label for="COMPLETED">
													<?=COMPLETE?>
												</label>
											</div>
										</div>
										
									</div>
									
									<div class="row">
										<div class="col-md-12">
											<center>
												<button type="button" onclick="save_form()" class="btn waves-effect waves-light btn-info"><?=UPDATE?></button>
											<center>
										</div>
									</div>
									<input type="hidden" name="PK_STUDENT_TASK" id="PK_STUDENT_TASK" value="" >
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
		//var form1 = new Validation('form1');
		
		function save_form(){
			var PK_STUDENT_TASK = window.opener.document.getElementsByName('PK_STUDENT_TASK[]')
			var str = '';
			for(var i = 0 ; i < PK_STUDENT_TASK.length ; i++){
				if(PK_STUDENT_TASK[i].checked == true) {
					if(str != '')
						str += ',';
					str += PK_STUDENT_TASK[i].value
				}
			}
			document.getElementById('PK_STUDENT_TASK').value = str
			document.form1.submit();
		}
	</script>
	
	<!--  Ticket # 1593 -->
	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_EMPLOYEE_MASTER').select2();
		});
	</script>
	<!--  Ticket # 1593 -->
</body>

</html>