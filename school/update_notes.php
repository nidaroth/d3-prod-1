<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/notes.php");
require_once("../language/student.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_REGISTRAR') == 0 && check_access('MANAGEMENT_BULK_UPDATE') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	if($_POST['PK_NOTE_TYPE'] != -1)
		$STUDENT_NOTES['PK_NOTE_TYPE'] = $_POST['PK_NOTE_TYPE'];
	
	if($_POST['PK_NOTE_STATUS'] != -1)
		$STUDENT_NOTES['PK_NOTE_STATUS'] = $_POST['PK_NOTE_STATUS'];
	
	if($_POST['PK_EVENT_OTHER'] != -1)
		$STUDENT_NOTES['PK_EVENT_OTHER'] = $_POST['PK_EVENT_OTHER'];
	
	if($_POST['PK_EMPLOYEE_MASTER'] != -1)
		$STUDENT_NOTES['PK_EMPLOYEE_MASTER'] = $_POST['PK_EMPLOYEE_MASTER'];
		
	if($_POST['SATISFIED'] != -1)
		$STUDENT_NOTES['SATISFIED'] = $_POST['SATISFIED'];	

	if($_POST['NOTE_DATE'] != '')
		$STUDENT_NOTES['NOTE_DATE'] = date("Y-m-d",strtotime($_POST['NOTE_DATE']));
		
	if($_POST['NOTE_TIME'] != '')
		$STUDENT_NOTES['NOTE_TIME'] = date("H:i:s",strtotime($_POST['NOTE_TIME']));
		
	if($_POST['FOLLOWUP_DATE'] != '')
		$STUDENT_NOTES['FOLLOWUP_DATE'] = date("Y-m-d",strtotime($_POST['FOLLOWUP_DATE']));
		
	if($_POST['FOLLOWUP_TIME'] != '')
		$STUDENT_NOTES['FOLLOWUP_TIME'] = date("H:i:s",strtotime($_POST['FOLLOWUP_TIME']));
		
	if(trim($_POST['NOTES']) != '')
		$STUDENT_NOTES['NOTES'] = $_POST['NOTES'];
		
	$PK_STUDENT_NOTES_ARR = explode(",",$_POST['PK_STUDENT_NOTES']);
	foreach($PK_STUDENT_NOTES_ARR as $PK_STUDENT_NOTES) {
		$STUDENT_NOTES['EDITED_BY']  = $_SESSION['PK_USER'];
		$STUDENT_NOTES['EDITED_ON']  = date("Y-m-d H:i");
		db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'update', " PK_STUDENT_NOTES = '$PK_STUDENT_NOTES' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' " );
	}
		
	?>
	<script type="text/javascript">window.opener.close_win(this)</script>
<? } 
$title1 = MNU_UPDATE_NOTES;
if($_GET['event'] == 1)
	$title1 = MNU_UPDATE_EVENTS; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=$title1 ?> | <?=$title?></title>
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
											<center><b><?=$title1 ?></b><center>
										</div>
									</div>
									<br />
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<?  $cond = " AND TYPE = 1 ";
												if($_GET['event'] == 1)
													$cond = " AND TYPE = 2 ";
													
												$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
												$cond .= " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
												
												$res_type = $db->Execute("select PK_NOTE_TYPE,NOTE_TYPE,DESCRIPTION from M_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by NOTE_TYPE ASC"); ?>
												<select id="PK_NOTE_TYPE" name="PK_NOTE_TYPE" class="form-control">
													<option value="-1" selected>No Update</option>
													<? while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_NOTE_TYPE']?>" <? if($res_type->fields['PK_NOTE_TYPE'] == $PK_NOTE_TYPE) echo "selected"; ?> ><?=$res_type->fields['NOTE_TYPE'].' - '.$res_type->fields['DESCRIPTION']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_NOTE_TYPE">
													<? if($_GET['event'] == 1) echo EVENT_TYPE; else echo NOTES_TYPE; ?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40">
												<select id="PK_NOTE_STATUS" name="PK_NOTE_STATUS" class="form-control">
													<option value="-1" selected>No Update</option>
													<? $cond = " AND TYPE = 2 ";
													if($_GET['event'] == 1)
														$cond = " AND TYPE = 3 ";
														
													$PK_DEPARTMENT = get_department_from_t($_GET['t']);	
													$cond .= " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
													
													$res_type = $db->Execute("select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by NOTE_STATUS ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_NOTE_STATUS']?>" <? if($res_type->fields['PK_NOTE_STATUS'] == $PK_NOTE_STATUS) echo "selected"; ?> ><?=$res_type->fields['NOTE_STATUS']?></option>
													<?	$res_type->MoveNext();
													} ?>
												</select>
												<span class="bar"></span> 
												<label for="PK_NOTE_STATUS">
													<? if($_GET['event'] == 1) echo EVENT_STATUS; else echo NOTE_STATUS;?>
												</label>
											</div>
										</div>
										
										<? if($_GET['event'] == 1) { ?>
											<div class="col-md-3">
												<div class="form-group m-b-40">
													<select id="PK_EVENT_OTHER" name="PK_EVENT_OTHER" class="form-control">
														<option value="-1" selected>No Update</option>
														<? //Ticket # 901
														$cond = " AND (PK_DEPARTMENT = '$PK_DEPARTMENT' OR PK_DEPARTMENT = -1) ";
														$res_type = $db->Execute("select PK_EVENT_OTHER,EVENT_OTHER,DESCRIPTION from M_EVENT_OTHER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 $cond  order by EVENT_OTHER ASC");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_EVENT_OTHER']?>" <? if($PK_EVENT_OTHER == $res_type->fields['PK_EVENT_OTHER']) echo "selected"; ?> ><?=$res_type->fields['EVENT_OTHER'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_EVENT_OTHER">
														<?=EVENT_OTHER?>
													</label>
												</div>
											</div>
										<? } ?>
									</div>
									
									<div class="row">
										<div class="col-md-3">
											<div class="form-group m-b-40" id="NOTE_DATE_LABEL" >
												<input type="text" class="form-control date" id="NOTE_DATE" name="NOTE_DATE" value="<?=$NOTE_DATE?>" >
												<span class="bar"></span>
												<label for="NOTE_DATE">
													<? if($_GET['event'] == 1) echo EVENT_DATE; else echo NOTE_DATE;?>
												</label>
											</div>
										</div>
										
										<div class="col-md-3">
											<div class="form-group m-b-40" id="NOTE_TIME_LABEL" >
												<input type="text" class="form-control timepicker" id="NOTE_TIME" name="NOTE_TIME" value="<?=$NOTE_TIME?>" >
												<span class="bar"></span>
												<label for="NOTE_TIME">
													<? if($_GET['event'] == 1) echo EVENT_TIME; else echo NOTE_TIME;?>
												</label>
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
												<label for="FOLLOWUP_TIME"><?=TIME?></label>
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
													<? $PK_DEPARTMENT11 = get_department_from_t($_GET['t']);
													$emp_cond = " AND PK_DEPARTMENT = '$PK_DEPARTMENT11' ";
													
													$res_type = $db->Execute("SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER $emp_cond 
													UNION 
													select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER') AS TEMP order by NAME ASC");
													while (!$res_type->EOF) { ?>
														<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($PK_EMPLOYEE_MASTER == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
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
												<select id="SATISFIED" name="SATISFIED" class="form-control">
													<option value="-1" selected>No Update</option>
													<option value="1" >Yes</option>
													<option value="0" >No</option>
												</select>
												<span class="bar"></span> 
												<label for="SATISFIED">
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
									<input type="hidden" name="PK_STUDENT_NOTES" id="PK_STUDENT_NOTES" value="" >
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
			var PK_STUDENT_NOTES = window.opener.document.getElementsByName('PK_STUDENT_NOTES[]')
			var str = '';
			for(var i = 0 ; i < PK_STUDENT_NOTES.length ; i++){
				if(PK_STUDENT_NOTES[i].checked == true) {
					if(str != '')
						str += ',';
					str += PK_STUDENT_NOTES[i].value
				}
			}
			document.getElementById('PK_STUDENT_NOTES').value = str
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