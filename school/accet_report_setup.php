<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/accet.php");
require_once("get_department_from_t.php");
require_once("check_access.php");
if(check_access('MANAGEMENT_ACCREDITATION') == 0 ){
	header("location:../index");
	exit;
}
$msg = '';	
if(!empty($_POST))
{


 	//exclusion
	$ACICS_ARRAY['EXCLUDED_PROGRAMS']  = implode(",",$_POST['EXCLUDED_PROGRAMS']);
	$ACICS_ARRAY['EXCLUDED_STUDENT_STATUS'] = implode(",",$_POST['EXCLUDEDStudentStatus']);
	//student status
	$ACICS_ARRAY['COMPLETIONS']   = implode(",",$_POST['COMPLETIONS']);
	//drop reasons
	$ACICS_ARRAY['COMPLETION_WAIVER']   = implode(",",$_POST['COMPLETION_WAIVER']);
	//Placement status
	$ACICS_ARRAY['DROPPED_PLACED_IN_RELATED_POSITION']    = implode(",",$_POST['DROPPED_PLACED_IN_RELATED_POSITION']);
	$ACICS_ARRAY['PLACEMENT_WAIVER']  = implode(",",$_POST['PLACEMENT_WAIVER']);
	$ACICS_ARRAY['PLACED']   = implode(",",$_POST['PLACED']);

    $res = $db->Execute("SELECT * FROM ACCET_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	if($res->RecordCount() == 0){
		$ACICS_ARRAY['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
		$ACICS_ARRAY['CREATED_BY'] = $_SESSION['PK_USER'];
		$ACICS_ARRAY['CREATED_ON'] = date("Y-m-d H:i:s");
		db_perform('ACCET_SETUP ', $ACICS_ARRAY, 'insert');
		$PK_S_ACICS_SETUP = $db->insert_ID();
	} else {
		$ACICS_ARRAY['EDITED_BY'] = $_SESSION['PK_USER'];
		$ACICS_ARRAY['EDITED_ON'] = date("Y-m-d H:i:s");
		db_perform('ACCET_SETUP ', $ACICS_ARRAY, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$PK_S_ACICS_SETUP = $_GET['id'];
	}
	header("location:accet_report_setup");
}

$res = $db->Execute("SELECT * FROM ACCET_SETUP  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
//exclusions
$EXCLUDED_PROGRAMS_ARR = explode(",",$res->fields['EXCLUDED_PROGRAMS']);
$EXCLUDEDStudentStatus_ARR = explode(",",$res->fields['EXCLUDED_STUDENT_STATUS']);
//student status
$COMPLETIONS_ARR = explode(",",$res->fields['COMPLETIONS']);
//drop reasons
$COMPLETION_WAIVER_ARR = explode(",",$res->fields['COMPLETION_WAIVER']);
//placement status
$DROPPED_PLACED_IN_RELATED_POSITION_ARR = explode(",",$res->fields['DROPPED_PLACED_IN_RELATED_POSITION']);
$PLACEMENT_WAIVER_ARR = explode(",",$res->fields['PLACEMENT_WAIVER']);
$PLACED_ARR = explode(",",$res->fields['PLACED']);
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
	<title><?=ACCET_DOC_SETUP?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
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
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor"><?=ACCET_DOC_SETUP?></h4>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-12">
                        <div class="card" style="margin-bottom: 0px !important;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">  
                                    </div>
                                    <div class="col-md-6" style="text-align: right;">    
                                        <button type="button" onclick="window.location.href='accet_report'" class="btn waves-effect waves-light btn-info">Go To Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card">
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" >
								<div class="p-20">
									<div class="row">
								<div class="col-md-4 ">

										<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUSIONS?></label>
												</div>
											</div>
											<br />
										
											<div class="row d-flex">
												<div class="col-12 col-sm-1"></div>
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_PROGRAM?></label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													
													<select id="EXCLUDED_PROGRAM" name="EXCLUDED_PROGRAMS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION,ACTIVE from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAMS_ARR as $EXCLUDED_PROGRAM_VAL){
																if($EXCLUDED_PROGRAM_VAL == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															}
															 $option_label = $res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
															
															<!-- <option value="<?//=$PK_CAMPUS_PROGRAM?>" <?//=$selected?> ><?//=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option> -->

														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=EXCLUDED_STUDENT_STATUS?></label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="EXCLUDED_STUDENT_STATUS" name="EXCLUDEDStudentStatus[]" multiple class="form-control" >
													<? $res_type_ess = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ess->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ess->fields['PK_STUDENT_STATUS']; 
													foreach($EXCLUDEDStudentStatus_ARR as $EXCLUDED_STUDENT_STATUS){
														if($EXCLUDED_STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ess->fields['STUDENT_STATUS'].' - '.$res_type_ess->fields['DESCRIPTION'];
													if($res_type_ess->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ess->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ess->MoveNext();
												} ?>
													</select>
												</div>
											</div>

										



										</div>

										<!-- Fixed column issue -->
										<div  class="col-md-4">
										<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=STUDENT_STATUS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Completions</label>
												</div>
											</div>
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="COMPLETIONS" name="COMPLETIONS[]" multiple class="form-control" >
													<? $res_type_ss = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION, ACTIVE from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND ADMISSIONS = 0 order by ACTIVE DESC, STUDENT_STATUS ASC");
													while (!$res_type_ss->EOF) { 
													$selected 			= "";
													$PK_STUDENT_STATUS 	= $res_type_ss->fields['PK_STUDENT_STATUS']; 
													foreach($COMPLETIONS_ARR as $STUDENT_STATUS){
														if($STUDENT_STATUS == $PK_STUDENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 
													
													$option_label = $res_type_ss->fields['STUDENT_STATUS'].' - '.$res_type_ss->fields['DESCRIPTION'];
													if($res_type_ss->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; ?>
													<option value="<?=$PK_STUDENT_STATUS?>" <?=$selected?> <? if($res_type_ss->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type_ss->MoveNext();
												} ?>
													</select>
												</div>
											</div>


											


										<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=DROP_REASONS?></label>
												</div>
											</div>
											<br />

											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Completion Waiver</label>
												</div>
											</div>
											
											<div class="row d-flex">
											<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="COMPLETION_WAIVER" name="COMPLETION_WAIVER[]" multiple class="form-control" >
													<? $res_type = $db->Execute("select PK_DROP_REASON,DROP_REASON,DESCRIPTION,ACTIVE from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DROP_REASON ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_DROP_REASON 	= $res_type->fields['PK_DROP_REASON']; 
															foreach($COMPLETION_WAIVER_ARR as $PK_DROP_REASON_1){
																if($PK_DROP_REASON_1 == $PK_DROP_REASON) {
																	$selected = 'selected';
																	break;
																}
															} 	 
															$option_label = $res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION'];
															if($res_type->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
															<option value="<?=$PK_DROP_REASON?>" <?=$selected?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>

															<!-- <option value="<?//=$PK_DROP_REASON?>" <?//=$selected?> ><?//=$res_type->fields['DROP_REASON'].' - '.$res_type->fields['DESCRIPTION']?></option> -->
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>

										</div>
										<!--- placment status starts -->
										<div class="col-md-4 ">										
											<div class="row d-flex">
												<div class="col-12 col-sm-11 focused">
													<span class="bar"></span> 
													<label ><?=PLACEMENT_STATUS?></label>
												</div>
											</div>
											<br />
																		
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Dropped Placed In Related Position</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="DROPPED_PLACED_IN_RELATED_POSITION" name="DROPPED_PLACED_IN_RELATED_POSITION[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
														while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
														foreach($DROPPED_PLACED_IN_RELATED_POSITION_ARR as $PLACEMENT_STATUS){
															if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?	$res_type_placement->MoveNext();
													} ?>
													</select>
												</div>
											</div>	
											<!-- ============ -->
											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Placement Waiver</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACEMENT_WAIVER" name="PLACEMENT_WAIVER[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
														$selected 			= "";
														$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 

														foreach($PLACEMENT_WAIVER_ARR as $PLACEMENT_STATUS){
															if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
																$selected = 'selected';
																break;
															}
														} 	
														$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
													<?	$res_type_placement->MoveNext();
													} ?>
													</select>
												</div>
											</div>			

											<div class="row d-flex">
												<div class="col-1 col-sm-1"></div>
												<div class="col-11 col-sm-11 focused">
													<span class="bar"></span> 
													<label >Placed</label>
												</div>
											</div>
											<div class="row d-flex">
												<div class="col-11 col-sm-1"></div>
												<div class="col-11 col-sm-11 form-group">
													<select id="PLACED" name="PLACED[]" multiple class="form-control" >
													<? $res_type_placement = $db->Execute("select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, PLACEMENT_STATUS ASC");
													while (!$res_type_placement->EOF) { 
													$selected 			= "";
													$PK_PLACEMENT_STATUS 	= $res_type_placement->fields['PK_PLACEMENT_STATUS']; 
													foreach($PLACED_ARR as $PLACEMENT_STATUS){
														if($PLACEMENT_STATUS == $PK_PLACEMENT_STATUS) {
															$selected = 'selected';
															break;
														}
													} 	
													$option_label = $res_type_placement->fields['PLACEMENT_STATUS'];
															if($res_type_placement->fields['ACTIVE'] == 0)
																$option_label .= " (Inactive)"; ?>
														
														<option value="<?=$res_type_placement->fields['PK_PLACEMENT_STATUS']?>" <?=$selected?> <? if($res_type_placement->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?=$option_label?></option>
												<?	$res_type_placement->MoveNext();
												} ?>
													</select>
												</div>
											</div>		
										</div>
									</div>
									
									<div class="row">
										<div class="col-4 col-sm-4">
										</div>
										<div class="col-8 col-sm-8">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											<button type="button" onclick="window.location.href='accet_report'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
					
		$('#EXCLUDED_PROGRAM').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAM?> selected'
		});		
		$('#EXCLUDED_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_STUDENT_STATUS?> selected'
		});
		
		//Status
		$('#COMPLETIONS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Completions',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Completions selected'
		});
		// drop resons
		$('#COMPLETION_WAIVER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Completion Waiver',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Completion Waiver selected'
		});
				
		//placement status
		$('#DROPPED_PLACED_IN_RELATED_POSITION').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Dropped Placed In Related Position',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Dropped Placed In Related Position selected'
		});
		$('#PLACEMENT_WAIVER').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Placement Waiver',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Placement Waiver selected'
		});
		$('#PLACED').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All Placed',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: 'Placed selected'
		});
	
	});
	</script>
</body>

</html>