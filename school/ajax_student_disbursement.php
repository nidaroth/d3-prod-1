<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$res_pay_access = $db->Execute("select ENABLE_DIAMOND_PAY from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

if($FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_DISBURSEMENT 	= $_REQUEST['PK_STUDENT_DISBURSEMENT'];
$disbursement_id			= $_REQUEST['disbursement_id'];

if($PK_STUDENT_DISBURSEMENT == ''){
	$DIS_PK_STUDENT_ENROLLMENT	= $_REQUEST['eid'];
	$PK_STUDENT_MASTER		= $_REQUEST['sid'];
	$PK_AR_LEDGER_CODE 		= '';
	$ACADEMIC_YEAR 			= 1;
	$ACADEMIC_PERIOD 		= 1;
	$DISBURSEMENT_DATE 		= '';
	$DISBURSEMENT_AMOUNT 	= '';
	$APPROVED_DATE 			= '';
	$DEPOSITED_DATE 		= '';
	$PK_DISBURSEMENT_STATUS = '';
	$HOURS_REQUIRED 		= '';
	$PK_DETAIL_TYPE 		= '';
	$DETAIL 				= '';
	$BATCH					= '';
	
	$DISBURSEMENT_GROSS_AMOUNT 		= '';
	$DISBURSEMENT_FEE_AMOUNT 		= '';
	$PK_PAYMENT_BATCH_DETAIL 		= '';
	$DISBURSEMENT_FUNDS_REQUESTED 	= '';
	$DISBURSEMENT_COMMENTS 			= '';
	
	$date = date("Y-m-d");
	$res_11 = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE '$date' BETWEEN BEGIN_DATE AND END_DATE ");
	$PK_AWARD_YEAR = $res_11->fields['PK_AWARD_YEAR'];
	
	$res_11 = $db->Execute("select ENROLLMENT_PK_TERM_BLOCK from S_STUDENT_ENROLLMENT WHERE PK_STUDENT_ENROLLMENT = '$DIS_PK_STUDENT_ENROLLMENT' ");
	$PK_TERM_BLOCK = $res_11->fields['ENROLLMENT_PK_TERM_BLOCK'];
} else {
	$res_11 = $db->Execute("select * from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' ");
	
	$DIS_PK_STUDENT_ENROLLMENT	= $res_11->fields['PK_STUDENT_ENROLLMENT'];
	$PK_STUDENT_MASTER		= $res_11->fields['PK_STUDENT_MASTER'];
	$PK_AR_LEDGER_CODE 		= $res_11->fields['PK_AR_LEDGER_CODE'];
	$ACADEMIC_YEAR 			= $res_11->fields['ACADEMIC_YEAR'];
	$ACADEMIC_PERIOD 		= $res_11->fields['ACADEMIC_PERIOD'];
	$DISBURSEMENT_DATE 		= $res_11->fields['DISBURSEMENT_DATE'];
	$DISBURSEMENT_AMOUNT 	= $res_11->fields['DISBURSEMENT_AMOUNT'];
	$PK_AWARD_YEAR 			= $res_11->fields['PK_AWARD_YEAR'];
	$APPROVED_DATE 			= $res_11->fields['APPROVED_DATE'];
	$DEPOSITED_DATE 		= $res_11->fields['DEPOSITED_DATE'];
	$PK_DISBURSEMENT_STATUS = $res_11->fields['PK_DISBURSEMENT_STATUS'];
	$HOURS_REQUIRED 		= $res_11->fields['HOURS_REQUIRED'];
	$PK_TERM_BLOCK 			= $res_11->fields['PK_TERM_BLOCK'];
	$PK_DETAIL_TYPE 		= $res_11->fields['PK_DETAIL_TYPE'];
	$DETAIL 				= $res_11->fields['DETAIL'];
	
	$DISBURSEMENT_GROSS_AMOUNT 		= $res_11->fields['GROSS_AMOUNT'];
	$DISBURSEMENT_FEE_AMOUNT 		= $res_11->fields['FEE_AMOUNT'];
	$DISBURSEMENT_FUNDS_REQUESTED 	= $res_11->fields['FUNDS_REQUESTED'];
	$DISBURSEMENT_COMMENTS 			= $res_11->fields['COMMENTS'];
	
	$BATCH					 = '';
	$PK_PAYMENT_BATCH_MASTER = '';
	$PK_PAYMENT_BATCH_DETAIL = $res_11->fields['PK_PAYMENT_BATCH_DETAIL'];
	$res_22 = $db->Execute("select S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER,BATCH_NO from S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL WHERE S_PAYMENT_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND PK_PAYMENT_BATCH_DETAIL = '$PK_PAYMENT_BATCH_DETAIL' ");
	if($res_22->RecordCount() > 0) {
		$BATCH 					 = $res_22->fields['BATCH_NO'];
		$PK_PAYMENT_BATCH_MASTER = $res_22->fields['PK_PAYMENT_BATCH_MASTER'];
	}
	
	if($DISBURSEMENT_DATE != '0000-00-00')
		$DISBURSEMENT_DATE = date("m/d/Y",strtotime($DISBURSEMENT_DATE));
	else
		$DISBURSEMENT_DATE = '';
		
	if($APPROVED_DATE != '0000-00-00')
		$APPROVED_DATE = date("m/d/Y",strtotime($APPROVED_DATE));
	else
		$APPROVED_DATE = '';
		
	if($DEPOSITED_DATE != '0000-00-00')
		$DEPOSITED_DATE = date("m/d/Y",strtotime($DEPOSITED_DATE));
	else
		$DEPOSITED_DATE = '';
		
}
/**
 * DAIM-99 
 *
 */
$option_type = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND ACTIVE = '1' AND DIAMOND_PAY = '1'"); 
$disable_disp = "";
if($PK_DISBURSEMENT_STATUS == 3 || $PK_DISBURSEMENT_STATUS == 1)
	$disable_disp = "disabled";
?>
<tr id="student_disbursement_div_<?=$disbursement_id?>" >
	<!-- Ticket # 1981 -->
	<td>
		<div style="width:80px;" >
		<? if($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3){ 
			if($disable_disp == '' && $PK_DISBURSEMENT_STATUS != 1 && $PK_DISBURSEMENT_STATUS != 3 ){ ?>
				<a href="javascript:void(0);" onclick="delete_row('<?=$disbursement_id?>','disbursement','<?=$PK_STUDENT_DISBURSEMENT?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
			<? } 
			if($PK_DISBURSEMENT_STATUS == 2 && $option_type->RecordCount() > 0 && ($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 || $res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2 || $res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 3 )) { // DAIM-99 , DIAM-2101

                if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1 ) {
                    ?>
                    <a href="make_payment.php?type=disp&id=<?=$PK_STUDENT_DISBURSEMENT?>&sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>" class="btn cc-color btn-circle" title="<?=MAKE_PAYMENT ?>"><i class="fas fa-credit-card"></i></a>
                    <?
                } else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 2) {
                    ?>
                    <a href="make_payment_stax.php?type=disp&id=<?=$PK_STUDENT_DISBURSEMENT?>&sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>" class="btn cc-color btn-circle" title="<?=MAKE_PAYMENT ?>"><i class="fas fa-credit-card"></i></a>
                    <?
                } else if($res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 3) {
                    ?>
                    <a href="make_payment_cybersource.php?type=disp&id=<?=$PK_STUDENT_DISBURSEMENT?>&sid=<?=$_GET['id']?>&t=<?=$_GET['t']?>&eid=<?=$_GET['eid']?>" class="btn cc-color btn-circle" title="<?=MAKE_PAYMENT ?> - CyberSource"><i class="fas fa-credit-card"></i></a>
                    <?
                }
            }
		} ?>
		</div>
	</td>
	<!-- Ticket # 1981 -->
	<td >
		<input type="hidden" name="PK_STUDENT_DISBURSEMENT_<?=$disbursement_id?>" id="PK_STUDENT_DISBURSEMENT" value="<?=$PK_STUDENT_DISBURSEMENT?>" />
		<input type="hidden" name="DISBURSEMENT_PK_DISBURSEMENT_STATUS_<?=$disbursement_id?>" id="DISBURSEMENT_PK_DISBURSEMENT_STATUS_<?=$disbursement_id?>" value="<?=$PK_DISBURSEMENT_STATUS?>" />
		
		<input type="hidden" name="disbursement_id[]"  value="<?=$disbursement_id?>"/>
		<select id="DISBURSEMENT_PK_AR_LEDGER_CODE_<?=$disbursement_id?>" name="DISBURSEMENT_PK_AR_LEDGER_CODE_<?=$disbursement_id?>" class="form-control" style="width:200px;" <?=$disable_disp?> >
			<option selected></option>
			<? /* Ticket #1692  */
			$res_type = $db->Execute("select PK_AR_LEDGER_CODE, CONCAT(CODE,' - ',LEDGER_DESCRIPTION) as CODE, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by ACTIVE DESC, CODE ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_type->MoveNext();
			} /* Ticket #1692  */ ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_AY_<?=$disbursement_id?>" id="DISBURSEMENT_AY_<?=$disbursement_id?>" value="<?=$ACADEMIC_YEAR?>" <?=$disable_disp?>  style="width:28px;"/><!--DIAM-2045 -->
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_AP_<?=$disbursement_id?>" id="DISBURSEMENT_AP_<?=$disbursement_id?>" value="<?=$ACADEMIC_PERIOD?>" <?=$disable_disp?> style="width:28px;"/><!--DIAM-2045 -->
	</td>
	<td>
		<input type="text" class="form-control date" placeholder="" name="DISBURSEMENT_DATE_<?=$disbursement_id?>" id="DISBURSEMENT_DATE_<?=$disbursement_id?>" value="<?=$DISBURSEMENT_DATE?>" <?=$disable_disp?> onchange="get_award_yearfrom_date(<?=$disbursement_id?>)" style="width:150px;" />
	</td>
	<!-- Ticket # 1026 -->
	<td class="DISB_GROSS" style="display:none" >
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_GROSS_AMOUNT_<?=$disbursement_id?>" id="DISBURSEMENT_GROSS_AMOUNT_<?=$disbursement_id?>" value="<?=$DISBURSEMENT_GROSS_AMOUNT?>" style="width:150px;text-align:right" <?=$disable_disp?> onblur="set_decimal('DISBURSEMENT_GROSS_AMOUNT_<?=$disbursement_id?>')" />
	</td>
	<td class="DISB_FEE" style="display:none" >
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_FEE_AMOUNT_<?=$disbursement_id?>" id="DISBURSEMENT_FEE_AMOUNT_<?=$disbursement_id?>" value="<?=$DISBURSEMENT_FEE_AMOUNT?>" style="width:150px;text-align:right" <?=$disable_disp?> onblur="set_decimal('DISBURSEMENT_FEE_AMOUNT_<?=$disbursement_id?>')" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_AMOUNT_<?=$disbursement_id?>" id="DISBURSEMENT_AMOUNT_<?=$disbursement_id?>" value="<?=$DISBURSEMENT_AMOUNT?>" style="text-align:right;width:180px;" <?=$disable_disp?> onblur="set_decimal('DISBURSEMENT_AMOUNT_<?=$disbursement_id?>')" />
	</td>
	<!-- Ticket # 1026 -->
	<td>
		<select id="DISBURSEMENT_PK_AWARD_YEAR_<?=$disbursement_id?>" name="DISBURSEMENT_PK_AWARD_YEAR_<?=$disbursement_id?>" class="form-control" style="width:130px;" <?=$disable_disp?> >
			<option></option>
			<? $res_type = $db->Execute("select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 order by AWARD_YEAR DESC"); //Ticket # 1687
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" <? if($PK_AWARD_YEAR == $res_type->fields['PK_AWARD_YEAR']) echo "selected"; ?> ><?=$res_type->fields['AWARD_YEAR'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<!--<input type="text" class="form-control date" placeholder="" name="DISBURSEMENT_APPROVED_DATE_<?=$disbursement_id?>" id="DISBURSEMENT_APPROVED_DATE_<?=$disbursement_id?>" value="<?=$APPROVED_DATE?>" style="width:100px;" disabled />-->
		<?=$APPROVED_DATE?>
	</td>
	<td>
		<!--<input type="text" class="form-control date" placeholder="" name="DISBURSEMENT_DEPOSITED_DATE_<?=$disbursement_id?>" id="DISBURSEMENT_DEPOSITED_DATE_<?=$disbursement_id?>" value="<?=$DEPOSITED_DATE?>" style="width:100px;" disabled />-->
		<?=$DEPOSITED_DATE?>
	</td>
	<td><!--//DIAM-1158-->
		<div id="DISBURSEMENT_STATUS_<?=$disbursement_id?>">
		<? $res_type = $db->Execute("select DISBURSEMENT_STATUS from M_DISBURSEMENT_STATUS WHERE PK_DISBURSEMENT_STATUS = '$PK_DISBURSEMENT_STATUS' ");
		echo $res_type->fields['DISBURSEMENT_STATUS']; ?></div>
	</td>
	<td>
		<div style="width:100px;">
			<a href="batch_payment?id=<?=$PK_PAYMENT_BATCH_MASTER?>" target="_blank" ><?=$BATCH?></a>
		</div>
	</td>
	<td>
		<select id="DISBURSEMENT_PK_ENROLLMENT_<?=$disbursement_id?>" name="DISBURSEMENT_PK_ENROLLMENT_<?=$disbursement_id?>" class="form-control filterval" style="width:150px;"  ><!-- DIAM-1156-ENROLL-FILTER-1160 -->
			<? /* Ticket # 1692 */
			$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $DIS_PK_STUDENT_ENROLLMENT) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
			<?	$res_type->MoveNext();
			} /* Ticket # 1692 */ ?>
		</select>
		<span id="" style="display:none;"><?=$DIS_PK_STUDENT_ENROLLMENT?></span><!-- DIAM-1156-ENROLL-FILTER-1160 -->
	</td>
	<td>
		<select id="DISBURSEMENT_PK_TERM_BLOCK_<?=$disbursement_id?>" name="DISBURSEMENT_PK_TERM_BLOCK_<?=$disbursement_id?>" class="form-control" style="width:130px;" <?=$disable_disp?> >
			<option value="" ></option>
			<? /* Ticket # 1692 */
			$res_type = $db->Execute("select PK_TERM_BLOCK, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, IF(END_DATE = '0000-00-00','',DATE_FORMAT(END_DATE, '%m/%d/%Y' )) AS  END_DATE_1, DESCRIPTION, ACTIVE from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, BEGIN_DATE DESC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['END_DATE_1'].' - '.$res_type->fields['DESCRIPTION'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" <? if($PK_TERM_BLOCK == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
			<?	$res_type->MoveNext();
			} /* Ticket # 1692 */ ?>
		</select>
	</td>
	<td>
		<select id="DISBURSEMENT_PK_DETAIL_TYPE_<?=$disbursement_id?>" name="DISBURSEMENT_PK_DETAIL_TYPE_<?=$disbursement_id?>" class="form-control" style="width:150px;" onchange="get_disbursement_detail(this.value,'<?=$disbursement_id?>')" <?=$disable_disp?> >
			<option value="" ></option>
			<? $res_type = $db->Execute("select PK_DETAIL_TYPE,DETAIL_TYPE from Z_DETAIL_TYPE WHERE ACTIVE = 1 ");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_DETAIL_TYPE']?>" <? if($PK_DETAIL_TYPE == $res_type->fields['PK_DETAIL_TYPE']) echo "selected"; ?> ><?=$res_type->fields['DETAIL_TYPE']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<div id="DETAIL_DIV_<?=$disbursement_id?>">
			<? $_REQUEST['detail_type'] 		= $PK_DETAIL_TYPE;
			$_REQUEST['detail_id'] 				= $disbursement_id;
			$_REQUEST['sid'] 					= $_GET['id'];
			$_REQUEST['eid'] 					= $DIS_PK_STUDENT_ENROLLMENT;
			$_REQUEST['DISBURSEMENT_DETAIL']	= $DETAIL;
			$_REQUEST['disable_disp']			= $disable_disp;
			
			include("ajax_get_disbursement_detail_drop_down.php"); ?>
		</div>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_HOURS_REQUIRED_<?=$disbursement_id?>" id="DISBURSEMENT_HOURS_REQUIRED_<?=$disbursement_id?>" value="<?=$HOURS_REQUIRED?>" <?=$disable_disp?> style="width:130px;" />
	</td>
	<td>
		<div style="width:130px;text-align:center" >
			<input type="checkbox" name="DISBURSEMENT_FUNDS_REQUESTED_<?=$disbursement_id?>" id="DISBURSEMENT_FUNDS_REQUESTED_<?=$disbursement_id?>" value="1" <? if($DISBURSEMENT_FUNDS_REQUESTED == 1) echo "checked"; ?> <?=$disable_disp?> />
		</div>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DISBURSEMENT_COMMENTS_<?=$disbursement_id?>" id="DISBURSEMENT_COMMENTS_<?=$disbursement_id?>" value="<?=$DISBURSEMENT_COMMENTS?>" style="width:150px;" <?=$disable_disp?> />
	</td>
	<td>
		<div style="width:50px;" >
			<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' AND INVOICE = 1"); 
			if($PK_DISBURSEMENT_STATUS == 2 && $res_type->RecordCount() > 0 && $res_pay_access->fields['ENABLE_DIAMOND_PAY'] == 1) { ?>
			<input type="checkbox" name="PK_STUDENT_DISBURSEMENT_CHK[]" value="<?=$PK_STUDENT_DISBURSEMENT ?>" />
			<? } ?>
		</div>
	</td>
	
</tr>
