<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/notification_settings.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_EMPLOYEE_MASTER_ARR = $_POST['PK_EMPLOYEE_MASTER'];
	unset($_POST['PK_EMPLOYEE_MASTER']);
	
	$PK_CAMPUS_PROGRAM_ARR = $_POST['PK_CAMPUS_PROGRAM'];
	unset($_POST['PK_CAMPUS_PROGRAM']);
	
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$EVENT_TEMPLATE = $_POST;
	$EVENT_TEMPLATE['RECIPIENTS_DEPARTMENT'] = implode(",",$_POST['RECIPIENTS_DEPARTMENT']);
	
	$EVENT_TEMPLATE['CREATE_TASK'] 			 = $_POST['CREATE_TASK'];
	$EVENT_TEMPLATE['MARK_TASK_AS_COMPLETE'] = $_POST['MARK_TASK_AS_COMPLETE'];
	if($EVENT_TEMPLATE['CREATE_TASK'] == '') {
		$EVENT_TEMPLATE['PK_TASK_TYPE'] 		 = '';
		$EVENT_TEMPLATE['MARK_TASK_AS_COMPLETE'] = '';
	}
	if($_GET['id'] == ''){
		$EVENT_TEMPLATE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$EVENT_TEMPLATE['CREATED_BY']  = $_SESSION['PK_USER'];
		$EVENT_TEMPLATE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'insert');
		$PK_EVENT_TEMPLATE = $db->Insert_ID();
	} else {
		$PK_EVENT_TEMPLATE = $_GET['id'];
		$EVENT_TEMPLATE['EDITED_BY'] = $_SESSION['PK_USER'];
		$EVENT_TEMPLATE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_EVENT_TEMPLATE', $EVENT_TEMPLATE, 'update'," PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}

	foreach($PK_EMPLOYEE_MASTER_ARR as $PK_EMPLOYEE_MASTER){
		$res = $db->Execute("SELECT PK_EVENT_TEMPLATE_RECIPIENTS FROM S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' "); 
		if($res->RecordCount() == 0) {
			$TEMPLATE_RECIPIENTS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$TEMPLATE_RECIPIENTS['PK_EVENT_TEMPLATE'] 	= $PK_EVENT_TEMPLATE;
			$TEMPLATE_RECIPIENTS['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
			$TEMPLATE_RECIPIENTS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$TEMPLATE_RECIPIENTS['CREATED_ON'] 			= date("Y-m-d H:i");
			db_perform('S_EVENT_TEMPLATE_RECIPIENTS', $TEMPLATE_RECIPIENTS, 'insert');
			$PK_EVENT_TEMPLATE_RECIPIENTS_ARR[] = $db->insert_ID();
		} else
			$PK_EVENT_TEMPLATE_RECIPIENTS_ARR[] = $res->fields['PK_EVENT_TEMPLATE_RECIPIENTS'];
	}
	
	$cond = "";
	if(!empty($PK_EVENT_TEMPLATE_RECIPIENTS_ARR))
		$cond = " AND PK_EVENT_TEMPLATE_RECIPIENTS NOT IN (".implode(",",$PK_EVENT_TEMPLATE_RECIPIENTS_ARR).") ";
		
	$db->Execute("DELETE FROM S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	foreach($PK_CAMPUS_PROGRAM_ARR as $PK_CAMPUS_PROGRAM){
		$res = $db->Execute("SELECT PK_EVENT_TEMPLATE_PROGRAM FROM S_EVENT_TEMPLATE_PROGRAM WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' "); 
		if($res->RecordCount() == 0) {
			$TEMPLATE_PROGRAM['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
			$TEMPLATE_PROGRAM['PK_EVENT_TEMPLATE'] 	= $PK_EVENT_TEMPLATE;
			$TEMPLATE_PROGRAM['PK_CAMPUS_PROGRAM'] 	= $PK_CAMPUS_PROGRAM;
			$TEMPLATE_PROGRAM['CREATED_BY']  		= $_SESSION['PK_USER'];
			$TEMPLATE_PROGRAM['CREATED_ON'] 		= date("Y-m-d H:i");
			db_perform('S_EVENT_TEMPLATE_PROGRAM', $TEMPLATE_PROGRAM, 'insert');
			$PK_EVENT_TEMPLATE_PROGRAM_ARR[] = $db->insert_ID();
		} else
			$PK_EVENT_TEMPLATE_PROGRAM_ARR[] = $res->fields['PK_EVENT_TEMPLATE_PROGRAM'];
	}
	
	$cond = "";
	if(!empty($PK_EVENT_TEMPLATE_PROGRAM_ARR))
		$cond = " AND PK_EVENT_TEMPLATE_PROGRAM NOT IN (".implode(",",$PK_EVENT_TEMPLATE_PROGRAM_ARR).") ";
		
	$db->Execute("DELETE FROM S_EVENT_TEMPLATE_PROGRAM WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_EVENT_TEMPLATE_CAMPUS FROM S_EVENT_TEMPLATE_CAMPUS WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$TEMPLATE_CAMPUS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$TEMPLATE_CAMPUS['PK_EVENT_TEMPLATE'] 	= $PK_EVENT_TEMPLATE;
			$TEMPLATE_CAMPUS['PK_CAMPUS'] 			= $PK_CAMPUS;
			$TEMPLATE_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$TEMPLATE_CAMPUS['CREATED_ON'] 			= date("Y-m-d H:i");
			db_perform('S_EVENT_TEMPLATE_CAMPUS', $TEMPLATE_CAMPUS, 'insert');
			$PK_EVENT_TEMPLATE_CAMPUS_ARR[] = $db->insert_ID();
		} else
			$PK_EVENT_TEMPLATE_CAMPUS_ARR[] = $res->fields['PK_EVENT_TEMPLATE_CAMPUS'];
	}
	
	$cond = "";
	if(!empty($PK_EVENT_TEMPLATE_CAMPUS_ARR))
		$cond = " AND PK_EVENT_TEMPLATE_CAMPUS NOT IN (".implode(",",$PK_EVENT_TEMPLATE_CAMPUS_ARR).") ";
		
	$db->Execute("DELETE FROM S_EVENT_TEMPLATE_CAMPUS WHERE PK_EVENT_TEMPLATE = '$PK_EVENT_TEMPLATE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
	
	header("location:manage_notification_settings");
}
if($_GET['id'] == ''){
	$PK_EVENT_TYPE 				= '';
	$CONTENT 					= '';
	$ACTIVE	 					= '';
	$PK_TASK_TYPE 				= '';
	$CREATE_TASK 				= '';
	$MARK_TASK_AS_COMPLETE 		= '';
	$PK_TEXT_SETTINGS 			= '';
	$RECIPIENTS_DEPARTMENT_ARR	= array();
} else {
	$res = $db->Execute("SELECT * FROM S_EVENT_TEMPLATE WHERE PK_EVENT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	if($res->RecordCount() == 0){
		header("location:manage_notification_settings");
		exit;
	}
	
	$PK_EVENT_TYPE 				= $res->fields['PK_EVENT_TYPE'];
	$CONTENT 					= $res->fields['CONTENT'];
	$ACTIVE  					= $res->fields['ACTIVE'];
	$PK_TASK_TYPE 				= $res->fields['PK_TASK_TYPE'];
	$CREATE_TASK 				= $res->fields['CREATE_TASK'];
	$MARK_TASK_AS_COMPLETE 		= $res->fields['MARK_TASK_AS_COMPLETE'];
	$PK_TEXT_SETTINGS 			= $res->fields['PK_TEXT_SETTINGS'];
	$RECIPIENTS_DEPARTMENT_ARR	= explode(",",$res->fields['RECIPIENTS_DEPARTMENT']);
}
$PK_CAMPUS_ARRAY = array();
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
	<title><?=NOTIFICATION_SETTINGS_PAGE_TITLE ?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                        <h4 class="text-themecolor"><?=NOTIFICATION_SETTINGS_PAGE_TITLE ?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-6">
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<select name="PK_EVENT_TYPE" id="PK_EVENT_TYPE" class="form-control required-entry" onchange="show_program(this.value)" >
															<option value=""></option>
															<? $res_dd = $db->Execute("select * from Z_EVENT_TYPE WHERE ACTIVE = '1' AND PK_EVENT_TYPE NOT IN (7,8,9,10,11,12,13,14,15,16) ORDER BY EVENT_TYPE ASC ");
															while (!$res_dd->EOF) { ?>
																<option value="<?=$res_dd->fields['PK_EVENT_TYPE']?>" <? if($res_dd->fields['PK_EVENT_TYPE'] == $PK_EVENT_TYPE) echo 'selected = "selected"';?> ><?=$res_dd->fields['EVENT_TYPE']?></option>
															<?	$res_dd->MoveNext();
															}	?>
														</select>
														<span class="bar"></span>
														<label for="PK_EVENT_TYPE"><?=EVENT_TYPE?></label>
													</div>
												</div>
											</div>
											
											<? $style = "display:none;";
											if($PK_EVENT_TYPE == 2) $style = "display:block;"; ?>
											
											<div style="<?=$style?>" id="program_div" >
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="PROGRAM"><?=PROGRAM?></label>
												</div>
												<div class="row" >
													<div class="form-group col-12 col-sm-12">
														<select name="PK_CAMPUS_PROGRAM[]" id="PK_CAMPUS_PROGRAM" class="" multiple>
															<? $res_type = $db->Execute("SELECT M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM, CONCAT(CODE,' - ',DESCRIPTION) AS CODE FROM M_CAMPUS_PROGRAM WHERE M_CAMPUS_PROGRAM.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  M_CAMPUS_PROGRAM.ACTIVE = 1 GROUP BY M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM ORDER BY CONCAT(CODE,' - ',DESCRIPTION) ASC"); 
															while (!$res_type->EOF) {
																$selected = '';
																$PK_CAMPUS_PROGRAM = $res_type->fields['PK_CAMPUS_PROGRAM'];
															
																$res = $db->Execute("select PK_EVENT_TEMPLATE_PROGRAM FROM S_EVENT_TEMPLATE_PROGRAM WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM' AND PK_EVENT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																if($res->RecordCount() > 0)
																	$selected = 'selected';
																?>
																<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'] ?></option>
															
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<textarea class="form-control required-entry" rows="1" id="CONTENT" name="CONTENT"><?=$CONTENT?></textarea>
														<span class="bar"></span>
														<label for="CONTENT"><?=MESSAGE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="TAGS"><?=TAGS?></label>
												</div>
												<div class="col-md-12">
													<div class="form-group m-b-40">
														{Student Name} {Task Type} {Task Status} {Text Message}
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="CREATE_TASK" name="CREATE_TASK" value="1" <? if($CREATE_TASK == 1) echo "checked"; ?> onclick="show_type()" >
															<label class="custom-control-label" for="CREATE_TASK"><?=CREATE_TASK?></label>
														</div>
													</div>
												</div>
												
												<div class="col-12 col-sm-6" id="PK_TASK_TYPE_DIV" <? if($CREATE_TASK != 1) { ?> style="display:none" <? } ?> >
													<div class="form-group m-b-40">
														<select id="PK_TASK_TYPE" name="PK_TASK_TYPE" class="form-control required-entry">
															<option></option>
															<? $res_type = $db->Execute("select PK_TASK_TYPE,TASK_TYPE,DESCRIPTION from M_TASK_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by TASK_TYPE ASC");
															while (!$res_type->EOF) { ?>
																<option value="<?=$res_type->fields['PK_TASK_TYPE']?>" <? if($PK_TASK_TYPE == $res_type->fields['PK_TASK_TYPE']) echo "selected"; ?> ><?=$res_type->fields['TASK_TYPE'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
														<span class="bar"></span> 
														<label for="PK_TASK_TYPE">
															<?=TASK_TYPE?>
														</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-6">
													<div class="d-flex">
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="MARK_TASK_AS_COMPLETE" name="MARK_TASK_AS_COMPLETE" value="1" <? if($MARK_TASK_AS_COMPLETE == 1) echo "checked"; ?> onclick="show_type()" >
															<label class="custom-control-label" for="MARK_TASK_AS_COMPLETE"><?=MARK_TASK_AS_COMPLETE?></label>
														</div>
													</div>
												</div>
											</div>
											
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-6">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ACTIVE?></div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-3">
															<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="customRadio22"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											<? } ?>
										</div>
										<div class="col-md-6">
											<div class="col-12 col-sm-6 focused">
												<span class="bar"></span> 
												<label for="CAMPUS"><?=CAMPUS?></label>
											</div>
											<div class="row" >
												<div class="form-group col-12 col-sm-12">
													<select name="PK_CAMPUS[]" id="PK_CAMPUS" class="" multiple onchange="get_employee()" >
														<? $res_type = $db->Execute("SELECT OFFICIAL_CAMPUS_NAME, PK_CAMPUS FROM S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  ACTIVE = 1 ORDER BY OFFICIAL_CAMPUS_NAME ASC"); 
														while (!$res_type->EOF) {
															$selected = '';
															$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
														
															$res = $db->Execute("select PK_EVENT_TEMPLATE_CAMPUS FROM S_EVENT_TEMPLATE_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_EVENT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															if($res->RecordCount() > 0 || ($res_type->RecordCount() == 1 && $_GET['id'] == '')) { //Ticket #849 
																$selected = 'selected';
																$PK_CAMPUS_ARRAY[] = $PK_CAMPUS;
															} ?>
															<option value="<?=$PK_CAMPUS?>" <?=$selected?> ><?=$res_type->fields['OFFICIAL_CAMPUS_NAME'] ?></option>
														
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<? $style = "display:none;";
											if($PK_EVENT_TYPE == 17)
												$style = ""; ?>
											<div class="row" id="PK_TEXT_SETTINGS_DIV" style="<?=$style?>" >
												<div class="form-group col-12 col-sm-12">
													<? $res_type = $db->Execute("select PK_TEXT_SETTINGS,FROM_NO from S_TEXT_SETTINGS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND FROM_NO != ''"); ?>
													<div class="row form-group">
														<div class="col-md-6 align-self-center">
															<select id="PK_TEXT_SETTINGS" name="PK_TEXT_SETTINGS" class="form-control <? if($PK_EVENT_TYPE == 17){ ?> required-entry <? } ?>" >
																<option></option>
																<? while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_TEXT_SETTINGS']?>" <? if($PK_TEXT_SETTINGS == $res_type->fields['PK_TEXT_SETTINGS']) echo "selected"; ?> ><?=$res_type->fields['FROM_NO'] ?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_TEXT_SETTINGS">
																<?=FROM_NO?>
															</label>
														</div>
													</div>
												</div>
											</div>
											
											<div >
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label for="DEPARTMENT"><?=DEPARTMENT ?></label>
												</div>
												
												<div class="row" >
													<div class="form-group col-12 col-sm-12">
														<select name="RECIPIENTS_DEPARTMENT[]" id="RECIPIENTS_DEPARTMENT" class="" multiple>
															<? $res_type = $db->Execute("SELECT PK_DEPARTMENT, DEPARTMENT FROM M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ORDER BY DEPARTMENT "); 
															while (!$res_type->EOF) {
																$selected = '';
																$PK_DEPARTMENT = $res_type->fields['PK_DEPARTMENT'];
																
																foreach($RECIPIENTS_DEPARTMENT_ARR as $RECIPIENTS_DEPARTMENT) {
																	if($RECIPIENTS_DEPARTMENT == $PK_DEPARTMENT) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_DEPARTMENT?>" <?=$selected?> ><?=$res_type->fields['DEPARTMENT'] ?></option>
															
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>
											
											<div class="col-12 col-sm-6 focused">
												<span class="bar"></span> 
												<label for="RECIPIENTS"><?=RECIPIENTS?></label>
											</div>
											<!--
											<div class="col-12 col-sm-12 form-group row" >
												<? /*$res_type = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) AS NAME, EMPLOYEE_ID FROM S_EMPLOYEE_MASTER WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND  S_EMPLOYEE_MASTER.ACTIVE = 1 ORDER BY CONCAT(FIRST_NAME,' ',MIDDLE_NAME,' ',LAST_NAME) ASC"); 
												while (!$res_type->EOF) { ?>
													<div class="form-group col-6 col-sm-6">
														<div class="custom-control custom-checkbox mr-sm-2" style="min-height: 36px;" >
															<? $checked = '';
															$PK_EMPLOYEE_MASTER = $res_type->fields['PK_EMPLOYEE_MASTER'];
															
															$dep = '';
															$res = $db->Execute("select DEPARTMENT FROM M_DEPARTMENT,S_EMPLOYEE_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
															while (!$res->EOF) {
																if($dep != '')
																	$dep .= ', ';
																	
																$dep .= $res->fields['DEPARTMENT'];
																$res->MoveNext();
															}
															
															$res = $db->Execute("select PK_EVENT_TEMPLATE_RECIPIENTS FROM S_EVENT_TEMPLATE_RECIPIENTS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_EVENT_TEMPLATE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
															if($res->RecordCount() > 0)
																$checked = 'checked';
															?>
															<input type="checkbox" class="custom-control-input" id="PK_EMPLOYEE_MASTER_<?=$PK_EMPLOYEE_MASTER?>" name="PK_EMPLOYEE_MASTER[]" value="<?=$PK_EMPLOYEE_MASTER?>" <?=$checked?> >
															<label class="custom-control-label" for="PK_EMPLOYEE_MASTER_<?=$PK_EMPLOYEE_MASTER?>" ><?=$res_type->fields['NAME']?><br /><span style="font-size:10px" ><?=$dep?></span></label>
														</div>
													</div>
												<?	$res_type->MoveNext();
												} */ ?>
											</div>
											-->
											
											<div class="row" >
												<div class="form-group col-12 col-sm-12" id="PK_EMPLOYEE_MASTER_DIV" >
													<? $_REQUEST['PK_EVENT_TEMPLATE'] 	= $_GET['id'];
													$_REQUEST['PK_CAMPUS'] 				= implode(",",$PK_CAMPUS_ARRAY);
													include("ajax_get_employee_from_campus_for_notification.php"); ?>
												</div>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_notification_settings?t=<?=$_GET['t']?>'" ><?=CANCEL?></button>
												
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
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>

	<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" rel="stylesheet" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.select').select2();
		});
		
		function show_type(){
			if(document.getElementById('CREATE_TASK').checked == true)
				document.getElementById('PK_TASK_TYPE_DIV').style.display = 'block';
			else
				document.getElementById('PK_TASK_TYPE_DIV').style.display = 'none';
		}
		function show_program(val){
			if(val == 2) {
				document.getElementById('program_div').style.display = 'block';
			} else {
				document.getElementById('program_div').style.display = 'none';
			}
			
			if(val == 17) {
				document.getElementById('PK_TEXT_SETTINGS_DIV').style.display 	= 'block';
				document.getElementById('PK_TEXT_SETTINGS').classList.add("required-entry");
			} else {
				document.getElementById('PK_TEXT_SETTINGS_DIV').style.display 	= 'none';
				document.getElementById('PK_TEXT_SETTINGS').classList.remove("required-entry");
			}
		}
		
		function get_employee(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&id=<?=$_GET['id']?>';
				var value = $.ajax({
					url: "ajax_get_employee_from_campus_for_notification",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById('PK_EMPLOYEE_MASTER_DIV').innerHTML = data;
						
						$('#PK_EMPLOYEE_MASTER').multiselect({
							includeSelectAllOption: true,
							allSelectedText: 'All <?=RECIPIENTS?>',
							nonSelectedText: '',
							numberDisplayed: 2,
							nSelectedText: '<?=RECIPIENTS?> selected', //Ticket # 1593
							enableCaseInsensitiveFiltering: true, //Ticket # 1593
						});
					}		
				}).responseText;
			});
		}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_EMPLOYEE_MASTER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=RECIPIENTS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=RECIPIENTS?> selected', //Ticket # 1593
			enableCaseInsensitiveFiltering: true, //Ticket # 1593
		});
		
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=PROGRAM?> selected'
		});
		
		$('#RECIPIENTS_DEPARTMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DEPARTMENT?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=DEPARTMENT?> selected'
		});
		
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>
	
</body>

</html>