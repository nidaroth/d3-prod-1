<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("../global/Models/S_LEDGER_CODE_GROUP.php");
require_once("check_access.php");
function update_ledger_groups_with_current_selection($current_ar_ledger_code , $PK_LEDGER_CODE_GROUP_ARR){
	$ALL_S_LEDGER_CODE_GROUPS = S_LEDGER_CODE_GROUP::where('PK_ACCOUNT' , $_SESSION['PK_ACCOUNT'])->get();
	foreach ($ALL_S_LEDGER_CODE_GROUPS as $S_LEDGER_CODE_GROUP) {
		# code...
		$exploded_codes = explode("," , $S_LEDGER_CODE_GROUP->PK_AR_LEDGER_CODES);
		//Check if this PK_LEDGER_CODE_GROUP exist in selected list
		#if yes , add current ar_pk_ledger_code
		if(in_array($S_LEDGER_CODE_GROUP->PK_LEDGER_CODE_GROUP , $PK_LEDGER_CODE_GROUP_ARR)){
			$key = array_search($current_ar_ledger_code, $exploded_codes);
			if ($key !== false) {
				//do nothing , code already exists
			}else{
				$exploded_codes[] = $current_ar_ledger_code;
				$S_LEDGER_CODE_GROUP->PK_AR_LEDGER_CODES = implode(',' , $exploded_codes);
				$S_LEDGER_CODE_GROUP->save();
			}
		}
		#if not , remove the current ar_pk_ledger_code from list of codes for ledger group
		else{
			$key = array_search($current_ar_ledger_code, $exploded_codes);
			if ($key !== false) {
				unset($exploded_codes[$key]);
				$S_LEDGER_CODE_GROUP->PK_AR_LEDGER_CODES = implode(',' , $exploded_codes);
				$S_LEDGER_CODE_GROUP->save();
			}
			
		}
	}
}
if(check_access('SETUP_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	ENABLE_DEBUGGING(TRUE);
	// echo "<pre>";print_r($_POST);exit;
	$PK_LEDGER_CODE_GROUP = $_POST['PK_LEDGER_CODE_GROUP'];
	unset($_POST['PK_LEDGER_CODE_GROUP']);
	$AR_LEDGER_CODE = $_POST;
	if($_GET['id'] == ''){
		$AR_LEDGER_CODE['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$AR_LEDGER_CODE['CREATED_BY']  = $_SESSION['PK_USER'];
		$AR_LEDGER_CODE['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'insert');
		$AR_LEDGER_CODE_ID = $db->insert_ID();
	} else {
		$AR_LEDGER_CODE['EDITED_BY'] = $_SESSION['PK_USER'];
		$AR_LEDGER_CODE['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('M_AR_LEDGER_CODE', $AR_LEDGER_CODE, 'update'," PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}
	$param_AR_LEDGER_CODE_ID = $_GET['id'] ?? $AR_LEDGER_CODE_ID;
	update_ledger_groups_with_current_selection($param_AR_LEDGER_CODE_ID , $PK_LEDGER_CODE_GROUP);
	header("location:manage_ar_leder_code");
}
if($_GET['id'] == ''){
	$CODE			 		= '';
	$LEDGER_DESCRIPTION 	= '';
	$INVOICE_DESCRIPTION 	= '';
	$GL_CODE_DEBIT 	 		= '';
	$GL_CODE_CREDIT 	 	= '';
	$TYPE 	 				= '';
	$NEED_ANALYSIS 	 		= 0;
	$AWARD_LETTER 	 		= 0;
	$INVOICE 	 			= 0;
	$TITLE_IV 	 			= 0;
	$QUICK_PAYMENT			= 0;
	$DIAMOND_PAY			= 0;
	$ACTIVE	 				= '';
	$DEFAULT_MANAGEMENT		=0; //DIAM-922
	
	
} else {
	$res = $db->Execute("SELECT * FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_ar_leder_code");
		exit;
	}
	
	$CODE 			 		= $res->fields['CODE'];
	$LEDGER_DESCRIPTION 	= $res->fields['LEDGER_DESCRIPTION'];
	$INVOICE_DESCRIPTION 	= $res->fields['INVOICE_DESCRIPTION'];
	$GL_CODE_DEBIT 	 		= $res->fields['GL_CODE_DEBIT'];
	$GL_CODE_CREDIT 	 	= $res->fields['GL_CODE_CREDIT'];
	$TYPE 	 			    = $res->fields['TYPE'];
	$NEED_ANALYSIS 	 		= $res->fields['NEED_ANALYSIS'];
	$AWARD_LETTER 	 		= $res->fields['AWARD_LETTER'];
	$INVOICE 	 			= $res->fields['INVOICE'];
	$TITLE_IV 	 			= $res->fields['TITLE_IV'];
	$QUICK_PAYMENT 	 		= $res->fields['QUICK_PAYMENT'];
	$DIAMOND_PAY 	 		= $res->fields['DIAMOND_PAY'];
	$DEFAULT_MANAGEMENT		= $res->fields['DEFAULT_MANAGEMENT'];; //DIAM-922
	$ACTIVE  		 		= $res->fields['ACTIVE'];
	$current_ar_ledger_code_1	= $_GET['id'];
	$PK_LEDGER_CODE_GROUP_ARR_sql = $db->Execute("SELECT GROUP_CONCAT(PK_LEDGER_CODE_GROUP) AS PKS FROM S_LEDGER_CODE_GROUP WHERE CONCAT(',', PK_AR_LEDGER_CODES, ',') LIKE '%,$current_ar_ledger_code_1,%'");
	$PK_LEDGER_CODE_GROUP_ARR = explode(',' , $PK_LEDGER_CODE_GROUP_ARR_sql->fields['PKS']);
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
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<style>
		li>a>label {
			position: unset !important;
		}
		.option_red > a > label{color:red !important}
	</style>
	<title><?=AR_LEDGER_CODE_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=AR_LEDGER_CODE_PAGE_TITLE?> </h4>
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
												<div class="col-md-6">
													<div class="row">
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="CODE" name="CODE" value="<?=$CODE?>" onBlur="duplicate_check()" >
																<span class="bar"></span>
																<label for="CODE"><?=LEDGER_CODE?></label>
																<div id="already_exit" style="display:none;color:#ff0000;" ><?=LEDGER_CODE?> already exists. Try with another.</div>
															</div>
														</div>
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<select id="TYPE" name="TYPE" class="form-control required-entry" <? if($_GET['id'] != '') echo "disabled"; ?> >
																	<option value=""></option>
																	<option value="1" <? if($TYPE == 1) echo "selected"; ?> >Award</option>
																	<option value="2" <? if($TYPE == 2) echo "selected"; ?> >Fee</option>
																</select>
																<span class="bar"></span> 
																 <label for="TYPE"><?=TYPE?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control required-entry" id="LEDGER_DESCRIPTION" name="LEDGER_DESCRIPTION" value="<?=$LEDGER_DESCRIPTION?>" >
																<span class="bar"></span>
																<label for="LEDGER_DESCRIPTION"><?=LEDGER_DESCRIPTION?></label>
															</div>
														</div>
												   
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control" id="INVOICE_DESCRIPTION" name="INVOICE_DESCRIPTION" value="<?=$INVOICE_DESCRIPTION?>" >
																<span class="bar"></span>
																<label for="INVOICE_DESCRIPTION"><?=INVOICE_DESCRIPTION?></label>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control" id="GL_CODE_DEBIT" name="GL_CODE_DEBIT" value="<?=$GL_CODE_DEBIT?>" >
																<span class="bar"></span>
																<label for="GL_CODE_DEBIT"><?=GL_CODE_DEBIT?></label>
															</div>
														</div>
												   
														<div class="col-md-6">
															<div class="form-group m-b-40">
																<input type="text" class="form-control" id="GL_CODE_CREDIT" name="GL_CODE_CREDIT" value="<?=$GL_CODE_CREDIT?>" >
																<span class="bar"></span>
																<label for="GL_CODE_CREDIT"><?=GL_CODE_CREDIT?></label>
															</div>
														</div>
													</div>
													
													<? if($_GET['id'] != ''){ ?>
													<div class="row">	
														<div class="col-md-6">
															<div class="row form-group">
																<div class="custom-control col-md-3"><?=ACTIVE?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>
													<? } ?>
												</div>
												<div class="col-md-3">
													
													<div class="row">	
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=DIAMOND_PAY?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="DIAMOND_PAYaa" name="DIAMOND_PAY" value="1" <? if($DIAMOND_PAY == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="DIAMOND_PAYaa"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="DIAMOND_PAYbb" name="DIAMOND_PAY" value="0" <? if($DIAMOND_PAY == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="DIAMOND_PAYbb"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>

													<div class="row">
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=INVOICE?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio77" name="INVOICE" value="1" <? if($INVOICE == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio77"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio88" name="INVOICE" value="0" <? if($INVOICE == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio88"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>

													<div class="row">
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=NEED_ANALYSIS?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio11a" name="NEED_ANALYSIS" value="1" <? if($NEED_ANALYSIS == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11a"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio22b" name="NEED_ANALYSIS" value="0" <? if($NEED_ANALYSIS == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22b"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>
													
													<div class="row">
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=OFFER_LETTER?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio33" name="AWARD_LETTER" value="1" <? if($AWARD_LETTER == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio33"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio44" name="AWARD_LETTER" value="0" <? if($AWARD_LETTER == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio44"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>
																										
													<div class="row">	
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=QUICK_PAYMENT?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="QUICK_PAYMENTaa" name="QUICK_PAYMENT" value="1" <? if($QUICK_PAYMENT == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="QUICK_PAYMENTaa"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="QUICK_PAYMENTbb" name="QUICK_PAYMENT" value="0" <? if($QUICK_PAYMENT == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="QUICK_PAYMENTbb"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>

													<div class="row">	
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=TITLE_IV?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadioaa" name="TITLE_IV" value="1" <? if($TITLE_IV == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadioaa"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadiobb" name="TITLE_IV" value="0" <? if($TITLE_IV == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadiobb"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>

													
													<?php if(has_wvjc_access($_SESSION['PK_ACCOUNT'],1)){ ?>
													<!--DIAM-922-->
													<div class="row">	
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-6"><?=DEFAULT_MANAGEMENT?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="DEFAULT_MANAGEMENTaa" name="DEFAULT_MANAGEMENT" value="1" <? if($DEFAULT_MANAGEMENT == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="DEFAULT_MANAGEMENTaa"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="DEFAULT_MANAGEMENTbb" name="DEFAULT_MANAGEMENT" value="0" <? if($DEFAULT_MANAGEMENT == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="DEFAULT_MANAGEMENTbb"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>
													<?php } ?>
													<!--DIAM-922-->					
												</div>
												<div class="col-md-3">
												<div class="d-flex">
													<div class="col-12 col-sm-12 focused">
														<span class="bar"></span>
														<label>Ledger Code Group</label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<select id="PK_LEDGER_CODE_GROUP" name="PK_LEDGER_CODE_GROUP[]" multiple class="form-control">
															<? $res_type = $db->Execute("SELECT PK_LEDGER_CODE_GROUP,LEDGER_CODE_GROUP,LEDGER_CODE_GROUP_DESC,ACTIVE from S_LEDGER_CODE_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, LEDGER_CODE_GROUP ASC");
															while (!$res_type->EOF) {
																$selected 			= "";
																$PK_LEDGER_CODE_GROUP 	= $res_type->fields['PK_LEDGER_CODE_GROUP'];
																foreach ($PK_LEDGER_CODE_GROUP_ARR as $PK_LEDGER_CODE_GROUP1) {
																	if ($PK_LEDGER_CODE_GROUP1 == $PK_LEDGER_CODE_GROUP) {
																		$selected = 'selected';
																		break;
																	}
																} ?>
																<option value="<?= $PK_LEDGER_CODE_GROUP ?>" <?= $selected ?>  <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?>><?= $res_type->fields['LEDGER_CODE_GROUP'] ?><? if($res_type->fields['ACTIVE'] == 0) echo " (Inactive)"; ?></option>
															<? $res_type->MoveNext();
															} ?>
														</select>
													</div>
												</div>
												</div>
											</div>
										</div>
                                    </div>
								
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_ar_leder_code'" ><?=CANCEL?></button>
												
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
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
		
		function duplicate_check(){
			jQuery(document).ready(function($) {
				if (document.form1.CODE.value  != ""){
					var CODE = document.form1.CODE.value;
					var data="CODE="+CODE+'&type=LEDGER_CODE&k=<?=$_SESSION['PK_ACCOUNT']?>&id=<?=$_GET['id']?>';
					$.ajax({
						type: "POST",
						url:"../check_duplicate",
						data:data,
						success: function(result1){ 
							if(result1==1){
								document.getElementById('already_exit').style.display="block";
								document.getElementById('CODE').value = "";
								return false;
							}else{
								document.getElementById('already_exit').style.display="none";
							}
						}
					});
				}
			});	
		}
	</script>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_LEDGER_CODE_GROUP').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All Ledger Groups',
				nonSelectedText: '',
				numberDisplayed: 3,
				nSelectedText: 'Ledger Groups selected'
			});

		});
	</script>

</body>

</html>
