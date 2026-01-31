<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cunt_course_fees  	= $_REQUEST['cunt_course_fees'];
$PK_COURSE_FEE  	= $_REQUEST['PK_COURSE_FEE'];
if($PK_COURSE_FEE == '') {
	$PK_AR_LEDGER_CODE  = '';
	$DESCRIPTION    	= '';
	$FEE_AMT   	  		= '';
	$ISBN_10   	  		= '';
	$ISBN_13   	  		= '';
	$SCHOOL_COST   	  	= '';
} else {
	$result = $db->Execute("SELECT * FROM S_COURSE_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_FEE = '$PK_COURSE_FEE' ");
	$PK_AR_LEDGER_CODE  = $result->fields['PK_AR_LEDGER_CODE'];
	$DESCRIPTION    	= $result->fields['DESCRIPTION'];
	$FEE_AMT   	  		= $result->fields['FEE_AMT'];
	$ISBN_10			= $result->fields['ISBN_10'];
	$ISBN_13			= $result->fields['ISBN_13'];
	$SCHOOL_COST		= $result->fields['SCHOOL_COST'];
}

?>
<div class="row" id="COURSE_FEE_<?=$cunt_course_fees?>" style="margin-bottom:5px" >
	<input type="hidden" name="PK_COURSE_FEE[]"  value="<?=$PK_COURSE_FEE?>" />
	<input type="hidden" name="cunt_course_fees[]"  value="<?=$cunt_course_fees?>" />
	
	<div class="col-md-2">
		<select id="PK_AR_LEDGER_CODE_<?=$cunt_course_fees?>" name="PK_AR_LEDGER_CODE[]" class="form-control" >
			<option selected></option>
			<? /* Ticket #1696  */
			$res_type = $db->Execute("select PK_AR_LEDGER_CODE, CONCAT(CODE, ' - ', LEDGER_DESCRIPTION) as CODE, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
			<?	$res_type->MoveNext();
			} /* Ticket #1696  */ ?>
		</select>
	</div>
	<div class="col-md-2">
		<input type="text" name="FEE_DESCRIPTION[]" placeholder="" id="FEE_DESCRIPTION_<?=$cunt_course_fees?>"  class="form-control" value="<?=$DESCRIPTION?>" />
	</div>
	<div class="col-md-2 input-group">
		<div class="input-group-prepend">
			<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
		</div>
		<input type="number" name="FEE_AMT[]" placeholder="" id="FEE_AMT_<?=$cunt_course_fees?>"  class="form-control" value="<?=$FEE_AMT?>" onchange="calc_tot_fee()" min="0" style="text-align:right" />
	</div>
	<div class="col-md-2">
		<input type="text" name="ISBN_10[]" placeholder="" id="ISBN_10_<?=$cunt_course_fees?>"  class="form-control" value="<?=$ISBN_10?>" />
	</div>
	<div class="col-md-2">
		<input type="text" name="ISBN_13[]" placeholder="" id="ISBN_13_<?=$cunt_course_fees?>"  class="form-control" value="<?=$ISBN_13?>" />
	</div>
	<div class="col-md-1 input-group">
		<div class="input-group-prepend">
			<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
		</div>
		<input type="number" name="SCHOOL_COST[]" placeholder="" id="SCHOOL_COST_<?=$cunt_course_fees?>"  class="form-control" value="<?=$SCHOOL_COST?>" onchange="calc_tot_fee()" min="0" style="text-align:right" />
	</div>
	<div class="col-md-1">
		<a href="javascript:void(0)" onclick="delete_row('<?=$cunt_course_fees?>','COURSE_FEE')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>

