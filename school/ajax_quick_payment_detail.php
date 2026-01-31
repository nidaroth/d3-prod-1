<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$student_count	= $_REQUEST['student_count'];
?>
<tr id="misc_batch_detail_div_<?=$student_count?>" >
	<td >
		<input type="hidden" name="student_count[]"  value="<?=$student_count?>" />
		<select id="BATCH_PK_AR_LEDGER_CODE_<?=$student_count?>" name="BATCH_PK_AR_LEDGER_CODE[]" class="form-control  required-entry" style="width:200px;" onchange="get_ledger_type(this.value,'<?=$student_count?>');get_fee_payment_type(this.value,'<?=$student_count?>');" >
			<option selected></option>
			 <? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND (QUICK_PAYMENT = 1 OR DIAMOND_PAY = 1) order by CODE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" ><?=$res_type->fields['CODE'].' - '.$res_type->fields['LEDGER_DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control date required-entry" onmousemove="date_picker()" onkeyup="date_picker()" value="<? echo date("m/d/Y"); ?>" name="TRANS_DATE[]" id="TRANS_DATE_<?=$student_count?>" style="width:90px" />
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0;width:100px;">
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 5px;height: 38px;">$</span>
			</div>
			<input type="number" class="form-control required-entry" placeholder="" name="BATCH_DEBIT[]" id="BATCH_DEBIT_<?=$student_count?>" value="0.00" onblur="format_val('BATCH_DEBIT',<?=$student_count?>);calc_total(1);" style="text-align:right;"  <?=$debit_disabled?> />
		</div>
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0;width:100px;">
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 5px;height: 38px;">$</span>
			</div>
			<input type="number" class="form-control required-entry" placeholder="" name="BATCH_CREDIT[]" id="BATCH_CREDIT_<?=$student_count?>" value="0.00" onblur="format_val('BATCH_CREDIT',<?=$student_count?>);calc_total(1);" style="text-align:right;"  <?=$credit_disabled?> />
		</div>
	</td>
	<td>
		<div style="width:100px" id="FEE_PAYMENT_TYPE_DIV_<?=$student_count?>" >
	
		</div>
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="BATCH_DETAIL_DESCRIPTION[]" id="BATCH_DETAIL_DESCRIPTION_<?=$student_count?>" value="Quick Batch" style="width:150px" />
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="BATCH_AY[]" id="BATCH_AY_<?=$student_count?>" value="1" style="width:50px"  />
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="BATCH_AP[]" id="BATCH_AP_<?=$student_count?>" value="1" style="width:50px"  />
	</td>
	<td>
		<div id="ENROLLMEN_DIV_<?=$student_count?>" style="width:150px" >
			
		</div>
	</td>
	<td>
		<select id="BATCH_PK_TERM_BLOCK_<?=$student_count?>" name="BATCH_PK_TERM_BLOCK[]" class="form-control " style="width:150px" >
			<option></option>
			<? 
			$res_type = $db->Execute("SELECT PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DESCRIPTION, ACTIVE FROM S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, BEGIN_DATE DESC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" <? if($PK_TERM_BLOCK == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			}  ?>
		</select>
	</td>
	<td>
		<select id="PRIOR_YEAR_<?=$student_count?>" name="PRIOR_YEAR[]" class="form-control required-entry" style="width:50px" >
			<option ></option>
			<option value="1" >Yes</option>
			<option value="2" selected >No</option>
		</select>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$student_count?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>