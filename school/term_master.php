<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_master.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$PK_CAMPUS_ARR = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);
	
	$TERM_MASTER = $_POST;
	
	if($TERM_MASTER['BEGIN_DATE'] != '')
		$TERM_MASTER['BEGIN_DATE'] = date("Y-m-d",strtotime($TERM_MASTER['BEGIN_DATE']));
		
	if($TERM_MASTER['END_DATE'] != '')
		$TERM_MASTER['END_DATE'] = date("Y-m-d",strtotime($TERM_MASTER['END_DATE']));
		
	if($_GET['id'] == ''){
		$TERM_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$TERM_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
		$TERM_MASTER['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_TERM_MASTER', $TERM_MASTER, 'insert');
		$PK_TERM_MASTER = $db->insert_ID();
	} else {
		$TERM_MASTER['EDITED_BY'] = $_SESSION['PK_USER'];
		$TERM_MASTER['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_TERM_MASTER', $TERM_MASTER, 'update'," PK_TERM_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		$PK_TERM_MASTER = $_GET['id'];
	}
	
	foreach($PK_CAMPUS_ARR as $PK_CAMPUS){
		$res = $db->Execute("SELECT PK_TERM_MASTER_CAMPUS FROM S_TERM_MASTER_CAMPUS WHERE PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' "); 
		if($res->RecordCount() == 0) {
			$TERM_MASTER_CAMPUS['PK_TERM_MASTER']   = $PK_TERM_MASTER;
			$TERM_MASTER_CAMPUS['PK_CAMPUS'] 		= $PK_CAMPUS;
			$TERM_MASTER_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
			$TERM_MASTER_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
			$TERM_MASTER_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_TERM_MASTER_CAMPUS', $TERM_MASTER_CAMPUS, 'insert');
			$PK_TERM_MASTER_CAMPUS_ARR[] = $db->insert_ID();
		} else {
			$PK_TERM_MASTER_CAMPUS_ARR[] = $res->fields['PK_TERM_MASTER_CAMPUS'];
		}
	}
	
	$cond = "";
	if(!empty($PK_TERM_MASTER_CAMPUS_ARR))
		$cond = " AND PK_TERM_MASTER_CAMPUS NOT IN (".implode(",",$PK_TERM_MASTER_CAMPUS_ARR).") ";
	
	$db->Execute("DELETE FROM S_TERM_MASTER_CAMPUS WHERE PK_TERM_MASTER = '$PK_TERM_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
			
	header("location:manage_term_master");
}
if($_GET['id'] == ''){
	$BEGIN_DATE 				= '';
	$END_DATE	 				= '';	
	$TERM_DESCRIPTION			= '';
	$TERM_GROUP	 				= '';
	$ALLOW_ONLINE_ENROLLMENT	= '';
	$LMS_ACTIVE	 				= '';
	$OLD_DSIS_ID 				= '';
	$ACTIVE	 					= '';
} else {
	$res = $db->Execute("SELECT * FROM S_TERM_MASTER WHERE PK_TERM_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_term_master");
		exit;
	}
	$BEGIN_DATE 				= $res->fields['BEGIN_DATE'];
	$END_DATE  					= $res->fields['END_DATE'];
	$TERM_DESCRIPTION  			= $res->fields['TERM_DESCRIPTION'];
	$TERM_GROUP  				= $res->fields['TERM_GROUP'];
	$ALLOW_ONLINE_ENROLLMENT	= $res->fields['ALLOW_ONLINE_ENROLLMENT'];
	$LMS_ACTIVE  				= $res->fields['LMS_ACTIVE'];
	$OLD_DSIS_ID  				= $res->fields['OLD_DSIS_ID'];
	$ACTIVE  					= $res->fields['ACTIVE'];
	
	if($BEGIN_DATE == '0000-00-00')
		$BEGIN_DATE = '';
	else
		$BEGIN_DATE = date("m/d/Y",strtotime($BEGIN_DATE));
		
	if($END_DATE == '0000-00-00')
		$END_DATE = '';
	else
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
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
	<title><?=TERM_MASTER_PAGE_TITLE?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TERM_MASTER_PAGE_TITLE?> </h4>
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
														<input type="text" class="form-control required-entry date1" id="BEGIN_DATE" name="BEGIN_DATE" value="<?=$BEGIN_DATE?>" >
														<span class="bar"></span>
														<label for="BEGIN_DATE"><?=BEGIN_DATE?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="form-group m-b-40">
														<input type="text" class="form-control date2" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" >
														<span class="bar"></span>
														<label for="END_DATE"><?=END_DATE?></label>
													</div>
												</div>
											</div>
											
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="OLD_DSIS_ID" name="OLD_DSIS_ID" value="<?=$OLD_DSIS_ID?>" >
														<span class="bar"></span>
														<label for="OLD_DSIS_ID"><?=OLD_DSIS_ID?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control " id="TERM_DESCRIPTION" name="TERM_DESCRIPTION" value="<?=$TERM_DESCRIPTION?>" >
														<span class="bar"></span>
														<label for="TERM_DESCRIPTION"><?=DESCRIPTION?></label>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control" id="TERM_GROUP" name="TERM_GROUP" value="<?=$TERM_GROUP?>" >
														<span class="bar"></span>
														<label for="TERM_GROUP"><?=GROUP?></label>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-md-6">
											
											<div class="row " >
												<div class="col-12 col-sm-12 form-group">
													<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control"  >
														<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC ");
														while (!$res_type->EOF) { 
															$selected = '';
															$PK_CAMPUS = $res_type->fields['PK_CAMPUS']; 
															
															/* Ticket # 1487 */
															if($_GET['id'] == '' && $res_type->RecordCount() == 1)
																$selected = 'selected';
															/* Ticket # 1487 */
															
															$res_type_1 = $db->Execute("select PK_TERM_MASTER_CAMPUS from S_TERM_MASTER_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TERM_MASTER = '$_GET[id]' AND PK_TERM_MASTER > 0 AND PK_CAMPUS = '$PK_CAMPUS' "); 
															if($res_type_1->RecordCount() > 0) 
																$selected = 'selected'; ?>
															<option value="<?=$PK_CAMPUS?>" <?=$selected ?> ><?=$res_type->fields['CAMPUS_CODE']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ALLOW_ONLINE_ENROLLMENT?></div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="ALLOW_ONLINE_ENROLLMENT_1" name="ALLOW_ONLINE_ENROLLMENT" value="1" <? if($ALLOW_ONLINE_ENROLLMENT == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="ALLOW_ONLINE_ENROLLMENT_1"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="ALLOW_ONLINE_ENROLLMENT_2" name="ALLOW_ONLINE_ENROLLMENT" value="0" <? if($ALLOW_ONLINE_ENROLLMENT == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="ALLOW_ONLINE_ENROLLMENT_2"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=LMS_ACTIVE?></div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="LMS_ACTIVE_1" name="LMS_ACTIVE" value="1" <? if($LMS_ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="LMS_ACTIVE_1"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="LMS_ACTIVE_2" name="LMS_ACTIVE" value="0" <? if($LMS_ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="LMS_ACTIVE_2"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											
											<? if($_GET['id'] != ''){ ?>
											<div class="row">
												<div class="col-md-12">
													<div class="row form-group">
														<div class="custom-control col-md-4"><?=ACTIVE?></div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11"><?=YES?></label>
														</div>
														<div class="custom-control custom-radio col-md-1">
															<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="customRadio22"><?=NO?></label>
														</div>
													</div>
												</div>
											</div>
											<? } ?>
										</div>
									</div>
									
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_term_master'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			
			jQuery('.date1').datepicker({
				todayHighlight: true,
				orientation: "bottom auto",
				autoclose: true,
			});
			
			$('.date1').datepicker().on('hide', function(e) {
				if(document.getElementById('BEGIN_DATE').value != '') {
					var minDate = $("#BEGIN_DATE").val();
					$('#END_DATE').datepicker('setStartDate', minDate);
					document.getElementById('END_DATE').focus();
					$("#BEGIN_DATE").parent().addClass("focused")
				} else
					$("#BEGIN_DATE").parent().removeClass("focused")
			});
			
			jQuery('.date2').datepicker({
				todayHighlight: true,
				orientation: "bottom auto",
			});
			
			<? if($BEGIN_DATE != ''){ ?>
				var minDate = $("#BEGIN_DATE").val();
				$('#END_DATE').datepicker('setStartDate', minDate);
			<? } ?>
		});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function calc_day(){
			var DAYS = 0;
			if(document.getElementById('JAN').value != '')
				DAYS += parseInt(document.getElementById('JAN').value);
				
			if(document.getElementById('FEB').value != '')
				DAYS += parseInt(document.getElementById('FEB').value);
				
			if(document.getElementById('MAR').value != '')
				DAYS += parseInt(document.getElementById('MAR').value);
				
			if(document.getElementById('APR').value != '')
				DAYS += parseInt(document.getElementById('APR').value);
				
			if(document.getElementById('MAY').value != '')
				DAYS += parseInt(document.getElementById('MAY').value);
				
			if(document.getElementById('JUN').value != '')
				DAYS += parseInt(document.getElementById('JUN').value);
				
			if(document.getElementById('JUL').value != '')
				DAYS += parseInt(document.getElementById('JUL').value);
				
			if(document.getElementById('AUG').value != '')
				DAYS += parseInt(document.getElementById('AUG').value);
				
			if(document.getElementById('SEP').value != '')
				DAYS += parseInt(document.getElementById('SEP').value);
				
			if(document.getElementById('OCT').value != '')
				DAYS += parseInt(document.getElementById('OCT').value);
				
			if(document.getElementById('NOV').value != '')
				DAYS += parseInt(document.getElementById('NOV').value);
				
			if(document.getElementById('DECEMBER').value != '')
				DAYS += parseInt(document.getElementById('DECEMBER').value);
				
			document.getElementById('DAYS').value = DAYS
			document.getElementById('DAYS_LABEL').classList.add("focused");
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '<?=CAMPUS?>',
			numberDisplayed: 3,
			nSelectedText: '<?=CAMPUS?> selected'
		});
	});
	</script>
</body>

</html>