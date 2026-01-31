<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');

if($FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_FEE_BUDGET	= '';
$PK_STUDENT_MASTER 		= $_REQUEST['sid'];
$PK_STUDENT_ENROLLMENT 	= $_REQUEST['eid'];
$fee_budge_id			= $_REQUEST['fee_budge_id'];

if($_REQUEST['NEW'] == 1) {
	$PK_CAMPUS_PROGRAM_FEE = $_REQUEST['PK_CAMPUS_PROGRAM_FEE'];
	$res_11 = $db->Execute("select * from M_CAMPUS_PROGRAM_FEE WHERE PK_CAMPUS_PROGRAM_FEE = '$PK_CAMPUS_PROGRAM_FEE' AND ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	
	$DAYS_FROM_START			= $res_11->fields['DAYS_FROM_START'];
	$PK_AR_LEDGER_CODE 			= $res_11->fields['PK_AR_LEDGER_CODE'];
	$ACADEMIC_YEAR 				= $res_11->fields['AY'];
	$ACADEMIC_PERIOD 			= $res_11->fields['AP'];
	$FEE_AMOUNT 				= $res_11->fields['AMOUNT'];
	$PK_FEE_TYPE 				= $res_11->fields['PK_FEE_TYPE'];
	$DESCRIPTION 				= $res_11->fields['DESCRIPTION'];
	$FEE_BUDGET_DATE			= '';
	$FEE_BUDGET_APPROVED_DATE	= '';
	$FEE_BUDGET_PK_ENROLLMENT 	= $_REQUEST['PK_STUDENT_ENROLLMENT'];
	$PK_TUITION_BATCH_DETAIL	= 0;
	
	$res_11 = $db->Execute("select BEGIN_DATE from S_STUDENT_ENROLLMENT, S_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$FEE_BUDGET_PK_ENROLLMENT' AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER = S_TERM_MASTER.PK_TERM_MASTER AND BEGIN_DATE != '0000-00-00' ");
	if($res_11->RecordCount() > 0)
		$FEE_BUDGET_DATE = date("m/d/Y",strtotime($res_11->fields['BEGIN_DATE']." +".$DAYS_FROM_START." days"));
	
} else if($_REQUEST['NEW'] == 0){
	$PK_STUDENT_FEE_BUDGET = $_REQUEST['PK_STUDENT_FEE_BUDGET'];
	$res_11 = $db->Execute("select * from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_FEE_BUDGET = '$PK_STUDENT_FEE_BUDGET' ");
	
	$FEE_BUDGET_PK_ENROLLMENT = $res_11->fields['PK_STUDENT_ENROLLMENT'];
	$PK_STUDENT_MASTER 			= $res_11->fields['PK_STUDENT_MASTER'];
	$PK_AR_LEDGER_CODE 			= $res_11->fields['PK_AR_LEDGER_CODE'];
	$ACADEMIC_YEAR 				= $res_11->fields['ACADEMIC_YEAR'];
	$ACADEMIC_PERIOD 			= $res_11->fields['ACADEMIC_PERIOD'];
	$FEE_AMOUNT 				= $res_11->fields['FEE_AMOUNT'];
	$PK_FEE_TYPE 				= $res_11->fields['PK_FEE_TYPE'];
	$PK_CAMPUS_PROGRAM 			= $res_11->fields['PK_CAMPUS_PROGRAM'];
	$PK_TERM_MASTER 			= $res_11->fields['PK_TERM_MASTER'];
	$DESCRIPTION 				= $res_11->fields['DESCRIPTION'];
	$FEE_BUDGET_DATE 			= $res_11->fields['FEE_BUDGET_DATE'];
	$FEE_BUDGET_APPROVED_DATE 	= $res_11->fields['FEE_BUDGET_APPROVED_DATE'];
	$PK_ESTIMATE_FEE_STATUS 	= $res_11->fields['PK_ESTIMATE_FEE_STATUS'];
	$FEE_BUDGET_DEPOSITED_DATE	= $res_11->fields['FEE_BUDGET_DEPOSITED_DATE'];
	
	if($FEE_BUDGET_DATE != '' && $FEE_BUDGET_DATE != '0000-00-00')
		$FEE_BUDGET_DATE = date("m/d/Y",strtotime($FEE_BUDGET_DATE));
	else
		$FEE_BUDGET_DATE = '';
		
	if($FEE_BUDGET_APPROVED_DATE != '' && $FEE_BUDGET_APPROVED_DATE != '0000-00-00')
		$FEE_BUDGET_APPROVED_DATE = date("m/d/Y",strtotime($FEE_BUDGET_APPROVED_DATE));
	else
		$FEE_BUDGET_APPROVED_DATE = '';
		
	if($FEE_BUDGET_DEPOSITED_DATE != '' && $FEE_BUDGET_DEPOSITED_DATE != '0000-00-00')
		$FEE_BUDGET_DEPOSITED_DATE = date("m/d/Y",strtotime($FEE_BUDGET_DEPOSITED_DATE));
	else
		$FEE_BUDGET_DEPOSITED_DATE = '';
		
	$BATCH					 = '';
	$PK_TUITION_BATCH_MASTER = '';
	$PK_TUITION_BATCH_DETAIL = $res_11->fields['PK_TUITION_BATCH_DETAIL'];
	$res_22 = $db->Execute("select S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER,BATCH_NO from S_TUITION_BATCH_MASTER, S_TUITION_BATCH_DETAIL WHERE S_TUITION_BATCH_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_TUITION_BATCH_MASTER.PK_TUITION_BATCH_MASTER = S_TUITION_BATCH_DETAIL.PK_TUITION_BATCH_MASTER AND PK_TUITION_BATCH_DETAIL = '$PK_TUITION_BATCH_DETAIL' ");
	if($res_22->RecordCount() > 0) {
		$BATCH 					 = $res_22->fields['BATCH_NO'];
		$PK_TUITION_BATCH_MASTER = $res_22->fields['PK_TUITION_BATCH_MASTER'];
	}
		
} else if($_REQUEST['NEW'] == 2){
	$PK_AR_LEDGER_CODE 			= '';
	$ACADEMIC_YEAR 				= '';
	$ACADEMIC_PERIOD 			= '';
	$FEE_AMOUNT 				= '';
	$PK_FEE_TYPE 				= '';
	$PK_CAMPUS_PROGRAM 			= '';
	$PK_TERM_MASTER 			= '';
	$DESCRIPTION 				= '';
	$FEE_BUDGET_DATE 			= '';
	$FEE_BUDGET_APPROVED_DATE 	= '';
	$FEE_BUDGET_PK_ENROLLMENT 	= $_REQUEST['eid'];
	$PK_TUITION_BATCH_DETAIL	= 0;
}


?>
<tr id="student_fee_budge_div_<?=$fee_budge_id?>" >
	<!-- Ticket # 1980 -->
	<td>
		<? if($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3){ 
			if($PK_TUITION_BATCH_DETAIL == 0){ ?>
			<a href="javascript:void(0);" onclick="delete_row('<?=$fee_budge_id?>','fee_budget')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
			<? } 
		} ?>
	</td>
	<!-- Ticket # 1980 -->
	
	<td >
		<input type="hidden" name="EST_FEE_HID[]" id="EST_FEE_HID_<?=$fee_budge_id?>" value="<?=$fee_budge_id?>" />
		<input type="hidden" name="PK_STUDENT_FEE_BUDGET[]" id="PK_STUDENT_FEE_BUDGET" value="<?=$PK_STUDENT_FEE_BUDGET?>" />
		<input type="text" class="form-control date" placeholder="" name="FEE_BUDGET_DATE[]" id="FEE_BUDGET_DATE_<?=$fee_budge_id?>" value="<?=$FEE_BUDGET_DATE?>" style="width:100px;" />
	</td>
	<td>
		<select id="FEE_BUDGET_PK_AR_LEDGER_CODE_<?=$fee_budge_id?>" name="FEE_BUDGET_PK_AR_LEDGER_CODE[]" class="form-control" onchange="get_ledger_desc(this.value,'<?=$fee_budge_id?>')" style="width:200px;" >
			<option selected></option>
			<? /* Ticket #1692  */
			$res_type = $db->Execute("select PK_AR_LEDGER_CODE, CONCAT(CODE,' - ',LEDGER_DESCRIPTION) as CODE, ACTIVE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND TYPE = 2 order by ACTIVE DESC, CODE ASC");
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['CODE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="FEE_BUDGET_AY[]" id="FEE_BUDGET_AY_<?=$fee_budge_id?>" value="<?=$ACADEMIC_YEAR?>" style="width:50px;" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="FEE_BUDGET_AP[]" id="FEE_BUDGET_AP_<?=$fee_budge_id?>" value="<?=$ACADEMIC_PERIOD?>" style="width:50px;" />
	</td>
	<td>
		<div class="col-md-12 input-group">
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
			</div>
			<input type="text" class="form-control" placeholder="" name="FEE_BUDGET_AMOUNT[]" id="FEE_BUDGET_AMOUNT_<?=$fee_budge_id?>" value="<?=$FEE_AMOUNT?>" style="text-align:right;width:100px;" onblur="calc_estimated_fee()" />
		</div>
	</td>
	<td>
		<select id="FEE_BUDGET_PK_FEE_TYPE_<?=$fee_budge_id?>" name="FEE_BUDGET_PK_FEE_TYPE[]" class="form-control" onchange="calc_estimated_fee()" style="width:200px;" >
			<option selected></option>
			<? /* Ticket #1149  */
			$act_type_cond = " AND ACTIVE = 1 ";
			if($PK_FEE_TYPE > 0)
				$act_type_cond = " AND (ACTIVE = 1 OR PK_FEE_TYPE = '$PK_FEE_TYPE' ) ";
				
			$res_type = $db->Execute("select PK_FEE_TYPE,FEE_TYPE from M_FEE_TYPE WHERE 1 = 1 $act_type_cond  order by FEE_TYPE ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_FEE_TYPE'] ?>" <? if($res_type->fields['PK_FEE_TYPE'] == $PK_FEE_TYPE) echo "selected"; ?> ><?=$res_type->fields['FEE_TYPE']?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	<!-- DIAM-1157 -->
	<?
		$res_code_type = $db->Execute("SELECT IF(NEED_ANALYSIS =1, 'Yes', 'No') AS COA_STATUS from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND TYPE = 2 AND PK_AR_LEDGER_CODE = $PK_AR_LEDGER_CODE");
	?>
	<td>
		<div style="width:80px;" id="COA_STATUS_<?=$fee_budge_id?>"><?=$res_code_type->fields['COA_STATUS']?></div>
	</td>
	<!-- DIAM-1157 -->
	<td>
		<div style="width:80px;" ><?=$FEE_BUDGET_APPROVED_DATE ?></div>
	</td>
	<td>
		<div style="width:80px;" ><?=$FEE_BUDGET_DEPOSITED_DATE ?></div>
	</td>
	<td>
		<? $res_type = $db->Execute("select ESTIMATE_FEE_STATUS from M_ESTIMATE_FEE_STATUS WHERE PK_ESTIMATE_FEE_STATUS = '$PK_ESTIMATE_FEE_STATUS' ");
		echo $res_type->fields['ESTIMATE_FEE_STATUS']; ?>
	</td>
	<td>
		<div style="width:100px;">
			<a href="tuition_batch?id=<?=$PK_TUITION_BATCH_MASTER?>" target="_blank" ><?=$BATCH?></a>
		</div>
	</td>
	<td>
		<select id="FEE_BUDGET_PK_ENROLLMENT_<?=$fee_budge_id?>" name="FEE_BUDGET_PK_ENROLLMENT[]" class="form-control" style="width:200px;" >
			<? /* Ticket # 1692 */
			$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $FEE_BUDGET_PK_ENROLLMENT) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
			<?	$res_type->MoveNext();
			}  /* Ticket # 1692 */ ?>
		</select>
		<span id="" style="display:none;"><?=$FEE_BUDGET_PK_ENROLLMENT?></span><!-- DIAM-1156-ENROLL-FILTER-1160 -->
	</td>
	<!--<td>
		<input type="text" class="form-control" placeholder="" name="FEE_BUDGET_DESCRIPTION[]" id="FEE_BUDGET_DESCRIPTION_<?=$fee_budge_id?>" value="<?=$DESCRIPTION?>" />
	</td>-->
	
</tr>
