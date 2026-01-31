<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_group.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
// $sap_pk_array=array('15','67','72','64');
// if(!in_array($_SESSION['PK_ACCOUNT'],$sap_pk_array))
// {   
// 	header("location:../school/index");
// 	exit;
// }
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$SAP_GROUP = $_POST;
	
	$SAP_GROUP['PK_STUDENT_STATUS'] = implode(",",$_POST['PK_STUDENT_STATUS']);
	$SAP_GROUP['PK_CAMPUS'] 		= implode(",",$_POST['PK_CAMPUS']);
	$SAP_GROUP['IS_DEFAULT']		= $_POST['IS_DEFAULT'];
	
	if($SAP_GROUP['IS_DEFAULT'] == 1) {
		$db->Execute("UPDATE S_SAP_GROUP SET IS_DEFAULT = 0  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	}
	
	if($_GET['id'] == ''){
		$SAP_GROUP['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$SAP_GROUP['CREATED_BY']  = $_SESSION['PK_USER'];
		$SAP_GROUP['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_SAP_GROUP', $SAP_GROUP, 'insert');
		$PK_SAP_GROUP = $db->insert_ID();
	} else {
		$SAP_GROUP['EDITED_BY'] = $_SESSION['PK_USER'];
		$SAP_GROUP['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_SAP_GROUP', $SAP_GROUP, 'update'," PK_SAP_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$PK_SAP_GROUP = $_GET['id'];
	}
	header("location:manage_sap_group");
}
if($_GET['id'] == ''){
	$SAP_GROUP_NAME 		= '';
	$SAP_GROUP_DESCRIPTION 	= '';
	$IS_DEFAULT 			= '';
	$ACTIVE	 				= 1;
	$PK_STUDENT_STATUS_ARR 	= array();
	$PK_CAMPUS_ARR 			= array();
	
	/* Ticket # 2026  */
	$res = $db->Execute("SELECT PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1");
	if($res->RecordCount() == 1)
		$PK_CAMPUS_ARR[] = $res->fields['PK_CAMPUS'];
	/* Ticket # 2026  */
} else {
	$res = $db->Execute("SELECT * FROM S_SAP_GROUP WHERE PK_SAP_GROUP = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_sap_group");
		exit;
	}
	
	$SAP_GROUP_NAME 		= $res->fields['SAP_GROUP_NAME'];
	$SAP_GROUP_DESCRIPTION 	= $res->fields['SAP_GROUP_DESCRIPTION'];
	$IS_DEFAULT 			= $res->fields['IS_DEFAULT'];
	$ACTIVE  				= $res->fields['ACTIVE'];
	$PK_STUDENT_STATUS_ARR 	= explode(",",$res->fields['PK_STUDENT_STATUS']);
	$PK_CAMPUS_ARR 			= explode(",",$res->fields['PK_CAMPUS']);
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
	<title><?=MNU_SAP_GROUP?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=MNU_SAP_GROUP?> </h4>
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
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="SAP_GROUP_NAME" name="SAP_GROUP_NAME" value="<?=$SAP_GROUP_NAME?>" >
														<span class="bar"></span>
														<label for="SAP_GROUP_NAME"><?=SAP_GROUP_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="SAP_GROUP_DESCRIPTION" name="SAP_GROUP_DESCRIPTION" value="<?=$SAP_GROUP_DESCRIPTION?>" >
														<span class="bar"></span>
														<label for="SAP_GROUP_DESCRIPTION"><?=SAP_GROUP_DESCRIPTION?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="IS_DEFAULT"><?=IS_DEFAULT?></label>
													</div>
												</div>
												
												<div class="form-group row col-12 col-sm-10">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="IS_DEFAULT" name="IS_DEFAULT" value="1" <? if($IS_DEFAULT == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="IS_DEFAULT" >Yes</label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-2">
													<div class="form-group m-b-40">
														<span class="bar"></span>
														<label for="IS_DEFAULT"><?=ACTIVE?></label>
													</div>
												</div>
												
												<div class="row form-group col-12 col-sm-10">
													<div class="custom-control custom-radio col-md-2">
														<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
														<label class="custom-control-label" for="customRadio11"><?=YES?></label>
													</div>
													<div class="custom-control custom-radio col-md-5">
														<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
														<label class="custom-control-label" for="customRadio22"><?=NO?></label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="d-flex">
												<div class="col-12 col-sm-12 focused">
													<span class="bar"></span> 
													<label ><?=INCLUDED_STUDENT_STATUSES?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_STUDENT_STATUS" name="PK_STUDENT_STATUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_STUDENT_STATUS,STUDENT_STATUS,DESCRIPTION from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND ADMISSIONS = 0 order by STUDENT_STATUS ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_STUDENT_STATUS 	= $res_type->fields['PK_STUDENT_STATUS']; 
															foreach($PK_STUDENT_STATUS_ARR as $PK_STUDENT_STATUS1){
																if($PK_STUDENT_STATUS1 == $PK_STUDENT_STATUS) {
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
													<label ><?=CAMPUS?></label>
												</div>
											</div>
											<div class="d-flex">
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control" >
														<? $res_type = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by CAMPUS_CODE ASC");
														while (!$res_type->EOF) { 
															$selected 			= "";
															$PK_CAMPUS 	= $res_type->fields['PK_CAMPUS']; 
															foreach($PK_CAMPUS_ARR as $PK_CAMPUS1){
																if($PK_CAMPUS1 == $PK_CAMPUS) {
																	$selected = 'selected';
																	break;
																}
															} ?>
															<option value="<?=$PK_CAMPUS?>" <?=$selected?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
										</div>
                                    </div>
									
									<div class="row">
										<div class="col-md-3">&nbsp;</div>
                                        <div class="col-md-6">
											<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
											
											<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_sap_group'" ><?=CANCEL?></button>
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
			numberDisplayed: 3,
			nSelectedText: '<?=STUDENT_STATUS?> selected'
		});
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 3,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
	});
	</script>

</body>

</html>