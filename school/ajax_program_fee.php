<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_CAMPUS_PROGRAM_FEE  = $_REQUEST['PK_CAMPUS_PROGRAM_FEE'];
$program_fee_id 		= $_REQUEST['program_fee_id']; 

if($PK_CAMPUS_PROGRAM_FEE == '') {
	$PK_AR_LEDGER_CODE 		= '';
	$PK_FEE_TYPE 	 		= '';
	$DESCRIPTION 	 		= '';
	$AMOUNT 		 		= '';
	$AY 	 				= '';
	$AP 	 				= '';
	$PK_DEPENDENT_STATUS 	= '';
	$PK_HOUSING_TYPE 	 	= '';
	$PK_GE_DISCLOSURE 	 	= '';
	$ACTIVE 	 			= 1;
	$DAYS_FROM_START 		= '';
	$PROGRAM_FEE_NO_OF_PAYMENTS		  = '';
	$PROGRAM_FEE_PK_PAYMENT_FREQUENCY = '';
} else {
	//$res_dd = $db->Execute("select * FROM M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM_FEE = '$PK_CAMPUS_PROGRAM_FEE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	
	$res_dd = $db->Execute("SELECT PK_AR_LEDGER_CODE,PK_FEE_TYPE,DESCRIPTION,AMOUNT,AY,AP,PK_DEPENDENT_STATUS,PK_HOUSING_TYPE,PK_GE_DISCLOSURE,ACTIVE,DAYS_FROM_START,PROGRAM_FEE_NO_OF_PAYMENTS,PROGRAM_FEE_PK_PAYMENT_FREQUENCY
	 FROM M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM_FEE = '$PK_CAMPUS_PROGRAM_FEE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");

	$PK_AR_LEDGER_CODE 		= $res_dd->fields['PK_AR_LEDGER_CODE'];
	$PK_FEE_TYPE   			= $res_dd->fields['PK_FEE_TYPE'];
	$DESCRIPTION   			= $res_dd->fields['DESCRIPTION'];
	$AMOUNT   				= $res_dd->fields['AMOUNT'];
	$AY   					= $res_dd->fields['AY'];
	$AP   					= $res_dd->fields['AP'];
	$PK_DEPENDENT_STATUS   	= $res_dd->fields['PK_DEPENDENT_STATUS'];
	$PK_HOUSING_TYPE   		= $res_dd->fields['PK_HOUSING_TYPE'];
	$PK_GE_DISCLOSURE   	= $res_dd->fields['PK_GE_DISCLOSURE'];
	$ACTIVE 	 			= $res_dd->fields['ACTIVE'];
	$DAYS_FROM_START 	 	= $res_dd->fields['DAYS_FROM_START'];
	$PROGRAM_FEE_NO_OF_PAYMENTS		  = $res_dd->fields['PROGRAM_FEE_NO_OF_PAYMENTS'];
	$PROGRAM_FEE_PK_PAYMENT_FREQUENCY = $res_dd->fields['PROGRAM_FEE_PK_PAYMENT_FREQUENCY'];
}
?>
<tr id="program_fee_div_<?=$program_fee_id?>" >
	<td >
		<input type="hidden" name="PK_CAMPUS_PROGRAM_FEE[]" value="<?=$PK_CAMPUS_PROGRAM_FEE?>" />
		<input type="hidden" name="program_fee_id[]" value="<?=$program_fee_id?>" />
		
		<input type="text" class="form-control" placeholder="" name="DAYS_FROM_START[]" id="DAYS_FROM_START_<?=$program_fee_id?>" value="<?=$DAYS_FROM_START?>" />
	</td>
	
	<td >
		<select id="PK_AR_LEDGER_CODE_<?=$program_fee_id?>" name="PK_AR_LEDGER_CODE[]" class="form-control required-entry" onchange="get_ledger_desc(this.value,'<?=$program_fee_id?>')" error_label="<?=FEE?> - <?=TAB_TUITION_FEE?>" style="width:160px;" ><!-- Ticket # 1160 -->
			<option selected></option>
			<? /* Ticket #1697  */
			$res_type = $db->Execute("select PK_AR_LEDGER_CODE, CONCAT(CODE, ' - ', LEDGER_DESCRIPTION) as CODE, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)";?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
			<?	$res_type->MoveNext();
			} /* Ticket #1697  */ ?>
		</select>
	</td>
	
	<td >
		<select id="PK_FEE_TYPE_<?=$program_fee_id?>" name="PK_FEE_TYPE[]" class="form-control required-entry" error_label="<?=FEE_TYPE?> - <?=TAB_TUITION_FEE?>" style="width:160px;" ><!-- Ticket # 1160 -->
			<option selected></option>
			<? /* Ticket #1149  */
			$act_type_cond = " AND ACTIVE = 1 ";
			if($PK_FEE_TYPE > 0)
				$act_type_cond = " AND (ACTIVE = 1 OR PK_FEE_TYPE='$PK_FEE_TYPE' ) ";
				
			$res_type = $db->Execute("select PK_FEE_TYPE,FEE_TYPE from M_FEE_TYPE WHERE 1 = 1 $act_type_cond order by FEE_TYPE ASC"); /* Ticket #1149  */
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_FEE_TYPE'] ?>" <? if($res_type->fields['PK_FEE_TYPE'] == $PK_FEE_TYPE) echo "selected"; ?> ><?=$res_type->fields['FEE_TYPE']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	
	<td >
		<input type="text" class="form-control" placeholder="" name="FEE_DESCRIPTION[]" id="FEE_DESCRIPTION_<?=$program_fee_id?>" value="<?=$DESCRIPTION?>" style="width:160px;" />
	</td>
	
	<td >
		<input type="text" class="form-control" placeholder="" name="FEE_AMOUNT[]" id="FEE_AMOUNT_<?=$program_fee_id?>" value="<?=$AMOUNT?>" onchange="calc_tot_fee()" style="text-align:right;width:100px;" />
	</td>
	
	<td >
		<input type="text" class="form-control" placeholder="" name="FEE_AY[]" id="FEE_AY_<?=$program_fee_id?>" value="<?=$AY?>" />
	</td>
	
	<td >
		<input type="text" class="form-control" placeholder="" name="FEE_AP[]" id="FEE_AP_<?=$program_fee_id?>" value="<?=$AP?>" />
	</td>
	
	<td >
		<select id="PROGRAM_FEE_PK_PAYMENT_FREQUENCY_<?=$award_id?>" name="PROGRAM_FEE_PK_PAYMENT_FREQUENCY[]" class="form-control" style="width:160px;">
			<option value="" ></option>
			<? $act_type_cond = " AND ACTIVE = 1 ";
			if($PROGRAM_FEE_PK_PAYMENT_FREQUENCY > 0)
				$act_type_cond = " AND (ACTIVE = 1 OR PK_PAYMENT_FREQUENCY = '$PROGRAM_FEE_PK_PAYMENT_FREQUENCY' ) ";
				
			$res_type = $db->Execute("select PK_PAYMENT_FREQUENCY,PAYMENT_FREQUENCY from M_PAYMENT_FREQUENCY WHERE 1 = 1 $act_type_cond order by PAYMENT_FREQUENCY ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_PAYMENT_FREQUENCY']?>" <? if($PROGRAM_FEE_PK_PAYMENT_FREQUENCY == $res_type->fields['PK_PAYMENT_FREQUENCY']) echo "selected"; ?> ><?=$res_type->fields['PAYMENT_FREQUENCY']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_FEE_NO_OF_PAYMENTS[]" id="PROGRAM_FEE_NO_OF_PAYMENTS_<?=$award_id?>" value="<?=$PROGRAM_FEE_NO_OF_PAYMENTS?>" style="width:100px;" />
	</td>
	
	<td >
		<select name="PK_DEPENDENT_STATUS[]" id="PK_DEPENDENT_STATUS_<?=$program_fee_id?>" class="form-control" style="width:100px;" >
			<option selected></option>
			<? /* Ticket #1149  */
			$act_type_cond = " AND ACTIVE = 1 ";
			if($PK_DEPENDENT_STATUS > 0)
				$act_type_cond = " AND (ACTIVE = 1 OR PK_DEPENDENT_STATUS='$PK_DEPENDENT_STATUS' ) ";
				
			$res_type = $db->Execute("select PK_DEPENDENT_STATUS,CODE,DESCRIPTION from M_DEPENDENT_STATUS WHERE 1 = 1 $act_type_cond order by CODE ASC"); /* Ticket #1149  */
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_DEPENDENT_STATUS'] ?>" <? if($res_type->fields['PK_DEPENDENT_STATUS'] == $PK_DEPENDENT_STATUS) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	
	<td >
		<select name="PK_HOUSING_TYPE[]" id="PK_HOUSING_TYPE_<?=$program_fee_id?>" class="form-control" style="width:160px;" >
			<option selected></option>
			<? /* Ticket #1149  */
			$act_type_cond = " AND ACTIVE = 1 ";
			if($PK_HOUSING_TYPE > 0)
				$act_type_cond = " AND (ACTIVE = 1 OR PK_HOUSING_TYPE='$PK_HOUSING_TYPE' ) ";
				
			$res_type = $db->Execute("select PK_HOUSING_TYPE,CODE,DESCRIPTION from M_HOUSING_TYPE WHERE 1 = 1 $act_type_cond order by CODE ASC"); /* Ticket #1149  */
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_HOUSING_TYPE'] ?>" <? if($res_type->fields['PK_HOUSING_TYPE'] == $PK_HOUSING_TYPE) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<!-- Ticket # 1528
	<td >
		<select name="PK_GE_DISCLOSURE[]" id="PK_GE_DISCLOSURE_<?=$program_fee_id?>" class="form-control" style="width:160px;" >
			<option selected></option>
			<? /* Ticket #1149  */
			//$act_type_cond = " AND ACTIVE = 1 ";
			//if($PK_GE_DISCLOSURE > 0)
				//$act_type_cond = " AND (ACTIVE = 1 OR PK_GE_DISCLOSURE='$PK_GE_DISCLOSURE' ) ";
				
			//$res_type = $db->Execute("select PK_GE_DISCLOSURE,GE_DISCLOSURE from M_GE_DISCLOSURE WHERE 1 = 1 $act_type_cond order by GE_DISCLOSURE ASC"); /* Ticket #1149  */
			//while (!$res_type->EOF) { ?>
				<option value="<? //=$res_type->fields['PK_GE_DISCLOSURE'] ?>" <? //if($res_type->fields['PK_GE_DISCLOSURE'] == $PK_GE_DISCLOSURE) echo "selected"; ?> ><? //=$res_type->fields['GE_DISCLOSURE'] ?></option>
			<?	//$res_type->MoveNext();
			//} ?>
		</select>
	</td>
	-->
	
	<td >
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="FEE_ACTIVE_<?=$program_fee_id?>" name="FEE_ACTIVE_<?=$program_fee_id?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="FEE_ACTIVE_<?=$program_fee_id?>">&nbsp;</label>
			</div>
		</div>
	</td>
	
	<td >
		<a href="javascript:void(0)" onclick="delete_row(<?=$program_fee_id?>,'program_fee')" class="btn delete-color btn-circle btn-sm" data-action="<?=$PK_CAMPUS_PROGRAM_FEE?>" id="delete_element_<?=$program_fee_id?>" 
 ><i class="far fa-trash-alt"></i></a><!--//DIAM-786-->
	</td>
	
</tr>
