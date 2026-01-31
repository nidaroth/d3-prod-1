<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CAMPUS = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$EARNINGS_SETUP = $_POST;
	
	$EARNINGS_SETUP['EXCLUDED_PK_CAMPUS_PROGRAM'] 		= implode(",",$_POST['EXCLUDED_PK_CAMPUS_PROGRAM']);
	$EARNINGS_SETUP['EXCLUDED_PK_STUDENT_STATUS'] 		= implode(",",$_POST['EXCLUDED_PK_STUDENT_STATUS']);
	$EARNINGS_SETUP['INCLUDED_PK_AR_LEDGER_CODE'] 		= implode(",",$_POST['INCLUDED_PK_AR_LEDGER_CODE']);
	if($_GET['id'] == ''){
		$EARNINGS_SETUP['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$EARNINGS_SETUP['CREATED_BY'] = $_SESSION['PK_USER'];
		$EARNINGS_SETUP['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_EARNINGS_SETUP', $EARNINGS_SETUP, 'insert');
		$PK_EARNINGS_SETUP= $db->insert_ID();
	} else {
		$PK_EARNINGS_SETUP = $_GET['id'];
		$EARNINGS_SETUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$EARNINGS_SETUP['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_EARNINGS_SETUP', $EARNINGS_SETUP, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EARNINGS_SETUP = '$PK_EARNINGS_SETUP' ");
	}
	
	foreach($CAMPUS as $CAMPUS_1){
		$res = $db->Execute("SELECT PK_EARNINGS_SETUP_CAMPUS FROM S_EARNINGS_SETUP_CAMPUS WHERE PK_EARNINGS_SETUP = '$PK_EARNINGS_SETUP' AND PK_CAMPUS = '$CAMPUS_1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		$EARNINGS_SETUP_CAMPUS['PK_CAMPUS']  = $CAMPUS_1;
		if($res->RecordCount() == 0) {
			$EARNINGS_SETUP_CAMPUS['PK_EARNINGS_SETUP'] 	= $PK_EARNINGS_SETUP;
			$EARNINGS_SETUP_CAMPUS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
			$EARNINGS_SETUP_CAMPUS['CREATED_BY']  			= $_SESSION['PK_USER'];
			$EARNINGS_SETUP_CAMPUS['CREATED_ON']  			= date("Y-m-d H:i");
			db_perform('S_EARNINGS_SETUP_CAMPUS', $EARNINGS_SETUP_CAMPUS, 'insert');
			$PK_EARNINGS_SETUP_CAMPUS_ARR[] = $db->insert_ID();
		} else {
			$PK_EARNINGS_SETUP_CAMPUS_ARR[] = $res->fields['PK_EARNINGS_SETUP_CAMPUS'];
		}
	}
	
	$cond = "";
	if(!empty($PK_EARNINGS_SETUP_CAMPUS_ARR))
		$cond = " AND PK_EARNINGS_SETUP_CAMPUS NOT IN (".implode(",",$PK_EARNINGS_SETUP_CAMPUS_ARR).") ";
	
	$db->Execute("DELETE FROM S_EARNINGS_SETUP_CAMPUS WHERE PK_EARNINGS_SETUP = '$PK_EARNINGS_SETUP' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
	
	if($_GET['id'] != ''){
		header("location:earnings_setup?id=".$_GET['id']);
	}
	else{
		header("location:manage_earnings_setup");
	}
	
}
if($_GET['id'] == ''){
	$EXCLUDED_PK_CAMPUS_PROGRAM_ARR 	= array();
	$EXCLUDED_PK_STUDENT_STATUS_ARR 	= array();
	$INCLUDED_PK_AR_LEDGER_CODES_ARR 	= array();
	
	$IGNORE_FUTURE_TUITION 		= '';
	$PRORATE_FIRST_MONTH 		= '';
	$PRORATE_LOA_STATUS 		= '';
	$PRORATE_BREAKS 			= '';
	$PRORATE_CLOSURES 			= '';
	$PRORATE_HOLIDAYS 			= '';
	$ACTIVE 					= '';
	$PK_EARNING_TYPE_ID 		= '';
} else {
	$res = $db->Execute("select * from S_EARNINGS_SETUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EARNINGS_SETUP = '$_GET[id]' ");
	$EXCLUDED_PK_CAMPUS_PROGRAM_ARR 	= explode(",",$res->fields['EXCLUDED_PK_CAMPUS_PROGRAM']);
	$EXCLUDED_PK_STUDENT_STATUS_ARR 	= explode(",",$res->fields['EXCLUDED_PK_STUDENT_STATUS']);
	$INCLUDED_PK_AR_LEDGER_CODES_ARR 	= explode(",",$res->fields['INCLUDED_PK_AR_LEDGER_CODE']);

	$IGNORE_FUTURE_TUITION 		= $res->fields['IGNORE_FUTURE_TUITION'];
	$PRORATE_FIRST_MONTH 		= $res->fields['PRORATE_FIRST_MONTH'];
	$PRORATE_LOA_STATUS 		= $res->fields['PRORATE_LOA_STATUS'];
	$PRORATE_BREAKS 			= $res->fields['PRORATE_BREAKS'];
	$PRORATE_CLOSURES 			= $res->fields['PRORATE_CLOSURES'];
	$PRORATE_HOLIDAYS 			= $res->fields['PRORATE_HOLIDAYS'];
	$ACTIVE 					= $res->fields['ACTIVE'];
	$PK_EARNING_TYPE_ID			= $res->fields['PK_EARNING_TYPE'];
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
	<title><?=MNU_EARNINGS_SETUP ?> | <?=$title?></title>
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
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add "; else echo "Edit "; ?><?=MNU_EARNINGS_SETUP ?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="d-flex">
										<div class="col-6 col-sm-6 ">
											<div class="row">
												<div class="col-12 col-sm-6 focused">
													<span class="bar"></span> 
													<label for="CAMPUS"><?=CAMPUS?></label>
												</div>
												<div class="col-12 col-sm-12 form-group row" id="PK_CAMPUS_DIV" >
													<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
													while (!$res_type->EOF) { ?>
														<div class="form-group col-12 col-sm-12">
															<div class="custom-control custom-checkbox mr-sm-2">
																<? $checked = '';
																$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																$res = $db->Execute("select PK_EARNINGS_SETUP_CAMPUS FROM S_EARNINGS_SETUP_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_EARNINGS_SETUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																if($res->RecordCount() > 0)
																	$checked = 'checked';
																?>
																<input type="checkbox" class="custom-control-input" id="PK_CAMPUS_<?=$PK_CAMPUS?>" name="PK_CAMPUS[]" value="<?=$PK_CAMPUS?>" <?=$checked?> onclick="check_campus(this.value,'<?=$PK_CAMPUS?>')" >
																<label class="custom-control-label" for="PK_CAMPUS_<?=$PK_CAMPUS?>" ><?=$res_type->fields['CAMPUS_CODE']?></label>
															</div>
														</div>
													<?	$res_type->MoveNext();
													} ?>
													<div id="PK_CAMPUS_ERROR" style="color:red;display:none" >Please select at least one Campus</div>
													
												</div>
											</div>
											
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<hr style="border-color:#000" />
												</div>
											</div>
											
											<!-- <div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?//=EXCLUDED_PROGRAMS?></label>
												</div>
											</div> -->
											<!-- <div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="EXCLUDED_PK_CAMPUS_PROGRAM" name="EXCLUDED_PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
														<? //$res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
														//while (!$res_type->EOF) { 
															//$selected 			= "";
															//$PK_CAMPUS_PROGRAM 	= $res_type->fields['//PK_CAMPUS_PROGRAM']; 
															//foreach($EXCLUDED_PK_CAMPUS_PROGRAM_ARR as $EXCLUDED_PK_CAMPUS_PROGRAM){
																//if($EXCLUDED_PK_CAMPUS_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	//$selected = 'selected';
																	//break;
																//}
															//} ?>
															<option value="<?//=$PK_CAMPUS_PROGRAM?>" <?//=$selected?> ><?//=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	//$res_type->MoveNext();
														//} ?>
													</select>
												</div>
											</div> -->

											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_EARNING_TYPE" name="PK_EARNING_TYPE" class="form-control required-entry" <? if($_GET['id'] != ''){ echo 'disabled="true"'; } ?> onchange="div_enable_disable()" >
													<option value=""></option>
													<? $res_type = $db->Execute("SELECT PK_EARNING_TYPE,EARNING_TYPE FROM M_EARNING_TYPE WHERE ACTIVE = 1 ");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_EARNING_TYPE 	= $res_type->fields['PK_EARNING_TYPE']; 
																if($PK_EARNING_TYPE_ID == $PK_EARNING_TYPE) {
																	$selected = 'selected';
																}
															?>
															<option value="<?=$PK_EARNING_TYPE?>" <?=$selected?> ><?=$res_type->fields['EARNING_TYPE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="PK_EARNING_TYPE"><?=EARNINGS_TYPE?></label>
												</div>
											</div> 
											<br>
											
											<div id="enable_first">
												<div class="d-flex">
													<div class="col-12 col-sm-12 focused">
														<span class="bar"></span> 
														<label ><?=EXCLUDED_STUDENT_STATUS?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<select id="EXCLUDED_PK_STUDENT_STATUS" name="EXCLUDED_PK_STUDENT_STATUS[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
																foreach($EXCLUDED_PK_STUDENT_STATUS_ARR as $EXCLUDED_PK_STUDENT_STATUS){
																	if($EXCLUDED_PK_STUDENT_STATUS == $PK_STUDENT_STATUS) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> ><?=$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											
												<div class="d-flex">
													<div class="col-12 col-sm-12 focused">
														<span class="bar"></span> 
														<label ><?=INCLUDED_FEE_LEDGER_CODES?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<select id="INCLUDED_PK_AR_LEDGER_CODE" name="INCLUDED_PK_AR_LEDGER_CODE[]" multiple class="form-control" >
															<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by CODE ASC");
															while (!$res_type->EOF) { 
																$selected 			= "";
																$PK_AR_LEDGER_CODE 	= $res_type->fields['PK_AR_LEDGER_CODE']; 
																foreach($INCLUDED_PK_AR_LEDGER_CODES_ARR as $INCLUDED_PK_AR_LEDGER_CODE){
																	if($INCLUDED_PK_AR_LEDGER_CODE == $PK_AR_LEDGER_CODE) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?=$PK_AR_LEDGER_CODE?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-9 col-sm-9">
												</div>
												<div class="col-3 col-sm-3">
													<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
													<button type="button" onclick="window.location.href='manage_earnings_setup'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
												</div>
											</div>
											
										</div>
										
										<div class="col-md-6 form-group" id="enable_all" >

										    <div id="enable_second">
												<div class="row form-group">
													<div class="custom-control col-md-4"><?=IGNORE_FUTURE_TUITION?></div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="IGNORE_FUTURE_TUITION_1" name="IGNORE_FUTURE_TUITION"  value="1" <? if($IGNORE_FUTURE_TUITION == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="IGNORE_FUTURE_TUITION_1" ><?=YES ?></label>
													</div>
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="IGNORE_FUTURE_TUITION_2" name="IGNORE_FUTURE_TUITION" value="0"  <? if($IGNORE_FUTURE_TUITION == 0) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="IGNORE_FUTURE_TUITION_2" ><?=NO ?></label>
													</div>
												</div>
											</div>
											<div id="enable_third">
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<hr style="border-color:#000" />
													</div>
												</div>
												
												<div class="col-md-12 form-group"  >
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=PRORATE_FIRST_MONTH?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_FIRST_MONTH_1" name="PRORATE_FIRST_MONTH"  value="1" <? if($PRORATE_FIRST_MONTH == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_FIRST_MONTH_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_FIRST_MONTH_2" name="PRORATE_FIRST_MONTH" value="0"  <? if($PRORATE_FIRST_MONTH == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_FIRST_MONTH_2" ><?=NO ?></label>
														</div>
													</div>
												</div> 
											
												<div class="col-md-12 form-group"  >
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=PRORATE_LOA_STATUS?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_LOA_STATUS_1" name="PRORATE_LOA_STATUS"  value="1" <? if($PRORATE_LOA_STATUS == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_LOA_STATUS_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_LOA_STATUS_2" name="PRORATE_LOA_STATUS" value="0"  <? if($PRORATE_LOA_STATUS == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_LOA_STATUS_2" ><?=NO ?></label>
														</div>
													</div>
												</div> 
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<hr style="border-color:#000" />
													</div>
												</div>
												
												<div class="col-md-12 form-group"  >
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=PRORATE_BREAKS?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_BREAKS_1" name="PRORATE_BREAKS"  value="1" <? if($PRORATE_BREAKS == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_BREAKS_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_BREAKS_2" name="PRORATE_BREAKS" value="0"  <? if($PRORATE_BREAKS == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_BREAKS_2" ><?=NO ?></label>
														</div>
													</div>
												</div> 
											
												<div class="col-md-12 form-group"  >
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=PRORATE_CLOSURES?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_CLOSURES_1" name="PRORATE_CLOSURES"  value="1" <? if($PRORATE_CLOSURES == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_CLOSURES_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_CLOSURES_2" name="PRORATE_CLOSURES" value="0"  <? if($PRORATE_CLOSURES == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_CLOSURES_2" ><?=NO ?></label>
														</div>
													</div>
												</div> 
											
												<div class="col-md-12 form-group"  >
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=PRORATE_HOLIDAYS?></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_HOLIDAYS_1" name="PRORATE_HOLIDAYS"  value="1" <? if($PRORATE_HOLIDAYS == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_HOLIDAYS_1" ><?=YES ?></label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="PRORATE_HOLIDAYS_2" name="PRORATE_HOLIDAYS" value="0"  <? if($PRORATE_HOLIDAYS == 0) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="PRORATE_HOLIDAYS_2" ><?=NO ?></label>
														</div>
													</div>
												</div> 
											</div>

										</div> 	
									</div>
								</div>
							</form>
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

		jQuery(document).ready(function($) { 													

			div_enable_disable();											
		});

		function check_campus(campus,id){
			jQuery(document).ready(function($) { 
				//alert(document.getElementById("PK_CAMPUS_"+id).checked)
				if(document.getElementById("PK_CAMPUS_"+id).checked == true) {
					var pk_earning_type = document.getElementById("PK_EARNING_TYPE").value;
					var data  = 'campus='+campus+'&id=<?=$_GET['id']?>&pk_earning_type='+pk_earning_type;
					var value = $.ajax({
						url: "ajax_check_earnings_setup_campus",
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							if(data == 'b'){
								document.getElementById("PK_CAMPUS_"+id).checked = false
								alert('The Campus is already assigned to another Earnings Setup');
							}
						}		
					}).responseText;
				}
			});
		}

		// DIAM-21
		function div_enable_disable()
		{	
			var EARNING_TYPE = document.getElementById("PK_EARNING_TYPE").value;
			// alert(EARNING_TYPE);
			if (EARNING_TYPE == '1') // Normal
			{
				document.getElementById("enable_first").style.display = 'block';
				document.getElementById("enable_second").style.display = 'block';
				document.getElementById("enable_third").style.display = 'block';
				document.getElementById("enable_all").style.display = 'block';
				jQuery(document).ready(function($) {
					$('#enable_third input').removeAttr('disabled');
				});
			}
			else if(EARNING_TYPE == '6') // Term Block
			{
				document.getElementById("enable_first").style.display = 'block';
				document.getElementById("enable_second").style.display = 'block';
				document.getElementById("enable_third").style.display = 'none';
				jQuery(document).ready(function($) {
					$('#enable_third input').attr('disabled' , 'disabled');
				});
				document.getElementById("enable_all").style.display = 'block';
			}
			else
			{
				document.getElementById("enable_first").style.display = 'none';
				document.getElementById("enable_second").style.display = 'none';
				document.getElementById("enable_third").style.display = 'none';
				document.getElementById("enable_all").style.display = 'none';
			}
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		$('#EXCLUDED_PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		$('#EXCLUDED_PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAMS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAMS?> selected'
		});
		
		$('#INCLUDED_PK_AR_LEDGER_CODE').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=INCLUDED_FEE_LEDGER_CODES?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=INCLUDED_FEE_LEDGER_CODES?> selected'
		});
	});
	</script>
</body>

</html>