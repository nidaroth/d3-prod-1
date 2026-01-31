<? require_once("../global/config.php"); 

$cond = "";
if($_REQUEST['pk_id'])
	$cond = " AND S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER != '$_REQUEST[pk_id]' ";
	
$res_disb = $db->Execute("SELECT S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT, CODE, IF(DISBURSEMENT_DATE != '0000-00-00', DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y') , '' ) as DISBURSEMENT_DATE, DISBURSEMENT_AMOUNT, CONCAT(LAST_NAME,', ', FIRST_NAME) as NAME, CODE, BATCH_NO 
FROM 
S_PAYMENT_BATCH_MASTER, S_PAYMENT_BATCH_DETAIL, S_STUDENT_MASTER, S_STUDENT_DISBURSEMENT 
LEFT JOIN M_AR_LEDGER_CODE ON M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE = S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE 
WHERE 
S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER AND 
S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT AND 
S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT IN ($_REQUEST[disb_id]) AND S_PAYMENT_BATCH_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond "); 
if($res_disb->RecordCount() > 0){
	echo "c!----@"; ?>
	<table width="100%" class='table table-hover' >
		<tr>
			<th>Batch #</th>
			<th>Student Name</th>
			<th>Ledger Code</th>
			<th>Disbursement Date</th>
			<th>Disbursement Amount</th>
		</tr>
		<? while (!$res_disb->EOF) {  ?>
			<tr>
				<td><?=$res_disb->fields['BATCH_NO']?></td>
				<td><?=$res_disb->fields['NAME']?></td>
				<td><?=$res_disb->fields['CODE']?></td>
				<td><?=$res_disb->fields['DISBURSEMENT_DATE']?></td>
				<td>$ <?=number_format_value_checker($res_disb->fields['DISBURSEMENT_AMOUNT'], 2)?></td>
			</tr>
		<?	$res_disb->MoveNext();
		} ?>
	</table>
<? exit;
}

if($_REQUEST['pk_id'] == '')
	echo "a!----@";
else {
	
	$res = $db->Execute("SELECT PK_BATCH_STATUS FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$_REQUEST[pk_id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'"); 
	if($res->fields['PK_BATCH_STATUS'] != 2)
		echo "a!----@";
	else
		echo "b!----@";
}