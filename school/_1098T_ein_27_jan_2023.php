<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/_1098T_Setup.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT _1098T FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 || $res_add_on->fields['_1098T'] == 0){
	header("location:../index");
	exit;
}
$report_error="";
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$CAMPUS = $_POST['PK_CAMPUS'];
	unset($_POST['PK_CAMPUS']);

    // DIAM - 16
	$IS_TRANS = $_POST['TRANSMITTER'];
	$EIN_NO = $_POST['EIN_NO'];
	$PK_ACCOUNT_REC = $_SESSION['PK_ACCOUNT'];
	$GET_ID = $_GET['id'];

	$res_type = $db->Execute("SELECT * FROM _1098T_EIN WHERE EIN_NO = '$EIN_NO' AND PK_ACCOUNT = '$PK_ACCOUNT_REC'  ");

	$sql_transmitter_exists="SELECT * FROM _1098T_EIN WHERE TRANSMITTER = '1' AND PK_ACCOUNT = '".$PK_ACCOUNT_REC."' ";

	$res_type_rec = $db->Execute($sql_transmitter_exists);
	$is_transmitter=$res_type_rec->RecordCount();
	$EIN_NUMBER = $res_type->fields['EIN_NO'];
	$EIN_NUMBER_REC = $res_type_rec->fields['EIN_NO'];
	$PK_ID = $res_type->fields['PK_1098T_EIN'];

	$PK_ID_REC = $res_type_rec->fields['PK_1098T_EIN'];
	//$PK_ID_TRANS = $res_type_rec->fields['PK_ID'];

	if (($EIN_NO == $EIN_NUMBER) && ($GET_ID != $PK_ID)) // DIAM -16
	{
		$report_error = "One record already exists for EIN : ".$EIN_NUMBER;
	}
	elseif($is_transmitter >= 1 && $IS_TRANS!=0 && ($GET_ID !=$PK_ID_REC)){

		$report_error = "One record already marked as TRANSMITTER for EIN : ".$EIN_NUMBER_REC;
	}
	else
	{
		$_1098T_EIN = $_POST;
		if($_GET['id'] == ''){
			$_1098T_EIN['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
			$_1098T_EIN['CREATED_BY'] = $_SESSION['PK_USER'];
			$_1098T_EIN['CREATED_ON'] = date("Y-m-d H:i:s");
			db_perform('_1098T_EIN', $_1098T_EIN, 'insert');
			$PK_1098T_EIN= $db->insert_ID();
		} else {
			$PK_1098T_EIN = $_GET['id'];
			$_1098T_EIN['EDITED_BY'] = $_SESSION['PK_USER'];
			$_1098T_EIN['EDITED_ON'] = date("Y-m-d H:i:s");
			db_perform('_1098T_EIN', $_1098T_EIN, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_1098T_EIN = '$PK_1098T_EIN' ");
		}
		
		foreach($CAMPUS as $CAMPUS_1){
			$res = $db->Execute("SELECT PK_1098T_EIN_CAMPUS FROM _1098T_EIN_CAMPUS WHERE PK_1098T_EIN = '$PK_1098T_EIN' AND PK_CAMPUS = '$CAMPUS_1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
			$_1098T_EIN_CAMPUS['PK_CAMPUS']  = $CAMPUS_1;
			if($res->RecordCount() == 0) {
				$_1098T_EIN_CAMPUS['PK_1098T_EIN'] 		= $PK_1098T_EIN;
				$_1098T_EIN_CAMPUS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
				$_1098T_EIN_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$_1098T_EIN_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('_1098T_EIN_CAMPUS', $_1098T_EIN_CAMPUS, 'insert');
				$PK_1098T_EIN_CAMPUS_ARR[] = $db->insert_ID();
			} else {
				$PK_1098T_EIN_CAMPUS_ARR[] = $res->fields['PK_1098T_EIN_CAMPUS'];
			}
		}
		
		$cond = "";
		if(!empty($PK_1098T_EIN_CAMPUS_ARR))
			$cond = " AND PK_1098T_EIN_CAMPUS NOT IN (".implode(",",$PK_1098T_EIN_CAMPUS_ARR).") ";
		
		$db->Execute("DELETE FROM _1098T_EIN_CAMPUS WHERE PK_1098T_EIN = '$PK_1098T_EIN' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");
		
		header("location:manage_1098T_ein");
	}
}
if($_GET['id'] == ''){
	$TRANSMITTER_CONTROL_CODE 	= '';
	$TRANSMITTER 			= '';
	$TRANSMITTER_NAME 			= '';
	$COMPANY_ISSUER_NAME 		= '';
	$ADDRESS 					= '';
	$ADDRESS_1 					= '';
	$CITY 						= '';
	$PK_STATES 					= '';
	$ZIP 						= '';
	$EIN_NO 					= '';
	$CONTACT_NAME	 			= '';
	$CONTACT_PHONE 				= '';
	$CONTACT_EMAIL 				= '';
} else {
	$res = $db->Execute("select * from _1098T_EIN WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_1098T_EIN = '$_GET[id]' ");
	$TRANSMITTER_CONTROL_CODE 	= $res->fields['TRANSMITTER_CONTROL_CODE'];
	$TRANSMITTER 			    = $res->fields['TRANSMITTER'];
	$TRANSMITTER_NAME 			= $res->fields['TRANSMITTER_NAME'];
	$COMPANY_ISSUER_NAME 		= $res->fields['COMPANY_ISSUER_NAME'];
	$ADDRESS 					= $res->fields['ADDRESS'];
	$ADDRESS_1 					= $res->fields['ADDRESS_1'];
	$CITY 						= $res->fields['CITY'];
	$PK_STATES 					= $res->fields['PK_STATES'];
	$ZIP 						= $res->fields['ZIP'];
	$EIN_NO 					= $res->fields['EIN_NO'];
	$CONTACT_NAME 				= $res->fields['CONTACT_NAME'];
	$CONTACT_PHONE 				= $res->fields['CONTACT_PHONE'];
	$CONTACT_EMAIL 				= $res->fields['CONTACT_EMAIL'];
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
	<title><?=MNU_1098T_EIN ?> | <?=$title?></title>
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
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo "Add "; else echo "Edit "; ?><?=MNU_1098T_EIN ?></h4>
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
																$res = $db->Execute("select PK_1098T_EIN_CAMPUS FROM _1098T_EIN_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_1098T_EIN = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="TRANSMITTER_CONTROL_CODE" name="TRANSMITTER_CONTROL_CODE" value="<?=$TRANSMITTER_CONTROL_CODE?>" >
														<span class="bar"></span>
														<label for="TRANSMITTER_CONTROL_CODE"><?=TRANSMITTER_CONTROL_CODE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="EIN_NO" name="EIN_NO" value="<?=$EIN_NO?>" >
														<span class="bar"></span>
														<label for="EIN_1"><?=EIN_1?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="CONTACT_NAME" name="CONTACT_NAME" value="<?=$CONTACT_NAME?>" >
														<span class="bar"></span>
														<label for="CONTACT_NAME"><?=CONTACT_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control phone-inputmask required-entry" id="CONTACT_PHONE" name="CONTACT_PHONE" value="<?=$CONTACT_PHONE?>" >
														<span class="bar"></span>
														<label for="CONTACT_PHONE"><?=CONTACT_PHONE?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control validate-email required-entry" id="CONTACT_EMAIL" name="CONTACT_EMAIL" value="<?=$CONTACT_EMAIL?>" >
														<span class="bar"></span>
														<label for="CONTACT_EMAIL"><?=CONTACT_EMAIL?></label>
													</div>
												</div>
											</div>
											
										</div>
										<div class="col-6 col-sm-6 ">
										
											<div class="row">

												<div class="col-12 col-sm-8 form-group" style="padding-bottom: 22px;padding-top: 25px;">
													<div class="row form-group">
														<div class="custom-control col-md-3" style="padding-left: 0.5rem;"><label for="TRANSMITTER">Transmitter</label></div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="customRadio11" name="TRANSMITTER" value="1" <? if($TRANSMITTER == 1) echo "checked"; ?> class="custom-control-input">
															<label class="custom-control-label" for="customRadio11">Yes</label>
														</div>
														<div class="custom-control custom-radio col-md-2">
															<input type="radio" id="customRadio22" name="TRANSMITTER" value="0" <? if($TRANSMITTER == 0) echo "checked"; ?>  class="custom-control-input">
															<label class="custom-control-label" for="customRadio22">No</label>
														</div>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="TRANSMITTER_NAME" name="TRANSMITTER_NAME" value="<?=$TRANSMITTER_NAME?>" >
														<span class="bar"></span>
														<label for="TRANSMITTER_NAME"><?=TRANSMITTER_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="COMPANY_ISSUER_NAME" name="COMPANY_ISSUER_NAME" value="<?=$COMPANY_ISSUER_NAME?>" >
														<span class="bar"></span>
														<label for="COMPANY_ISSUER_NAME"><?=COMPANY_ISSUER_NAME?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="ADDRESS" name="ADDRESS" value="<?=$ADDRESS?>" >
														<span class="bar"></span>
														<label for="ADDRESS"><?=ADDRESS?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-md-12">
													<div class="form-group m-b-40">
														<input type="text" class="form-control required-entry" id="ADDRESS_1" name="ADDRESS_1" value="<?=$ADDRESS_1?>" >
														<span class="bar"></span>
														<label for="ADDRESS_1"><?=ADDRESS_1?></label>
													</div>
												</div>
											</div>
											
											<div class="row">
												<div class="col-12 col-sm-4 form-group">
													<input id="CITY" name="CITY" type="text" class="form-control required-entry" value="<?=$CITY?>">
													<span class="bar"></span> 
													 <label for="CITY">City</label>
												</div>
												<div class="col-12 col-sm-4 form-group">
													<select id="PK_STATES" name="PK_STATES" class="form-control  required-entry" onchange="get_country(this.value,'PK_COUNTRY')" >
														<option selected></option>
														<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
														while (!$res_type->EOF) { ?>
															<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
														<?	$res_type->MoveNext();
														} ?>
													</select>
													<span class="bar"></span> 
													<label for="STATE">State</label>
												</div>
												<div class="col-12 col-sm-4 form-group">
													<input id="ZIP" name="ZIP" type="text" class="form-control validate-zipcode required-entry" value="<?=$ZIP?>">
													<span class="bar"></span> 
													 <label for="ZIP">Zip</label>
												</div>
											</div>

											<div class="row">
												<div class="col-9 col-sm-9">
												</div>
												<div class="col-3 col-sm-3">
													<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
													<button type="button" onclick="window.location.href='manage_1098T_ein'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
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
        <?php if($report_error!="") {?>
		<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">1098T - IRS FIRE info Error Reporting</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" style="color: red;font-size: 15px;">
							<b><?php echo $report_error; ?></b>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn waves-effect waves-light btn-info">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
    </div>
   
	<? require_once("js.php"); ?>

	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		var error= '<?php echo  $report_error; ?>';
		jQuery(document).ready(function($) {
		   if(error!=""){
			jQuery('#errorModal').modal();
		   }
		})

		var form1 = new Validation('form1');
		function check_campus(campus,id){
			jQuery(document).ready(function($) { 
				//alert(document.getElementById("PK_CAMPUS_"+id).checked)
				if(document.getElementById("PK_CAMPUS_"+id).checked == true) {
					var data  = 'campus='+campus+'&id=<?=$_GET['id']?>';
					var value = $.ajax({
						url: "ajax_check_ein_campus",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							if(data == 'b'){
								document.getElementById("PK_CAMPUS_"+id).checked = false
								alert('This Campus is Already Assigned to Another EID');
							}
						}		
					}).responseText;
				}
			});
		}

		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "/super_admin/ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id+'_LABEL').classList.add("focused");
						document.getElementById(id).innerHTML = data;
					}		
				}).responseText;
			});
		}
	</script>
	<?php $report_error=""; ?>
</body>

</html>