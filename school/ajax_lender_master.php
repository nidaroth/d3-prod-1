<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/lender_master.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$count  		   = $_REQUEST['count'];
$PK_LENDER_MASTER  = $_REQUEST['PK_LENDER_MASTER'];  

if($PK_LENDER_MASTER == ''){
	$ACTIVE 	= 1;
	$LENDER  	= '';
	$CONTACT  	= '';
	$ADDRESS  	= '';
	$ADDRESS1   = '';
	$PHONE  	= '';
	$CITY  		= '';
	$EMAIL  	= '';
	$ZIP  		= '';
	$PK_STATES  = '';
} 
else{
	$result 	= $db->Execute("SELECT * FROM S_LENDER_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_LENDER_MASTER = '$PK_LENDER_MASTER'");
	$ACTIVE  	= $result->fields['ACTIVE'];
	$LENDER  	= $result->fields['LENDER'];
	$CONTACT  	= $result->fields['CONTACT'];
	$ADDRESS  	= $result->fields['ADDRESS'];
	$ADDRESS1   = $result->fields['ADDRESS1'];
	$PHONE  	= $result->fields['PHONE'];
	$CITY  		= $result->fields['CITY'];
	$EMAIL  	= $result->fields['EMAIL'];
	$ZIP  		= $result->fields['ZIP'];
	$PK_STATES  = $result->fields['PK_STATES'];	
}
?>

<div class="p-20 pb-0 lender-form" id="table_<?=$count?>">
	
	<div class="row pt-3" style="border: 1px solid #fdfdfd;box-shadow: 1px 0px 20px rgba(0, 0, 0, 0.08);">
		<input type="hidden" name="PK_LENDER_MASTER[]"  value="<?=$PK_LENDER_MASTER?>" />
		<input type="hidden" name="COUNT[]"  value="<?=$count?>" />

		<div class="col-md-12">
			<div style="text-align:right;display: flex;align-items: center;justify-content: flex-end;" >
				<div class="d-flex">
					<div class="custom-control custom-checkbox form-group mb-0" style="width: 70px;">
						<input type="checkbox" class="custom-control-input" id="ACTIVE_<?=$count?>" name="ACTIVE_<?=$count?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
						<label class="custom-control-label" for="ACTIVE_<?=$count?>" style="left:0;top: 0;" ><?=ACTIVE?></label>
					</div>
				</div>
				<span>
					<? $res_check1 = $db->Execute("select PK_STUDENT_APPROVED_AWARD_SUMMARY from S_STUDENT_APPROVED_AWARD_SUMMARY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_LENDER_MASTER = '$PK_LENDER_MASTER' AND PK_LENDER_MASTER > 0 ");
					$res_check2 = $db->Execute("select PK_STUDENT_AWARD from S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_LENDER_MASTER = '$PK_LENDER_MASTER' AND PK_LENDER_MASTER > 0 ");
					$res_check3 = $db->Execute("select PK_STUDENT_FINANCIAL from S_STUDENT_FINANCIAL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_LENDER_MASTER = '$PK_LENDER_MASTER' AND PK_LENDER_MASTER > 0 ");
					if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0) {
					?>
					<a href="javascript:void(0)" onclick="delete_row('<?=$count?>','detail')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
					<? } ?>
				</span>
			</div>		
		</div>
		<div class="col-sm-6 pt-2"> 
			
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="LENDER_<?=$count?>" name="LENDER[]" type="text" class="form-control required-entry" value="<?=$LENDER?>" autofocus />
					<span class="bar"></span>
					<label for="LENDER_<?=$count?>"><?=LENDER?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="CONTACT_<?=$count?>" name="CONTACT[]" type="text" class="form-control " value="<?=$CONTACT?>" />
					<span class="bar"></span>
					<label for="CONTACT_<?=$count?>"><?=CONTACT?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="ADDRESS_<?=$count?>" name="ADDRESS[]" type="text" value="<?=$ADDRESS?>" class="form-control " />
					<span class="bar"></span>
					<label for="ADDRESS_<?=$count?>"><?=ADDRESS?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="PHONE_<?=$count?>" name="PHONE[]" type="text" value="<?=$PHONE?>" class="form-control phone-inputmask" />
					<span class="bar"></span>
					<label for="PHONE_<?=$count?>"><?=PHONE?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="ADDRESS1_<?=$count?>" name="ADDRESS1[]" type="text" value="<?=$ADDRESS1?>" class="form-control " />
					<span class="bar"></span>
					<label for="ADDRESS1_<?=$count?>"><?=ADDRESS1?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-8 form-group focused">
					<input id="EMAIL_<?=$count?>" name="EMAIL[]" type="text" value="<?=$EMAIL?>" class="form-control validate-email" />
					<span class="bar"></span>
					<label for="EMAIL_<?=$count?>"><?=EMAIL?></label>
				</div>
			</div>
		</div>
		<div class="col-sm-6 pt-2"> 
			<div class="d-flex">
				<div class="col-sm-4 form-group focused">
					<input id="CITY_<?=$count?>" name="CITY[]" type="text" value="<?=$CITY?>" class="form-control " />
					<span class="bar"></span>
					<label for="CITY_<?=$count?>"><?=CITY?></label>
				</div>
				<div class="col-sm-4 form-group focused">
					<select id="PK_STATES_<?=$count?>" name="PK_STATES[]" value="<?=$PK_STATES?>" class="form-control " >  
					<option selected></option>
					<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
					<?	$res_type->MoveNext();
					} ?>
					</select>
					<span class="bar"></span> 
					<label for="PK_STATES_<?=$count?>"><?=STATE?></label>
				</div>  
				<div class="col-sm-4 form-group focused">
					<input id="ZIP_<?=$count?>" name="ZIP[]" type="text" value="<?=$ZIP?>" class="form-control " />
					<span class="bar"></span>
					<label for="ZIP_<?=$count?>"><?=ZIP?></label>
				</div>
			</div>
		</div>
	</div>
</div>