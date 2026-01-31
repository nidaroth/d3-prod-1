<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$PK_MISC_BATCH_DETAIL 	= $_REQUEST['PK_MISC_BATCH_DETAIL'];
$student_count			= $_REQUEST['student_count'];
$student_type			= $_REQUEST['student_type'];

$misc_search_cond = "";
if($_REQUEST['SRC_TERM_MASTER'] != ''){
	$misc_search_cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$_REQUEST['SRC_TERM_MASTER'].") ";
}
if($_REQUEST['SRC_CAMPUS_PROGRAM'] != ''){
	$misc_search_cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$_REQUEST['SRC_CAMPUS_PROGRAM'].") ";
}
if($_REQUEST['SRC_STUDENT_STATUS'] != ''){
	$misc_search_cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$_REQUEST['SRC_STUDENT_STATUS'].") ";
}
if($_REQUEST['SRC_SEARCH'] != ''){
	$misc_search_cond .= " AND CONCAT(LAST_NAME,', ',FIRST_NAME) LIKE '%".$_REQUEST['SRC_SEARCH']."%' ";
}

if($PK_MISC_BATCH_DETAIL == ''){
	$PK_STUDENT_MASTER 		= '';
	$PK_STUDENT_ENROLLMENT 	= '';
	$PK_AR_LEDGER_CODE		= '';
	$TRANSACTION_DATE 		= $_REQUEST['def_date']; // Ticket # 1883
	$DEBIT 					= '';
	$CREDIT 				= '';
	$AY 					= '';
	$AP 					= '';
	$PK_TERM_BLOCK			= '';
	$BATCH_DETAIL_DESCRIPTION = '';
	$MISC_RECEIPT_NO		  = '';
	$PK_AR_FEE_TYPE		  	  = '';
	$PK_AR_PAYMENT_TYPE		  = '';
	$PRIOR_YEAR				  = 2; //Ticket # 1047
	$en_cond 				  = " AND IS_ACTIVE_ENROLLMENT = 1 ";
	
	$credit_disabled = "disabled";
	$debit_disabled  = "disabled";
} else {
	$res_11 = $db->Execute("select * from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_MISC_BATCH_DETAIL = '$PK_MISC_BATCH_DETAIL' ");
	
	$PK_STUDENT_MASTER 		= $res_11->fields['PK_STUDENT_MASTER'];
	$PK_STUDENT_ENROLLMENT 	= $res_11->fields['PK_STUDENT_ENROLLMENT'];
	$PK_AR_LEDGER_CODE 		= $res_11->fields['PK_AR_LEDGER_CODE'];
	$TRANSACTION_DATE 		= $res_11->fields['TRANSACTION_DATE'];
	$DEBIT 					= $res_11->fields['DEBIT'];
	$CREDIT 				= $res_11->fields['CREDIT'];
	$AY 					= $res_11->fields['AY'];
	$AP 					= $res_11->fields['AP'];
	$PK_TERM_BLOCK			= $res_11->fields['PK_TERM_BLOCK'];
	$BATCH_DETAIL_DESCRIPTION 	= $res_11->fields['BATCH_DETAIL_DESCRIPTION'];
	$MISC_RECEIPT_NO 			= $res_11->fields['MISC_RECEIPT_NO'];
	$PK_AR_FEE_TYPE		  	    = $res_11->fields['PK_AR_FEE_TYPE'];
	$PK_AR_PAYMENT_TYPE		    = $res_11->fields['PK_AR_PAYMENT_TYPE'];
	$PRIOR_YEAR 				= $res_11->fields['PRIOR_YEAR']; //Ticket # 1047
	
	if($TRANSACTION_DATE != '0000-00-00')
		$TRANSACTION_DATE = date("m/d/Y",strtotime($TRANSACTION_DATE));
	else
		$TRANSACTION_DATE = '';
	
	$res_11 = $db->Execute("SELECT TYPE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ");
	if($res_11->fields['TYPE'] == 2) {
		$credit_disabled = "disabled";
		$debit_disabled  = "";
	} else if($res_11->fields['TYPE'] == 1) {
		$credit_disabled = "";
		$debit_disabled  = "disabled";
	}
	$en_cond = "";
}

$stu_cond = "";
if($PK_MISC_BATCH_DETAIL == ''){
	if($student_type == 1)
		//$stu_cond = " AND ADMISSIONS IN (0,1) "; // DIAM-1728
		$stu_cond = " AND ADMISSIONS = 0 "; // DIAM-2064
	else
		$stu_cond = " AND ADMISSIONS = 1 ";
	$stu_cond .= " AND ARCHIVED = 0 ";
}
	
$PK_CAMPUS	= $_REQUEST['campus_id'];
$campus_table = "";
$campus_cond  = "";
$group_by  	  = "";
if($PK_CAMPUS != ''){ 
	$campus_table = ",S_STUDENT_CAMPUS ";
	$campus_cond  = " AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
	$group_by  	  = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
}
?>

<tr id="misc_batch_detail_div_<?=$student_count?>" >
	<td>
	<input type="hidden" class="pk_stud_enrol" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$PK_STUDENT_ENROLLMENT?>" />
		<? if($_REQUEST['PK_BATCH_STATUS'] != 2){ ?>
		<a href="javascript:void(0);" onclick="delete_row('<?=$student_count?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
		<? } ?>
	</td>
	<td style="width:200px;" >
		<select id="BATCH_PK_STUDENT_MASTER_<?=$student_count?>" name="BATCH_PK_STUDENT_MASTER[]" class="required-entry"  onchange="get_ssn(this.value,<?=$student_count?>);get_enrollment_det(this.value,<?=$student_count?>);"  > <!-- Ticket #1612 -->
			<? /* Ticket #1612 */
			/*$posted_cond = "";
			if($_REQUEST['PK_BATCH_STATUS'] == 2)
				$posted_cond = " AND S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
				
			$res_type = $db->Execute("select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, M_STUDENT_STATUS $campus_table WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS $posted_cond $en_cond $stu_cond $campus_cond $misc_search_cond $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME)");  */
				
			$_SESSION['MISC_BATCH_STU_QUERY'] = "select S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID, SSN FROM S_STUDENT_MASTER, S_STUDENT_ENROLLMENT, S_STUDENT_ACADEMICS, M_STUDENT_STATUS $campus_table WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS AND S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER $en_cond $stu_cond $campus_cond $misc_search_cond ";
			$_SESSION['MISC_BATCH_STU_GROUP_BY'] = $group_by;
			$_SESSION['MISC_BATCH_STU_ORDER_BY'] = " ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ";
			
			if($PK_STUDENT_MASTER > 0){
				$res_type = $db->Execute($_SESSION['MISC_BATCH_STU_QUERY']." AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ".$_SESSION['MISC_BATCH_STU_GROUP_BY']." ".$_SESSION['MISC_BATCH_STU_ORDER_BY']);
				while (!$res_type->EOF) { ?>
					<option value="<?=$res_type->fields['PK_STUDENT_MASTER'] ?>" <? if($res_type->fields['PK_STUDENT_MASTER'] == $PK_STUDENT_MASTER) echo "selected"; ?> ><?=$res_type->fields['NAME'] ?></option>
				<?	$res_type->MoveNext();
				} 
			} 
			/* Ticket #1612 */ ?>
		</select>
	</td>
	<td style="padding: 0px 0px 0px 5px;" > <!-- Ticket # 1612 -->
		<input type="hidden" name="PK_MISC_BATCH_DETAIL[]" id="PK_MISC_BATCH_DETAIL" value="<?=$PK_MISC_BATCH_DETAIL?>" />
		<input type="hidden" name="student_count[]"  value="<?=$student_count?>" />
		
		<div id="SSN_DIV_<?=$student_count?>" style="width:85px;" ><!-- Ticket # 1612 -->
			<? $_REQUEST['eid'] = $PK_STUDENT_ENROLLMENT;
			include("ajax_student_ssn.php"); ?>
		</div>
	</td>
	<td>
		<select id="BATCH_PK_AR_LEDGER_CODE_<?=$student_count?>" name="BATCH_PK_AR_LEDGER_CODE[]" class="form-control required-entry ledger_select2" style="width:200px;" onchange="get_ledger_type(this.value,'<?=$student_count?>');get_fee_payment_type(this.value,'<?=$student_count?>');" > <!-- Ticket # 2005 -->
			<option selected></option>
			<? /* Ticket #1612 */
			$posted_cond = " AND ACTIVE = 1 ";
			// if($PK_AR_LEDGER_CODE != ''){
			// 	$posted_cond = " AND ( ACTIVE = 1 OR PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ) ";
			// }
			
			if($_REQUEST['PK_BATCH_STATUS'] == 2)
				$posted_cond = " AND PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE' ";
				
			$res_type = $db->Execute("SELECT PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION,ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $posted_cond order by CODE ASC");
			/* Ticket #1612 */
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> ><?=$res_type->fields['CODE'] ?> <?php if($res_type->fields['ACTIVE'] != 1){ echo '(inactive)';} ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control date required-entry" placeholder="" name="BATCH_TRANSACTION_DATE[]" id="BATCH_TRANSACTION_DATE_<?=$student_count?>" value="<?=$TRANSACTION_DATE?>" style="width:90px"  /><!-- Ticket # 1612 -->
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0;width:100px;" > <!-- Ticket # 1612  -->
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 5px;height: 38px;">$</span>
			</div>
			<input type="number" class="form-control <? if($debit_disabled == '') { ?> required-entry <? } ?> " placeholder="" name="BATCH_DEBIT[]" id="BATCH_DEBIT_<?=$student_count?>" value="<?=$DEBIT?>" onblur="format_val('BATCH_DEBIT',<?=$student_count?>);calc_total(1);" style="text-align:right;"  <?=$debit_disabled?> style="width:75px" />
		</div>
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0;width:100px;" > <!-- Ticket # 1612  -->
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 5px;height: 38px;">$</span>
			</div>
			<input type="number" class="form-control <? if($credit_disabled == '') { ?> required-entry <? } ?>" placeholder="" name="BATCH_CREDIT[]" id="BATCH_CREDIT_<?=$student_count?>" value="<?=$CREDIT?>" onblur="format_val('BATCH_CREDIT',<?=$student_count?>);calc_total(1);" style="text-align:right;"  <?=$credit_disabled?>   />
		</div>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="BATCH_DETAIL_DESCRIPTION[]" id="BATCH_DETAIL_DESCRIPTION_<?=$student_count?>" value="<?=$BATCH_DETAIL_DESCRIPTION?>" style="width:150px" />
	</td>
	<td>
		<div style="width:100px" id="FEE_PAYMENT_TYPE_DIV_<?=$student_count?>" >
			<? if($PK_AR_LEDGER_CODE > 0) {
				$_REQUEST['ledger_id'] 				= $PK_AR_LEDGER_CODE;
				$_REQUEST['DEF_pk_ar_fee_type'] 	= $PK_AR_FEE_TYPE;
				$_REQUEST['DEF_pk_ar_payment_type'] = $PK_AR_PAYMENT_TYPE;
				$_REQUEST['count_1'] 				= $student_count;
				include("ajax_misc_batch_fee_payment_type.php");
			} ?>
		</div>
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="BATCH_AY[]" id="BATCH_AY_<?=$student_count?>" value="<?=$AY?>" style="width:50px"  /><!-- Ticket # 1612 -->
	</td>
	<td>
		<input type="text" class="form-control required-entry" placeholder="" name="BATCH_AP[]" id="BATCH_AP_<?=$student_count?>" value="<?=$AP?>" style="width:50px"  /><!-- Ticket # 1612 -->
	</td>
	<!-- Ticket # 1612 -->
	<td>
		<div style="width:100px" >
			<? if($MISC_RECEIPT_NO != '' && $PK_MISC_BATCH_DETAIL > 0){ ?>
			<a href="receipt_pdf?misc_id=<?=$PK_MISC_BATCH_DETAIL?>"><?=$MISC_RECEIPT_NO?></a>
			<? } ?>
		</div>
	</td>
	<td>
		<div id="ENROLLMEN_DIV_<?=$student_count?>" style="width:150px" >
			<? //if($PK_STUDENT_ENROLLMENT > 0){ 
				//$_REQUEST['stud_id'] 		= $PK_STUDENT_MASTER;
				//$_REQUEST['count1'] 		= $student_count;
				//$_REQUEST['en_def_val'] 	= $PK_STUDENT_ENROLLMENT;
				//include("ajax_get_misc_batch_student_enrollment.php");
			//} ?>
			<!-- DIAM-1728 -->
			<select id="BATCH_PK_STUDENT_ENROLLMENT_<?=$student_count?>" name="BATCH_PK_STUDENT_ENROLLMENT[]" class="form-control required-entry" onchange="get_term(this.value,<?=$student_count?>)"  >
				<option></option>
				<? $res_type = $db->Execute("SELECT PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT FROM S_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				while (!$res_type->EOF) { 
					$selected = ""; 
					if($PK_STUDENT_ENROLLMENT != ''){
						if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT)
							$selected = "selected"; 
					} else { 
						if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1)
							$selected = "selected"; 
					} ?>
					<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <?=$selected ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS']?></option>
				<?	$res_type->MoveNext();
				} ?>
			</select>
			<!-- End DIAM-1728 -->
		</div>
	</td>
	<!-- Ticket # 1612 -->
	
	<td>
		<select id="BATCH_PK_TERM_BLOCK_<?=$student_count?>" name="BATCH_PK_TERM_BLOCK[]" class="form-control" style="width:150px" >
			<option></option>
			<? $posted_cond = "";
			if($_REQUEST['PK_BATCH_STATUS'] == 2)
				$posted_cond = " AND PK_TERM_BLOCK = '$PK_TERM_BLOCK' ";
				
			$res_type = $db->Execute("select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DESCRIPTION, ACTIVE from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $posted_cond order by ACTIVE DESC, BEGIN_DATE DESC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" <? if($PK_TERM_BLOCK == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['DESCRIPTION']?></option>
			<?	$res_type->MoveNext();
			}  ?>
		</select>
	</td>
	
	<!-- Ticket # 1047 -->
	<td>
		<select id="PRIOR_YEAR_<?=$student_count?>" name="PRIOR_YEAR[]" class="form-control required-entry" style="width:50px" >
			<option ></option>
			<option value="1" <? if($PRIOR_YEAR == 1) echo "selected"; ?> >Yes</option>
			<option value="2" <? if($PRIOR_YEAR == 2) echo "selected"; ?> >No</option>
		</select>
	</td>
	<!-- Ticket # 1047 -->
	
</tr>