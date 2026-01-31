<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/ar_leder_code.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT COE,ECM,_1098T,_90_10,IPEDS,POPULATION_REPORT FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['_90_10'] == 0 || check_access('MANAGEMENT_90_10') == 0){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	/*foreach($_POST['PK_STUDENT_STATUS'] as $PK_STUDENT_STATUS){
		$res = $db->Execute("SELECT PK_90_10_EXCLUDED_STUDENT_STATUS FROM S_90_10_EXCLUDED_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$PK_STUDENT_STATUS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
		if($res->RecordCount() == 0){
			$EXCLUDED_STUDENT_STATUS['PK_STUDENT_STATUS']  	= $PK_STUDENT_STATUS;
			$EXCLUDED_STUDENT_STATUS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$EXCLUDED_STUDENT_STATUS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$EXCLUDED_STUDENT_STATUS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_90_10_EXCLUDED_STUDENT_STATUS', $EXCLUDED_STUDENT_STATUS, 'insert');
			$PK_90_10_EXCLUDED_STUDENT_STATUS_ARR[] = $db->insert_ID();
		} else 
			$PK_90_10_EXCLUDED_STUDENT_STATUS_ARR[] = $res->fields['PK_90_10_EXCLUDED_STUDENT_STATUS'];
	}
	
	$cond = "";
	if(!empty($PK_90_10_EXCLUDED_STUDENT_STATUS_ARR))
		$cond = " AND PK_90_10_EXCLUDED_STUDENT_STATUS NOT IN (".implode(",",$PK_90_10_EXCLUDED_STUDENT_STATUS_ARR).") ";

	$res = $db->Execute("DELETE FROM S_90_10_EXCLUDED_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); */

	$EXCLUDED_STUDENT_STATUS['PK_STUDENT_STATUS']  	= implode(",",$_POST['PK_STUDENT_STATUS']);
	$EXCLUDED_STUDENT_STATUS['EXCLUDED_PROGRAMS']  	= implode(",",$_POST['EXCLUDED_PROGRAMS']);
	
	$res = $db->Execute("SELECT PK_90_10_EXCLUDED_STUDENT_STATUS FROM S_90_10_EXCLUDED_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		$EXCLUDED_STUDENT_STATUS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
		$EXCLUDED_STUDENT_STATUS['CREATED_BY']  		= $_SESSION['PK_USER'];
		$EXCLUDED_STUDENT_STATUS['CREATED_ON']  		= date("Y-m-d H:i");
		db_perform('S_90_10_EXCLUDED_STUDENT_STATUS', $EXCLUDED_STUDENT_STATUS, 'insert');
	} else {
		$EXCLUDED_STUDENT_STATUS['EDITED_BY']  			= $_SESSION['PK_USER'];
		$EXCLUDED_STUDENT_STATUS['EDITED_ON']  			= date("Y-m-d H:i");
		db_perform('S_90_10_EXCLUDED_STUDENT_STATUS', $EXCLUDED_STUDENT_STATUS, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	header("location:management");
}
$res = $db->Execute("SELECT PK_STUDENT_STATUS, EXCLUDED_PROGRAMS FROM S_90_10_EXCLUDED_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
$EXCLUDED_PROGRAMS_ARR = explode(",",$res->fields['EXCLUDED_PROGRAMS']);
$PK_STUDENT_STATUS_ARR = explode(",",$res->fields['PK_STUDENT_STATUS']);
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
	<title><?=MNU_90_10_CALCULATION_DISCLOSURE_SETUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><?=MNU_90_10_CALCULATION_DISCLOSURE_SETUP?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-12">
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<?=EXCLUDED_PROGRAM?>
														<select id="EXCLUDED_PROGRAMS" name="EXCLUDED_PROGRAMS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS_PROGRAM 	= $res_type->fields['PK_CAMPUS_PROGRAM']; 
															foreach($EXCLUDED_PROGRAMS_ARR as $EXCLUDED_PROGRAM){
																if($EXCLUDED_PROGRAM == $PK_CAMPUS_PROGRAM) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS_PROGRAM?>" <?=$selected?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-3">
													<div class="form-group m-b-40">
														<?=EXCLUDED_STUDENT_STATUS?>
														<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control">
															<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ADMISSIONS = 0 AND ACTIVE = 1 order by STUDENT_STATUS ASC");
															while (!$res_type->EOF) { 
																$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS'];
																$selected 			= "";
																/*$res = $db->Execute("SELECT PK_90_10_EXCLUDED_STUDENT_STATUS FROM S_90_10_EXCLUDED_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$PK_STUDENT_STATUS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
																if($res->RecordCount() > 0)
																	$selected = "selected";*/ 
																foreach($PK_STUDENT_STATUS_ARR as $PK_STUDENT_STATUS1){
																	if($PK_STUDENT_STATUS == $PK_STUDENT_STATUS1) {
																		$selected = "selected";
																		break;
																	}
																} ?>
																<option value="<?=$PK_STUDENT_STATUS ?>" <?=$selected ?> ><?=$res_type->fields['STUDENT_STATUS']?></option>
															<?	$res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
											</div>
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='management'" ><?=CANCEL?></button>
												
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
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_STUDENT_STATUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=STUDENT_STATUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		
		$('#EXCLUDED_PROGRAMS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=EXCLUDED_PROGRAM?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=EXCLUDED_PROGRAM?> selected'
		});
		
		
	});
	</script>

</body>

</html>