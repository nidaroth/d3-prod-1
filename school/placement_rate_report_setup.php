<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/placement_rate_report.php");

require_once("check_access.php");

if(check_access('MANAGEMENT_PLACEMENT') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$res = $db->Execute("select * from S_PLACEMENT_RATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$PLACEMENT_RATE['PK_CAMPUS_PROGRAM'] 				= implode(",",$_POST['PK_CAMPUS_PROGRAM']);
	$PLACEMENT_RATE['EXCLUDED_PK_PLACEMENT_STATUS'] 	= implode(",",$_POST['EXCLUDED_PK_PLACEMENT_STATUS']);
	$PLACEMENT_RATE['PLACED_PK_PLACEMENT_STATUS'] 		= implode(",",$_POST['PLACED_PK_PLACEMENT_STATUS']);
	
	if($res->RecordCount() == 0){
		$PLACEMENT_RATE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$PLACEMENT_RATE['CREATED_BY'] = $_SESSION['PK_USER'];
		$PLACEMENT_RATE['CREATED_ON'] = date("Y-m-d H:i:s");
		$PLACEMENT_RATE['EDITED_BY']  = $_SESSION['PK_USER'];
		$PLACEMENT_RATE['EDITED_ON']  = date("Y-m-d H:i:s");
		db_perform('S_PLACEMENT_RATE', $PLACEMENT_RATE, 'insert');
		$PK_PLACEMENT_RATE = $db->insert_ID();
	} else {
		$PLACEMENT_RATE['EDITED_BY'] = $_SESSION['PK_USER'];
		$PLACEMENT_RATE['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('S_PLACEMENT_RATE', $PLACEMENT_RATE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_PLACEMENT_RATE = $_GET['id'];
	}
	header("location:placement_rate_report_setup");
}
$res = $db->Execute("select * from S_PLACEMENT_RATE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
$PK_CAMPUS_PROGRAM_ARR 				= explode(",",$res->fields['PK_CAMPUS_PROGRAM']);
$EXCLUDED_PK_PLACEMENT_STATUS_ARR 	= explode(",",$res->fields['EXCLUDED_PK_PLACEMENT_STATUS']);
$PLACED_PK_PLACEMENT_STATUS_ARR 	= explode(",",$res->fields['PLACED_PK_PLACEMENT_STATUS']);

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
	<title><?=MNU_PLACEMENT_RATE_REPORT_SETUP?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
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
                        <h4 class="text-themecolor"><?=MNU_PLACEMENT_RATE_REPORT_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="row">
										<div class="col-md-6 ">
											
											<div class="row d-flex">
												<div class="col-12 col-sm-1"></div>
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAMS?></label>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PK_CAMPUS_PROGRAM" name="PK_CAMPUS_PROGRAM[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,PROGRAM_TRANSCRIPT_CODE,DESCRIPTION, ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['CODE']." - ".$res_type->fields['PROGRAM_TRANSCRIPT_CODE']." - ".$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($PK_CAMPUS_PROGRAM_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PLACEMENT_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_PK_PLACEMENT_STATUS" name="EXCLUDED_PK_PLACEMENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, M_PLACEMENT_STATUS.ACTIVE, IF(EMPLOYED = '1', 'Yes', 'No') as EMPLOYED, PLACEMENT_STUDENT_STATUS_CATEGORY from M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by M_PLACEMENT_STATUS.ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS']." - ".$res_type->fields['PLACEMENT_STUDENT_STATUS_CATEGORY']." - Employed(".$res_type->fields['EMPLOYED'].')';
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($EXCLUDED_PK_PLACEMENT_STATUS_ARR as $EXCLUDED_PK_PLACEMENT_STATUS){
																if($EXCLUDED_PK_PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
										</div>
										
										<div class="col-md-6 ">
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PLACED_PLACEMENT_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED_PK_PLACEMENT_STATUS" name="PLACED_PK_PLACEMENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_PLACEMENT_STATUS, PLACEMENT_STATUS, M_PLACEMENT_STATUS.ACTIVE, IF(EMPLOYED = '1', 'Yes', 'No') as EMPLOYED, PLACEMENT_STUDENT_STATUS_CATEGORY from M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by M_PLACEMENT_STATUS.ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$option_label 		= $res_type->fields['PLACEMENT_STATUS']." - ".$res_type->fields['PLACEMENT_STUDENT_STATUS_CATEGORY']." - Employed(".$res_type->fields['EMPLOYED'].')';
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)";
																
															$selected 				= "";
															$PK_PLACEMENT_STATUS 	= $res_type->fields['PK_PLACEMENT_STATUS']; 
															foreach($PLACED_PK_PLACEMENT_STATUS_ARR as $PLACED_PK_PLACEMENT_STATUS){
																if($PLACED_PK_PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_PLACEMENT_STATUS?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
										</div>
									</div>
									
									<div class="row">
										<div class="col-3 col-sm-3">
										</div>
										<div class="col-3 col-sm-3">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='placement_rate_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
										</div>
										
										<div class="col-3 col-sm-3">
											<button type="button" onclick="window.location.href='placement_rate_report'"  class="btn waves-effect waves-light btn-info" ><?=GO_TO_REPORT?></button>
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
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAMS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAMS?> selected'
		});
		
		$('#EXCLUDED_PK_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PLACEMENT_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PLACEMENT_STUDENT_STATUS?> selected'
		});
		
		$('#PLACED_PK_PLACEMENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=PLACED_PLACEMENT_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=PLACED_PLACEMENT_STUDENT_STATUS?> selected'
		});
	});
	</script>
	
</body>

</html>