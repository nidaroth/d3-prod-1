<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$PK_CAMPUS_PROGRAM_AWARD 	= $_REQUEST['PK_CAMPUS_PROGRAM_AWARD'];
$award_id					= $_REQUEST['award_id'];

if($PK_CAMPUS_PROGRAM_AWARD == ''){
	$DAYS_FROM_START 		= '';
	$PK_AR_LEDGER_CODE 		= '';
	$ACADEMIC_YEAR 			= '';
	$ACADEMIC_PERIOD 		= '';
	$GROSS_AMOUNT 			= '';
	$FEE_AMOUNT 			= '';
	$NET_AMOUNT 			= '';
	$PK_DEPENDENT_STATUS 	= '';
	$PK_PAYMENT_FREQUENCY 	= 11;
	$HOURS_REQUIRED			= '';
	$NO_OF_PAYMENTS 		= 1;
} else {	
	$res_11 = $db->Execute("SELECT DAYS_FROM_START,PK_AR_LEDGER_CODE,ACADEMIC_YEAR,ACADEMIC_PERIOD,GROSS_AMOUNT,FEE_AMOUNT,NET_AMOUNT,PK_DEPENDENT_STATUS,NO_OF_PAYMENTS,PK_PAYMENT_FREQUENCY,HOURS_REQUIRED
	FROM M_CAMPUS_PROGRAM_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM_AWARD = '$PK_CAMPUS_PROGRAM_AWARD'");
	
	$DAYS_FROM_START 		= $res_11->fields['DAYS_FROM_START'];
	$PK_AR_LEDGER_CODE 		= $res_11->fields['PK_AR_LEDGER_CODE'];
	$ACADEMIC_YEAR 			= $res_11->fields['ACADEMIC_YEAR'];
	$ACADEMIC_PERIOD 		= $res_11->fields['ACADEMIC_PERIOD'];
	$GROSS_AMOUNT 			= $res_11->fields['GROSS_AMOUNT'];
	$FEE_AMOUNT 			= $res_11->fields['FEE_AMOUNT'];
	$NET_AMOUNT 			= $res_11->fields['NET_AMOUNT'];
	$PK_DEPENDENT_STATUS 	= $res_11->fields['PK_DEPENDENT_STATUS'];
	$NO_OF_PAYMENTS 		= $res_11->fields['NO_OF_PAYMENTS'];
	$PK_PAYMENT_FREQUENCY 	= $res_11->fields['PK_PAYMENT_FREQUENCY'];
	$HOURS_REQUIRED			= $res_11->fields['HOURS_REQUIRED'];
}
?>
<tr id="student_award_div_<?=$award_id?>" >
	<td >
		<input type="hidden" name="PK_CAMPUS_PROGRAM_AWARD[]" id="PK_CAMPUS_PROGRAM_AWARD" value="<?=$PK_CAMPUS_PROGRAM_AWARD?>" />
		<input type="hidden" name="award_id[]"  value="<?=$award_id?>" />
		
		<input type="text" class="form-control" placeholder="" name="AWARD_DAYS_FROM_START[]" id="AWARD_DAYS_FROM_START_<?=$award_id?>" value="<?=$DAYS_FROM_START?>" />
	</td>
	<td>
		<select id="AWARD_PK_AR_LEDGER_CODE_<?=$award_id?>" name="AWARD_PK_AR_LEDGER_CODE[]" class="form-control" style="width:200px;" onchange="get_ledger_desc_1(this.value,'<?=$award_id?>')" >
			<option selected></option>
			<? /* Ticket #1697  */
			$res_type = $db->Execute("select PK_AR_LEDGER_CODE, CONCAT(CODE, ' - ', LEDGER_DESCRIPTION) as CODE, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_type->MoveNext();
			} /* Ticket #1697  */ ?>
		</select>
	</td>
	<td>
		<div id="AWARD_PK_AR_LEDGER_CODE_DESC_<?=$award_id?>">
			<? if($PK_AR_LEDGER_CODE != '') {
				$_REQUEST['val'] = $PK_AR_LEDGER_CODE;
				include('ajax_get_ledger_desc.php');
			} ?>
		</div>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_ACADEMIC_YEAR[]" id="AWARD_ACADEMIC_YEAR_<?=$award_id?>" value="<?=$ACADEMIC_YEAR?>" style="width:50px;" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_ACADEMIC_PERIOD[]" id="AWARD_ACADEMIC_PERIOD_<?=$award_id?>" value="<?=$ACADEMIC_PERIOD?>" style="width:50px;" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_GROSS_AMOUNT[]" id="AWARD_GROSS_AMOUNT_<?=$award_id?>" value="<?=$GROSS_AMOUNT?>" style="text-align:right" style="width:200px;" onchange="calc_net_amount(<?=$award_id?>)" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_FEE_AMOUNT[]" id="AWARD_FEE_AMOUNT_<?=$award_id?>" value="<?=$FEE_AMOUNT?>" style="text-align:right" style="width:200px;" onchange="calc_net_amount(<?=$award_id?>)" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_NET_AMOUNT[]" id="AWARD_NET_AMOUNT_<?=$award_id?>" value="<?=$NET_AMOUNT?>" style="text-align:right" style="width:200px;" readonly />
	</td>
	<td>
		<select id="AWARD_PK_DEPENDENT_STATUS_<?=$award_id?>" name="AWARD_PK_DEPENDENT_STATUS[]" class="form-control" style="width:130px;">
			<option value="" ></option>
			<? $res_type = $db->Execute("select PK_DEPENDENT_STATUS,CONCAT(CODE,' - ',DESCRIPTION) AS DESCRIPTION  from M_DEPENDENT_STATUS WHERE ACTIVE = 1 order by CODE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_DEPENDENT_STATUS']?>" <? if($PK_DEPENDENT_STATUS == $res_type->fields['PK_DEPENDENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_HOURS_REQUIRED[]" id="AWARD_HOURS_REQUIRED_<?=$award_id?>" value="<?=$HOURS_REQUIRED?>" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="AWARD_NO_OF_PAYMENTS[]" id="AWARD_NO_OF_PAYMENTS_<?=$award_id?>" value="<?=$NO_OF_PAYMENTS?>" style="width:150px;" />
	</td>
	<td>
		<select id="AWARD_PK_PAYMENT_FREQUENCY_<?=$award_id?>" name="AWARD_PK_PAYMENT_FREQUENCY[]" class="form-control" style="width:160px;">
			<option value="" ></option>
			<? $res_type = $db->Execute("select PK_PAYMENT_FREQUENCY,PAYMENT_FREQUENCY from M_PAYMENT_FREQUENCY WHERE ACTIVE = 1 order by DISPLAY_ORDER ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PAYMENT_FREQUENCY']?>" <? if($PK_PAYMENT_FREQUENCY == $res_type->fields['PK_PAYMENT_FREQUENCY']) echo "selected"; ?> ><?=$res_type->fields['PAYMENT_FREQUENCY']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$award_id?>','award')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>