<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
if($_GET['act'] == 'logo')	{
	$res = $db->Execute("SELECT LOGO FROM S_CAMPUS WHERE PK_CAMPUS = '$_GET[id]' ");
	unlink($res->fields['LOGO']);
	$db->Execute("UPDATE S_CAMPUS SET LOGO = '' WHERE PK_CAMPUS = '$_GET[id]' ");
		
	header("location:campus?id=".$_GET['id']);
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$SAVE_CONTINUE = $_POST['SAVE_CONTINUE'];
	$current_tab   = $_POST['current_tab'];
	unset($_POST['SAVE_CONTINUE']);
	unset($_POST['current_tab']);
	
	$CAMPUS = $_POST;
	$CAMPUS['PRIMARY_CAMPUS'] = $_POST['PRIMARY_CAMPUS'];
	
	if($_GET['id'] == ''){
		$CAMPUS['PK_TIMEZONE'] = 4;
		$CAMPUS['PK_ACCOUNT']  = $_GET['s_id'];
		$CAMPUS['CREATED_BY']  = $_SESSION['ADMIN_PK_USER'];
		$CAMPUS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_CAMPUS', $CAMPUS, 'insert');
		
		$PK_CAMPUS = $db->insert_ID();
	} else {
		$PK_CAMPUS = $_GET['id'];
		$CAMPUS['EDITED_BY'] = $_SESSION['ADMIN_PK_USER'];
		$CAMPUS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_CAMPUS', $CAMPUS, 'update'," PK_CAMPUS = '$PK_CAMPUS'");
	}
//echo "<pre>";print_r($CAMPUS);exit;	
	if($SAVE_CONTINUE == 0)
		header("location:accounts?id=".$_GET['s_id'].'&tab=campusTab');
	else
		header("location:campus?id=".$PK_CAMPUS.'&s_id='.$_GET['s_id'].'&tab='.($current_tab));
}

if($_GET['id'] == ''){
	$OFFICIAL_CAMPUS_NAME		= '';
	$CAMPUS_NAME 				= '';
	$CAMPUS_CODE 				= '';
	$SCHOOL_CODE	 			= '';
	$INSTITUTION_CODE	 		= '';
	$FEDERAL_SCHOOL_CODE  		= '';
	$FA_SCHOOL_CODE  			= '';
	$AMBASSADOR_SCHOOL_CODE  	= '';
	$COSMO_LICENSE  			= '';
	$ADDRESS	 				= '';
	$ADDRESS_1	 				= '';
	$CITY	 					= '';
	$PK_STATES 					= '';
	$ZIP	 					= '';
	$PK_COUNTRY					= '';
	$PHONE	 					= '';
	$FAX	 					= '';
	$PRIMARY_CAMPUS	 			= '';
	$ACCSC_SCHOOL_NUMBER	 	= '';
	$ACICS_SCHOOL_NUMBER	 	= '';
	$NACCAS_SCHOOL_NUMBER	 	= '';
	
} else {
	$res = $db->Execute("SELECT * FROM S_CAMPUS WHERE PK_CAMPUS = '$_GET[id]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_campus");
		exit;
	}
	
	$OFFICIAL_CAMPUS_NAME 		= $res->fields['OFFICIAL_CAMPUS_NAME'];
	$CAMPUS_NAME 				= $res->fields['CAMPUS_NAME'];
	$CAMPUS_CODE 				= $res->fields['CAMPUS_CODE'];
	$SCHOOL_CODE  				= $res->fields['SCHOOL_CODE'];
	$INSTITUTION_CODE  			= $res->fields['INSTITUTION_CODE'];
	
	$FEDERAL_SCHOOL_CODE  		= $res->fields['FEDERAL_SCHOOL_CODE'];
	$FA_SCHOOL_CODE  			= $res->fields['FA_SCHOOL_CODE'];
	$AMBASSADOR_SCHOOL_CODE  	= $res->fields['AMBASSADOR_SCHOOL_CODE'];
	$COSMO_LICENSE  			= $res->fields['COSMO_LICENSE'];
	
	$ADDRESS  					= $res->fields['ADDRESS'];
	$ADDRESS_1  				= $res->fields['ADDRESS_1'];
	$CITY  						= $res->fields['CITY'];
	$PK_STATES  				= $res->fields['PK_STATES'];
	$ZIP  						= $res->fields['ZIP'];
	$PK_COUNTRY  				= $res->fields['PK_COUNTRY'];
	$PHONE  					= $res->fields['PHONE'];
	$FAX  						= $res->fields['FAX'];
	$PRIMARY_CAMPUS  			= $res->fields['PRIMARY_CAMPUS'];
	$ACCSC_SCHOOL_NUMBER  		= $res->fields['ACCSC_SCHOOL_NUMBER'];
	$ACICS_SCHOOL_NUMBER  		= $res->fields['ACICS_SCHOOL_NUMBER'];
	$NACCAS_SCHOOL_NUMBER  		= $res->fields['NACCAS_SCHOOL_NUMBER'];
	
	//echo $PRIMARY_CAMPUS;exit;
}

if($_GET['tab'] == '' || $_GET['tab'] == 'homeTab' )
	$home_tab = 'active';
else if($_GET['tab'] == 'usersTab')
	$user_tab = 'active';
else
	$home_tab = 'active';
	
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
	<title>Campus | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Campus</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link <?=$home_tab?>" data-toggle="tab" href="#homeTab" role="tab"><span class="hidden-sm-up"><i class="ti-homeTab"></i></span> <span class="hidden-xs-down">General</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
								<div class="tab-content">
									<div class="tab-pane <?=$home_tab?>" id="homeTab" role="tabpanel">
										<div class="p-20">
                                        	<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="OFFICIAL_CAMPUS_NAME" name="OFFICIAL_CAMPUS_NAME" type="text" class="form-control" value="<?=$OFFICIAL_CAMPUS_NAME?>">
			                                        <span class="bar"></span> 
			                                        <label for="OFFICIAL_CAMPUS_NAME">Official Campus Name</label>
		                                    	</div>
												
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="CAMPUS_NAME" name="CAMPUS_NAME" type="text" class="form-control" value="<?=$CAMPUS_NAME?>">
			                                        <span class="bar"></span> 
			                                        <label for="CAMPUS_NAME">Campus Name</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="CAMPUS_CODE" name="CAMPUS_CODE" type="text" class="form-control" value="<?=$CAMPUS_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="CAMPUS_CODE">Campus Code</label>
		                                    	</div>
												<div class="col-12 col-sm-3 form-group">
		                                        	<input id="SCHOOL_CODE" name="SCHOOL_CODE" type="text" class="form-control" value="<?=$SCHOOL_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="SCHOOL_CODE">School Code</label>
		                                        </div>
											</div>
											
											<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="INSTITUTION_CODE" name="INSTITUTION_CODE" type="text" class="form-control" value="<?=$INSTITUTION_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="INSTITUTION_CODE">Institution Code(OPEID)</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="FEDERAL_SCHOOL_CODE" name="FEDERAL_SCHOOL_CODE" type="text" class="form-control" value="<?=$FEDERAL_SCHOOL_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="FEDERAL_SCHOOL_CODE">Federal School Code</label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="FA_SCHOOL_CODE" name="FA_SCHOOL_CODE" type="text" class="form-control" value="<?=$FA_SCHOOL_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="FA_SCHOOL_CODE">FA School Code</label>
		                                    	</div>	
																								
		                                    </div>
		                                    
		                                    <div class="row">
		                                    	<div class="col-sm-6 pt-25">
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?=$ADDRESS?>">
															<span class="bar"></span>
															<label for="ADDRESS">Address</label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?=$ADDRESS_1?>">
															<span class="bar"></span>
															<label for="ADDRESS_1">Address 2</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CITY" name="CITY" type="text" class="form-control" value="<?=$CITY?>">
															<span class="bar"></span> 
															 <label for="CITY">City</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<select id="PK_STATES" name="PK_STATES" class="form-control" onchange="get_country(this.value,'PK_COUNTRY')" >
																<option selected></option>
																 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_STATES">State</label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?=$ZIP?>">
															<span class="bar"></span> 
															 <label for="ZIP">Zip</label>
														</div>	
														<div class="col-12 col-sm-6 form-group" id="PK_COUNTRY_LABEL" >
															<div id="PK_COUNTRY_DIV" >
																<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																	<option selected></option>
																</select>
															</div>
															<span class="bar"></span> 
															<label for="PK_COUNTRY">Country</label>
														</div>	                                        
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="PHONE" name="PHONE" type="text" class="form-control phone-inputmask" value="<?=$PHONE?>">
															<span class="bar"></span> 
															 <label for="PHONE">Phone</label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<input id="FAX" name="FAX" type="text" class="form-control phone-inputmask" value="<?=$FAX?>">
															<span class="bar"></span> 
															 <label for="FAX">Fax</label>
														</div>
													</div>
												</div>
												<div class="col-sm-6 pt-25 theme-v-border">
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="AMBASSADOR_SCHOOL_CODE" name="AMBASSADOR_SCHOOL_CODE" type="text" class="form-control" value="<?=$AMBASSADOR_SCHOOL_CODE?>">
															<span class="bar"></span> 
															<label for="AMBASSADOR_SCHOOL_CODE">Ambassador School Code</label>
														</div>
														
														<div class="col-12 col-sm-6 form-group">
															<input id="COSMO_LICENSE" name="COSMO_LICENSE" type="text" class="form-control" value="<?=$COSMO_LICENSE?>">
															<span class="bar"></span> 
															<label for="COSMO_LICENSE">Cosmo License</label>
														</div>
													</div>
													
													<div class="d-flex">
				                                    	<div class="col-12 col-sm-6 form-group">
				                                    		<input id="ACCSC_SCHOOL_NUMBER" name="ACCSC_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$ACCSC_SCHOOL_NUMBER?>">
					                                        <span class="bar"></span> 
					                                        <label for="ACCSC_SCHOOL_NUMBER">ACCSC School Number</label>
				                                    	</div>
				                                    	<div class="col-12 col-sm-6 form-group">
				                                    		<input id="ACICS_SCHOOL_NUMBER" name="ACICS_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$ACICS_SCHOOL_NUMBER?>">
					                                        <span class="bar"></span> 
					                                        <label for="ACICS_SCHOOL_NUMBER">ACICS School Number</label>
				                                    	</div>
				                                    </div>
				                                    <div class="d-flex">
				                                    	<div class="col-12 col-sm-6 form-group">
				                                    		<input id="NACCAS_SCHOOL_NUMBER" name="NACCAS_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$NACCAS_SCHOOL_NUMBER?>">
					                                        <span class="bar"></span> 
					                                        <label for="NACCAS_SCHOOL_NUMBER">NACCAS School Number</label>
				                                    	</div>
				                                    	<div class="col-12 col-sm-6 form-group">
															<div class="custom-control custom-checkbox mr-sm-2">
																<input type="checkbox" class="custom-control-input" id="PRIMARY_CAMPUS" name="PRIMARY_CAMPUS" value="1" <? if($PRIMARY_CAMPUS == 1) echo "checked"; ?> >
																<label class="custom-control-label" for="PRIMARY_CAMPUS">Primary Campus?</label>
															</div>
														</div>
				                                    </div>
				                                    <div class="d-flex submit-button-sec">
														<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
														<input type="hidden" id="current_tab" name="current_tab" value="0" >
									
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info">Save & Continue</button>
														
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info">Save & Exit</button>
														
														<button type="button" onclick="window.location.href='accounts?id=<?=$_GET['s_id'].'&tab=campusTab'?>'"  class="btn waves-effect waves-light btn-dark">Cancel</button>
													</div>
												</div>
											</div>
		                                    <div class="row">
												<div class="col-3 col-sm-3">
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
		
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1">Delete Confirmation</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info">Yes</button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" >No</button>
					</div>
				</div>
			</div>
		</div>
    </div>
   
	<? require_once("js.php"); ?>
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		current_tab = 'homeTab';
	<? } ?>
	jQuery(document).ready(function($) {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
		
		<? if($_GET['id'] != ''){ ?>
			get_country(<?=$PK_STATES?>,'PK_COUNTRY')
		<? } ?>
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		
		function validate_form(val){
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true)
				document.form1.submit();
		}
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "ajax_get_country_from_state",	
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
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'logo')
					document.getElementById('delete_message').innerHTML = 'Are you sure you want to Delete this Logo?';
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'logo')
						window.location.href = 'campus?act=logo&id=<?=$_GET['id']?>';
				} else
					$("#deleteModal").modal("hide");
			});
		}
	</script>

</body>

</html>